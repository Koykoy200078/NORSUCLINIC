<?php

namespace Tests\Feature\Regression;

use App\Models\ActivityLog;
use App\Models\ConsultationMedicine;
use App\Models\DocumentIssuance;
use App\Models\LabRequest;
use App\Models\Medicine;
use App\Models\MedicineTransaction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\Concerns\BuildsClinicData;
use Tests\TestCase;

/**
 * R3-M6: lab requests are soft-deleted like the other clinical records, and the administrator can see what was deleted,
 * by whom, and bring it back (Settings > Deleted records). A consultation that gave out medicines takes them out of
 * stock again when restored, and is refused if the stock is no longer there.
 */
class DeletedRecordsTest extends TestCase
{
    use RefreshDatabase;
    use BuildsClinicData;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedAccessControl();
    }

    private function labRequest(array $attributes = []): int
    {
        $nurse = $this->makeStaff('nurse', 'medical_consultation', ['email' => 'lab.nurse' . uniqid() . '@test.local']);
        $patient = $this->makePatient(['email' => 'lab.pt' . uniqid() . '@test.local']);

        $id = DB::table('lab_requests')->insertGetId(array_merge([
            'request_number' => (string) random_int(100000, 999999), 'document_creator_id' => $nurse->id, 'patient_user_id' => $patient->user_id,
            'patient_name' => 'Lab Patient', 'requested_at' => now()->toDateString(), 'status' => 'pending',
            'created_at' => now(), 'updated_at' => now(),
        ], $attributes));

        DB::table('lab_request_items')->insert([
            'lab_request_id' => $id, 'test_name' => 'CBC', 'test_category' => 'Hematology', 'result_status' => 'pending',
            'created_at' => now(), 'updated_at' => now(),
        ]);

        return $id;
    }

    public function test_deleting_a_lab_request_hides_it_but_keeps_the_row_and_its_tests(): void
    {
        $admin = $this->makeAdmin();
        $id = $this->labRequest(['document_creator_id' => $admin->id]);

        $this->actingAs($admin)->delete(route('lab-requests.destroy', $id))->assertRedirect();

        $this->assertNull(LabRequest::find($id));
        $this->assertSoftDeleted('lab_requests', ['id' => $id]);
        $this->assertSame(1, DB::table('lab_request_items')->where('lab_request_id', $id)->count(), 'the tests stay with the request');
        $this->assertSame(1, ActivityLog::where('action', 'lab_request_deleted')->count());
    }

    public function test_the_administrator_sees_who_deleted_what_and_restores_a_lab_request(): void
    {
        $admin = $this->makeAdmin();
        $nurse = $this->makeStaff('nurse', 'medical_consultation');
        $id = $this->labRequest(['document_creator_id' => $nurse->id, 'patient_name' => 'Restore Me']);

        $this->actingAs($nurse)->delete(route('staff.lab-requests.destroy', $id))->assertRedirect();

        $this->actingAs($admin)->get(route('deleted-records.index'))->assertOk()
            ->assertSee('Restore Me')->assertSee($nurse->full_name);

        $this->post(route('deleted-records.restore', ['type' => 'lab-request', 'id' => $id]))->assertRedirect(route('deleted-records.index'));

        $this->assertNotNull(LabRequest::find($id));
        $this->assertSame(1, LabRequest::find($id)->items()->count());
        $this->assertSame(1, ActivityLog::where('action', 'lab_request_restored')->count());
        // (the confirmation message names the patient, so look for the row's button instead)
        $this->get(route('deleted-records.index'))
            ->assertDontSee(route('deleted-records.restore', ['type' => 'lab-request', 'id' => $id]), false);
    }

    public function test_a_certificate_is_restored_to_the_lists(): void
    {
        $admin = $this->makeAdmin();
        $patient = $this->makePatient();
        $certificate = DocumentIssuance::create([
            'document_type' => 'medical_certificate', 'document_creator_id' => $admin->id, 'user_id' => $patient->user_id,
            'name' => 'Cert Person', 'age' => 20, 'gender' => 'Male', 'address' => 'Dumaguete', 'requested_at' => now()->toDateString(),
        ]);
        $certificate->delete();

        $this->actingAs($admin)->get(route('deleted-records.index'))->assertOk()->assertSee('Medical certificate #' . $certificate->id);

        $this->post(route('deleted-records.restore', ['type' => 'document', 'id' => $certificate->id]))->assertRedirect();

        $this->assertNotNull(DocumentIssuance::find($certificate->id));
        $this->assertSame(1, ActivityLog::where('action', 'document_restored')->count());
    }

    public function test_restoring_a_consultation_takes_its_medicines_out_of_stock_again(): void
    {
        $admin = $this->makeAdmin();
        $patient = $this->makePatient();
        $medicine = $this->makeMedicine('Restoremed');
        $this->stockIn($medicine, 10, now()->addYear()->toDateString());

        $visit = DocumentIssuance::create([
            'document_type' => 'consultation_form', 'document_creator_id' => $admin->id, 'user_id' => $patient->user_id,
            'name' => 'Rx Patient', 'age' => 20, 'gender' => 'Male', 'address' => 'Dumaguete', 'requested_at' => now()->toDateString(),
        ]);
        ConsultationMedicine::create(['request_document_id' => $visit->id, 'medicine_id' => $medicine->id, 'dosage' => '500mg', 'quantity' => 4, 'used_for' => 'nursing_intervention']);

        // deleted: the 4 units were already given back (as the delete flow does), so the shelf shows 10
        $this->actingAs($admin);
        $visit->delete();
        $this->assertSame(10, (int) $medicine->fresh()->available_quantity);

        $this->post(route('deleted-records.restore', ['type' => 'document', 'id' => $visit->id]))->assertRedirect();

        $this->assertNotNull(DocumentIssuance::find($visit->id));
        $this->assertSame(6, (int) $medicine->fresh()->available_quantity, '4 of the 10 units are out again');
        $this->assertSame(1, MedicineTransaction::where('transaction_type', MedicineTransaction::TYPE_DISPENSE)->where('quantity', 4)->count());
    }

    public function test_a_consultation_is_not_restored_when_the_stock_is_gone_and_nothing_changes(): void
    {
        $admin = $this->makeAdmin();
        $patient = $this->makePatient();
        $medicine = $this->makeMedicine('Gonemed');
        $this->stockIn($medicine, 2, now()->addYear()->toDateString());

        $visit = DocumentIssuance::create([
            'document_type' => 'consultation_form', 'document_creator_id' => $admin->id, 'user_id' => $patient->user_id,
            'name' => 'Short Patient', 'age' => 20, 'gender' => 'Male', 'address' => 'Dumaguete', 'requested_at' => now()->toDateString(),
        ]);
        ConsultationMedicine::create(['request_document_id' => $visit->id, 'medicine_id' => $medicine->id, 'dosage' => '500mg', 'quantity' => 5, 'used_for' => 'plan']);
        $this->actingAs($admin);
        $visit->delete();

        $this->post(route('deleted-records.restore', ['type' => 'document', 'id' => $visit->id]))->assertRedirect(route('deleted-records.index'));

        $this->assertNull(DocumentIssuance::find($visit->id), 'still deleted');
        $this->assertSame(2, (int) $medicine->fresh()->available_quantity, 'stock untouched');
        $this->assertSame(0, ActivityLog::where('action', 'document_restored')->count());
        $this->get(route('deleted-records.index'))->assertSee('Short Patient');
    }

    public function test_only_the_administrator_can_open_or_restore_deleted_records(): void
    {
        $visit = DocumentIssuance::create([
            'document_type' => 'medical_certificate', 'document_creator_id' => $this->makeAdmin()->id, 'user_id' => $this->makePatient()->user_id,
            'name' => 'Private', 'age' => 20, 'gender' => 'Male', 'address' => 'Dumaguete', 'requested_at' => now()->toDateString(),
        ]);
        $visit->delete();

        foreach ([$this->makeDoctor(), $this->makeStaff('clinic_head', 'front_desk')] as $user) {
            $this->actingAs($user)->get(route('deleted-records.index'))->assertForbidden();
            $this->post(route('deleted-records.restore', ['type' => 'document', 'id' => $visit->id]))->assertForbidden();
        }

        $this->assertNull(DocumentIssuance::find($visit->id));
    }

    public function test_a_record_that_is_not_deleted_cannot_be_restored(): void
    {
        $admin = $this->makeAdmin();
        $live = DocumentIssuance::create([
            'document_type' => 'medical_certificate', 'document_creator_id' => $admin->id, 'user_id' => $this->makePatient()->user_id,
            'name' => 'Still Here', 'age' => 20, 'gender' => 'Male', 'address' => 'Dumaguete', 'requested_at' => now()->toDateString(),
        ]);

        $this->actingAs($admin)->post(route('deleted-records.restore', ['type' => 'document', 'id' => $live->id]))->assertRedirect();
        $this->assertSame(0, ActivityLog::where('action', 'document_restored')->count());

        $this->post('/admin/settings/deleted-records/bogus/1/restore')->assertNotFound();
    }
}
