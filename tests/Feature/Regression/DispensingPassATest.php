<?php

namespace Tests\Feature\Regression;

use App\Livewire\PrescriptionVerificationTable;
use App\Models\Category;
use App\Models\DispenseRecord;
use App\Models\Medicine;
use App\Models\MedicineBatch;
use App\Models\Patient;
use App\Models\PatientType;
use App\Models\Prescription;
use App\Models\PrescriptionMedicine;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;
use Spatie\Permission\PermissionRegistrar;
use Tests\Concerns\BuildsClinicData;
use Tests\TestCase;

/**
 * Phase 5, row 5.7 Dispensing, Pass A: what the earlier tests (PrescriptionControlTest, PrescriptionInventoryTest,
 * StockOutTabTest, DispenseHistoryTest, PdfNastyDataTest) did not reach.
 */
class DispensingPassATest extends TestCase
{
    use RefreshDatabase;
    use BuildsClinicData;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedAccessControl();
    }

    private function grant(string $role, array $permissions): void
    {
        Role::findByName($role)->syncPermissions($permissions);
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    /** @return array<string, mixed> */
    private function patientPayload(array $overrides = []): array
    {
        return array_merge([
            'first_name' => 'Quick', 'last_name' => 'Added', 'gender' => 1, 'dob' => '2000-01-01', 'email' => 'quick.added@test.com',
            'patient_type_id' => PatientType::query()->value('id'),
            'nationality_citizenship' => 'Filipino', 'immunization_record' => 'complete', 'university_id_number' => 'DP-2026-001',
            'contact' => '09171234567', 'emergency_contact_name' => 'Mama', 'emergency_contact_no' => '0918-111-2222',
            'emergency_relationship' => 'Mother',
        ], $overrides);
    }

    private function pendingPrescription(User $doctor, Patient $patient, Medicine $medicine): Prescription
    {
        $prescription = Prescription::create([
            'patient_id' => $patient->id, 'doctor_id' => $doctor->doctor->id, 'status' => Prescription::DISPENSE_STATUS_PENDING,
            'is_active' => 1, 'consultation_date' => now()->toDateString(),
        ]);
        PrescriptionMedicine::create([
            'prescription_id' => $prescription->id, 'medicine' => $medicine->id, 'dosage' => '500mg', 'route_of_administration' => 'oral',
            'frequency' => 1, 'duration_value' => 5, 'duration_unit' => 'day', 'total_quantity' => 5,
        ]);

        return $prescription;
    }

    // ------------------------------------------------------------------ authorisation

    public function test_registering_a_patient_from_the_dispense_form_needs_the_patients_permission_and_is_not_for_doctors(): void
    {
        $nurse = $this->makeNurse();

        // a pharmacist with the medicines permission only may not register people
        $this->grant('staff', ['manage_medicines']);
        $this->actingAs($nurse)->postJson(route('staff.dispense-records.store-patient'), $this->patientPayload())->assertForbidden();
        $this->assertSame(0, User::where('email', 'quick.added@test.com')->count());

        // doctors view and edit patients, they do not register them (owner decision), here or anywhere else
        $this->actingAs($this->makeDoctor())->postJson(route('doctors.dispense-records.store-patient'), $this->patientPayload())->assertForbidden();
        $this->assertSame(0, User::where('email', 'quick.added@test.com')->count());

        // with both permissions the nurse can
        $this->grant('staff', ['manage_medicines', 'manage_patients']);
        $this->actingAs($nurse)->postJson(route('staff.dispense-records.store-patient'), $this->patientPayload())->assertOk();
        $this->assertSame(1, User::where('email', 'quick.added@test.com')->count());
    }

    // ------------------------------------------------------------------ the Verify Prescription list

    public function test_the_verify_list_searches_by_patient_doctor_and_prescription_number_and_a_doctor_without_a_profile_sees_nothing(): void
    {
        $doctor = $this->makeDoctor(['first_name' => 'Zzdoctorfirst']);
        $other = $this->makeDoctor();
        $patient = $this->makePatient(['first_name' => 'Zzpatientfirst']);
        $medicine = $this->makeMedicine('Verifymed');
        $mine = $this->pendingPrescription($doctor, $patient, $medicine);
        $this->pendingPrescription($other, $this->makePatient(['first_name' => 'Someoneelse']), $medicine);

        $admin = $this->makeAdmin();
        $rows = fn (string $search, ?User $as = null) => Livewire::actingAs($as ?? $admin)->withoutLazyLoading()->test(PrescriptionVerificationTable::class)->set('search', $search)->instance()->getRows();

        $this->assertSame([$mine->id], $rows('Zzpatientfirst')->pluck('id')->all(), 'by patient');
        $this->assertSame([$mine->id], $rows('Zzdoctorfirst')->pluck('id')->all(), 'by doctor');
        $this->assertSame([$mine->id], $rows((string) $mine->id)->pluck('id')->all(), 'by prescription number');
        $this->assertCount(0, $rows('nobodyatall'));

        // a doctor account with no doctor profile must not be handed every doctor's prescriptions
        $profileless = User::factory()->create();
        $profileless->assignRole('doctor');
        $this->assertCount(0, $rows('', $profileless));
    }

    // ------------------------------------------------------------------ the manual dispense form

    public function test_the_manual_dispense_record_refuses_a_date_that_cannot_be_real_and_a_failed_line_saves_nothing(): void
    {
        $admin = $this->makeAdmin();
        $patient = $this->makePatient();
        $category = Category::create(['name' => 'Zzcat', 'is_active' => 1]);
        $one = $this->makeMedicine('Linemed one', ['category_id' => $category->id]);
        $two = $this->makeMedicine('Linemed two', ['category_id' => $category->id]);
        $this->stockIn($one, 10, now()->addYear()->toDateString());
        $this->stockIn($two, 3, now()->addYear()->toDateString());
        $this->actingAs($admin);

        $payload = fn (array $overrides = [], int $secondQuantity = 2) => array_merge([
            'patient_id' => $patient->id, 'bill_date' => now()->toDateString(), 'note' => 'Note',
            'category_id' => [$category->id, $category->id], 'medicine' => [$one->id, $two->id], 'dosage' => ['500mg', '500mg'],
            'quantity' => [4, $secondQuantity],
        ], $overrides);

        foreach (['1066-10-14', now()->addYears(3)->toDateString(), '05/10/2026'] as $date) {
            $this->post(route('dispense-records.store'), $payload(['bill_date' => $date]))->assertSessionHasErrors();
            $this->assertSame(0, DispenseRecord::count(), $date);
            session()->flush();
        }

        // the second line asks for more than is on the shelf: the first line's stock is not kept either
        $this->post(route('dispense-records.store'), $payload([], 50));
        $this->assertSame(0, DispenseRecord::count(), 'a failed line saves no record');
        $this->assertSame(10, (int) MedicineBatch::where('medicine_id', $one->id)->sum('quantity'));
        $this->assertSame(3, (int) MedicineBatch::where('medicine_id', $two->id)->sum('quantity'));

        $this->post(route('dispense-records.store'), $payload())->assertSessionHasNoErrors();
        $this->assertSame(1, DispenseRecord::count());
        $this->assertSame(6, (int) MedicineBatch::where('medicine_id', $one->id)->sum('quantity'));
    }

    // ------------------------------------------------------------------ pick-list

    public function test_the_dispense_forms_medicines_of_a_category_do_not_query_twice_per_medicine(): void
    {
        $admin = $this->makeAdmin();
        $category = Category::create(['name' => 'Zzcat', 'is_active' => 1]);
        $add = function (int $i) use ($category) {
            $medicine = $this->makeMedicine('Catmed ' . $i, ['category_id' => $category->id]);
            $this->stockIn($medicine, 5, now()->addMonths(6)->toDateString());
        };
        $queries = function () use ($admin, $category) {
            $this->actingAs($admin)->getJson(route('dispense-records.by-category', $category))->assertOk(); // warm the caches
            DB::flushQueryLog();
            DB::enableQueryLog();
            $response = $this->actingAs($admin)->getJson(route('dispense-records.by-category', $category))->assertOk();
            $count = count(DB::getQueryLog());
            DB::disableQueryLog();

            return [$count, $response->json('data.medicine_details')];
        };

        foreach (range(1, 2) as $i) {
            $add($i);
        }
        [$few] = $queries();
        foreach (range(3, 10) as $i) {
            $add($i);
        }
        [$many, $details] = $queries();

        $this->assertCount(10, $details);
        $this->assertSame([['dosage' => '500mg', 'available_quantity' => 5]], array_map(fn ($d) => ['dosage' => $d['dosage'], 'available_quantity' => $d['available_quantity']], $details[0]['dosages']));
        $this->assertSame($few, $many, 'the category pick-list must not query per medicine');
    }

    public function test_the_dispense_forms_category_list_offers_only_the_strengths_that_have_not_expired(): void
    {
        $category = Category::create(['name' => 'Zzmixed', 'is_active' => 1]);
        $medicine = $this->makeMedicine('Mixedmed', ['category_id' => $category->id]);
        $this->stockIn($medicine, 7, now()->subMonth()->toDateString(), '250mg', 'OLD');
        $this->stockIn($medicine, 5, now()->addMonths(6)->toDateString(), '500mg', 'NEW');

        $details = $this->actingAs($this->makeAdmin())->getJson(route('dispense-records.by-category', $category))->assertOk()->json('data.medicine_details');

        $this->assertCount(1, $details);
        $this->assertSame(['500mg'], array_column($details[0]['dosages'], 'dosage'));
    }

    public function test_editing_a_manual_dispense_record_checks_the_date_and_a_refused_edit_changes_nothing(): void
    {
        $admin = $this->makeAdmin();
        $patient = $this->makePatient();
        $category = Category::create(['name' => 'Zzeditcat', 'is_active' => 1]);
        $medicine = $this->makeMedicine('Editmed', ['category_id' => $category->id]);
        $this->stockIn($medicine, 10, now()->addYear()->toDateString());
        $this->actingAs($admin);

        $payload = fn (array $overrides = []) => array_merge([
            'patient_id' => $patient->id, 'bill_date' => now()->toDateString(), 'note' => 'Note',
            'category_id' => [$category->id], 'medicine' => [$medicine->id], 'dosage' => ['500mg'], 'quantity' => [4],
        ], $overrides);
        $this->post(route('dispense-records.store'), $payload())->assertSessionHasNoErrors();
        $record = DispenseRecord::firstOrFail();
        $this->assertSame(6, (int) MedicineBatch::where('medicine_id', $medicine->id)->sum('quantity'));

        foreach (['1066-10-14', now()->addYears(3)->toDateString()] as $date) {
            $this->putJson(route('dispense-records.update', $record), $payload(['bill_date' => $date, 'quantity' => [9]]))->assertStatus(422);
        }
        $this->assertSame(6, (int) MedicineBatch::where('medicine_id', $medicine->id)->sum('quantity'), 'a refused edit moves no stock');

        $this->putJson(route('dispense-records.update', $record), $payload(['bill_date' => now()->format('Y-m-d H:i:s'), 'quantity' => [7]]))->assertOk();
        $this->assertSame(3, (int) MedicineBatch::where('medicine_id', $medicine->id)->sum('quantity'));
    }
}
