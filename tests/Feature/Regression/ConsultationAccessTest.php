<?php

namespace Tests\Feature\Regression;

use App\Livewire\DocumentIssuanceTable;
use App\Livewire\ReportGeneration;
use App\Models\ActivityLog;
use App\Models\Category;
use App\Models\DocumentIssuance;
use App\Models\Medicine;
use App\Models\Patient;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Features\SupportLockedProperties\CannotUpdateLockedPropertyException;
use Livewire\Livewire;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\Concerns\BuildsClinicData;
use Tests\TestCase;

/**
 * Regression tests for who may use the consultation screens (2026-10-01 re-audit R3-M1, R3-M5, R3-M6,
 * R3-H6): the nurse can load the medicine list, deletes are limited and audited, and the Livewire tables
 * enforce the staff designation limits that the page routes enforce.
 */
class ConsultationAccessTest extends TestCase
{
    use RefreshDatabase;
    use BuildsClinicData;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedAccessControl();
    }

    private function makeDocument(User $creator, Patient $patient, string $type = 'consultation_form', array $extra = []): DocumentIssuance
    {
        return DocumentIssuance::create(array_merge([
            'document_type' => $type,
            'document_creator_id' => $creator->id,
            'user_id' => $patient->user_id,
            'name' => 'Juan Dela Cruz',
            'age' => 23,
            'gender' => 'Male',
            'address' => 'Dumaguete',
            'requested_at' => now()->toDateString(),
        ], $extra));
    }

    /** R3-M1 */
    public function test_a_nurse_can_load_the_medicine_list_for_the_consultation_form(): void
    {
        $category = Category::create(['name' => 'Analgesics', 'is_active' => 1]);
        $medicine = $this->makeMedicine('Paracetamol', ['category_id' => $category->id]);
        $this->stockIn($medicine, 10, now()->addMonths(6)->toDateString());

        $nurse = $this->makeStaff('nurse', 'triage_area');
        $response = $this->actingAs($nurse)->getJson(route('staff.medicines.by.category'))->assertOk();
        $this->assertSame('Paracetamol', $response->json('data.0.medicines.0.name'));

        // Still closed to staff whose designation/station has neither consultations nor inventory.
        $frontDesk = $this->makeStaff('clinic_staff', 'front_desk');
        \App\Models\Role::findByName('staff')->revokePermissionTo(['manage_request_documents', 'manage_medicines']);
        $this->actingAs($frontDesk->fresh())->getJson(route('staff.medicines.by.category'))->assertForbidden();
    }

    /** R3-H5: the stored available_quantity is only refreshed when stock moves, so the list cannot trust it */
    public function test_the_consultation_medicine_list_ignores_stock_that_has_expired(): void
    {
        $category = Category::create(['name' => 'Analgesics', 'is_active' => 1]);
        $soonExpired = $this->makeMedicine('Soon Expired', ['category_id' => $category->id]);
        $this->stockIn($soonExpired, 5, now()->addDay()->toDateString());
        $fresh = $this->makeMedicine('Still Good', ['category_id' => $category->id]);
        $this->stockIn($fresh, 5, now()->addMonths(3)->toDateString());

        $this->travelTo(now()->addDays(3)); // the first batch is now expired; its stored quantity still says 5
        $this->assertSame(5, (int) $soonExpired->fresh()->available_quantity);

        $nurse = $this->makeStaff('nurse', 'triage_area');
        $names = collect($this->actingAs($nurse)->getJson(route('staff.medicines.by.category'))->assertOk()->json('data'))
            ->pluck('medicines.*.name')->flatten()->all();

        $this->assertSame(['Still Good'], $names);
    }

    /** R3-M6 + decision: only the admin or the creator delete a consultation */
    public function test_only_the_admin_or_the_creator_can_delete_a_consultation(): void
    {
        $patient = $this->makePatient();
        $doctor = $this->makeDoctor();
        $otherDoctor = $this->makeDoctor();
        $nurse = $this->makeStaff('nurse', 'triage_area');
        $otherNurse = $this->makeStaff('nurse', 'triage_area');
        $admin = $this->makeAdmin();

        $document = $this->makeDocument($nurse, $patient);

        foreach ([[$doctor, 'doctors'], [$otherDoctor, 'doctors'], [$otherNurse, 'staff']] as [$user, $prefix]) {
            $this->actingAs($user)->delete(route($prefix . '.document-issuances.destroy', $document))->assertForbidden();
        }
        $this->assertNotNull(DocumentIssuance::find($document->id));

        // The creator can ...
        $this->actingAs($nurse)->delete(route('staff.document-issuances.destroy', $document))->assertRedirect();
        $this->assertNull(DocumentIssuance::find($document->id));

        // ... and so can the clinic admin.
        $second = $this->makeDocument($doctor, $patient);
        $this->actingAs($admin)->delete(route('document-issuances.destroy', $second))->assertRedirect();
        $this->assertNull(DocumentIssuance::find($second->id));
    }

    /** R3-M5 */
    public function test_deleting_a_consultation_leaves_an_audit_snapshot(): void
    {
        $patient = $this->makePatient();
        $doctor = $this->makeDoctor();
        $medicine = $this->makeMedicine('Ibuprofen');
        $this->stockIn($medicine, 10, now()->addMonths(2)->toDateString());
        $document = $this->makeDocument($doctor, $patient, 'consultation_form', [
            'complaints' => 'Headache', 'assessment' => 'Tension headache', 'plan' => 'Rest',
        ]);
        \App\Models\ConsultationMedicine::create([
            'request_document_id' => $document->id, 'medicine_id' => $medicine->id,
            'dosage' => '500mg', 'quantity' => 2, 'used_for' => 'plan',
        ]);

        $this->actingAs($doctor)->delete(route('doctors.document-issuances.destroy', $document))->assertRedirect();

        $log = ActivityLog::where('action', 'document_deleted')->where('subject_id', $document->id)->firstOrFail();
        $this->assertSame($doctor->id, (int) $log->user_id);
        $this->assertSame('Juan Dela Cruz', $log->patient_name);
        $this->assertSame('Headache', $log->complaints);
        $this->assertSame('Tension headache', $log->diagnosis);
        $this->assertSame('consultation_form', $log->properties['document_type']);
        $this->assertSame('Ibuprofen', $log->properties['medicines'][0]['medicine']);
        $this->assertSame(2, $log->properties['medicines'][0]['quantity']);
    }

    /** R3-M5: certificates are audited too, and keep their existing module-based access */
    public function test_deleting_a_certificate_is_audited_and_front_desk_staff_may_still_delete_it(): void
    {
        $frontDesk = $this->makeStaff('clinic_staff', 'front_desk');
        $other = $this->makeStaff('clinic_staff', 'front_desk');
        $certificate = $this->makeDocument($other, $this->makePatient(), 'medical_certificate');

        $this->actingAs($frontDesk)->delete(route('staff.document-issuances.destroy', $certificate))->assertRedirect();

        $this->assertNull(DocumentIssuance::find($certificate->id));
        $this->assertNotNull(ActivityLog::where('action', 'document_deleted')->where('subject_id', $certificate->id)->first());
    }

    /** R3-H6 */
    public function test_the_livewire_document_table_cannot_be_switched_to_another_module(): void
    {
        $frontDesk = $this->makeStaff('clinic_staff', 'front_desk'); // certificates only
        $patient = $this->makePatient();
        $this->makeDocument($frontDesk, $patient, 'consultation_form', ['name' => 'CONSULTPROBE Patient']);
        $this->makeDocument($frontDesk, $patient, 'medical_certificate', ['name' => 'CERTPROBE Patient']);

        $this->actingAs($frontDesk);

        // Their own module works and shows certificates only.
        Livewire::test(DocumentIssuanceTable::class, ['module' => 'certificate'])
            ->assertSee('CERTPROBE Patient')
            ->assertDontSee('CONSULTPROBE Patient');

        // The module and the patient scope are fixed when the component is mounted.
        try {
            Livewire::test(DocumentIssuanceTable::class, ['module' => 'certificate'])->set('module', 'consultation');
            $this->fail('the module property must be locked');
        } catch (CannotUpdateLockedPropertyException $e) {
            $this->assertTrue(true);
        }

        try {
            Livewire::test(DocumentIssuanceTable::class, ['module' => 'certificate'])->set('patientId', $patient->user_id);
            $this->fail('the patientId property must be locked');
        } catch (CannotUpdateLockedPropertyException $e) {
            $this->assertTrue(true);
        }

        \App\Models\Role::findByName('staff')->revokePermissionTo('manage_request_documents');
        $this->actingAs($frontDesk->fresh());
        // Building either document list is refused without the permission (the test client turns the 403 into a
        // response, so the component's query builder is called directly).
        $component = new DocumentIssuanceTable();
        $component->module = 'consultation';
        try {
            $component->builder();
            $this->fail('a role without document permission must not build the consultation list');
        } catch (HttpException $e) {
            $this->assertSame(403, $e->getStatusCode());
        }
    }

    /** R3-H6 */
    public function test_doctors_and_authorised_staff_still_see_the_consultation_table(): void
    {
        $doctor = $this->makeDoctor();
        $nurse = $this->makeStaff('nurse', 'triage_area');
        $this->makeDocument($doctor, $this->makePatient(), 'consultation_form', ['name' => 'CONSULTPROBE Patient']);

        foreach ([$doctor, $nurse] as $user) {
            $this->actingAs($user);
            Livewire::test(DocumentIssuanceTable::class, ['module' => 'consultation'])->assertSee('CONSULTPROBE Patient');
        }
    }

    /** R3-H6: P2-H3 registered the Spatie middleware under a namespace that does not exist */
    public function test_every_livewire_persistent_middleware_is_a_real_class(): void
    {
        // Livewire's own default list names optional packages (e.g. Jetstream) that need not be installed;
        // only the entries this application and spatie/laravel-permission contribute must be real classes.
        $classes = array_filter(
            Livewire::getPersistentMiddleware(),
            fn (string $class) => str_starts_with($class, 'App\\') || str_starts_with($class, 'Spatie\\')
        );
        $this->assertContains(\Spatie\Permission\Middlewares\RoleMiddleware::class, $classes);
        $this->assertContains(\Spatie\Permission\Middlewares\PermissionMiddleware::class, $classes);

        foreach ($classes as $class) {
            $this->assertTrue(class_exists($class), "Livewire persistent middleware {$class} does not exist, so it is never re-applied");
        }
    }

    /** R3-H6 */
    public function test_notifications_and_reports_remain_available_without_optional_permissions(): void
    {
        $nurse = $this->makeStaff('nurse', 'triage_area'); // reports yes, notifications no
        $log = ActivityLog::create([
            'user_id' => $nurse->id, 'user_type' => 'staff', 'user_name' => 'Someone', 'action' => 'consultation_record',
            'description' => 'Consultation form: SECRETLOG Patient', 'patient_name' => 'SECRETLOG Patient', 'date' => now()->toDateString(),
        ]);

        \App\Models\Role::findByName('staff')->syncPermissions([]);
        $this->actingAs($nurse->fresh());

        $this->get(route('staff.activity-logs.index', ['tab' => 'logs']))->assertOk()->assertSee('SECRETLOG Patient');
        $this->get(route('staff.activity-logs.index'))->assertOk()->assertSee('SECRETLOG Patient');
        $this->get(route('staff.activity-logs.export'))->assertOk();
        $this->get(route('staff.activity-logs.export', ['tab' => 'logs']))->assertOk();
        $this->get(route('staff.activity-logs.show', $log))->assertOk();
        $this->get(route('staff.activity-logs.export', ['tab' => 'visits']))->assertOk();

        // Clicking the "Activity Logs" tab inside the open page does not get around it either.
        Livewire::test(ReportGeneration::class)
            ->call('setTab', 'logs')
            ->assertSet('tab', 'logs')
            ->assertSee('SECRETLOG Patient');
    }

    public function test_staff_with_the_notifications_module_and_the_admin_can_still_read_the_activity_log(): void
    {
        $head = $this->makeStaff('clinic_head', 'front_desk');
        $admin = $this->makeAdmin();
        $log = ActivityLog::create([
            'user_id' => $head->id, 'user_type' => 'staff', 'user_name' => 'Someone', 'action' => 'consultation_record',
            'description' => 'Consultation form: SECRETLOG Patient', 'patient_name' => 'SECRETLOG Patient', 'date' => now()->toDateString(),
        ]);

        $this->actingAs($head);
        $this->get(route('staff.activity-logs.index', ['tab' => 'logs']))->assertOk()->assertSee('SECRETLOG Patient');
        $this->get(route('staff.activity-logs.show', $log))->assertOk();
        $this->get(route('staff.activity-logs.export', ['tab' => 'logs']))->assertOk();

        $this->actingAs($admin);
        Livewire::test(ReportGeneration::class)->call('setTab', 'logs')->assertSet('tab', 'logs');
    }
}
