<?php

namespace Tests\Feature\Regression;

use App\Models\ActivityLog;
use App\Models\College;
use App\Models\Course;
use App\Models\DocumentIssuance;
use App\Models\Patient;
use App\Models\PatientQueue;
use App\Models\PatientType;
use App\Models\User;
use App\Models\YearLevel;
use App\Repositories\PrescriptionRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Tests\Concerns\BuildsClinicData;
use Tests\TestCase;

/**
 * Regression tests for the Patients module (2026-10-01 re-audit, staff/nurse + doctor workflow):
 * only staff/nurse (and the clinic admin) register patients, doctors may view and edit but not
 * add / archive / restore / reset, and registering or archiving a patient keeps the rest of the
 * clinic (queue, prescription dropdowns, audit trail, history) consistent.
 */
class PatientModuleTest extends TestCase
{
    use RefreshDatabase;
    use BuildsClinicData;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedAccessControl();
    }

    private function patientPayload(array $overrides = []): array
    {
        return array_merge([
            'first_name' => 'Maria',
            'last_name' => 'Santos',
            'email' => 'maria.santos@test.local',
            'gender' => User::FEMALE,
            'dob' => '2004-02-02',
            'patient_type_id' => PatientType::where('code', 'student')->value('id'),
            'university_id_number' => 'S-2026-001',
            'nationality_citizenship' => 'Filipino',
            'immunization_record' => 'Complete',
        ], $overrides);
    }

    /** @return array<string, array{0: string, 1: string}> */
    public static function staffWhoMayRegisterPatients(): array
    {
        return [
            'clinic head at front desk' => ['clinic_head', 'front_desk'],
            'front-desk clinic staff' => ['clinic_staff', 'front_desk'],
            'nurse at triage' => ['nurse', 'triage_area'],
            'triage officer at triage' => ['triage_officer', 'triage_area'],
            'records officer at records area' => ['records_officer', 'records_area'],
        ];
    }

    /**
     * @dataProvider staffWhoMayRegisterPatients
     */
    public function test_staff_and_nurses_can_register_a_patient_and_it_is_audited(string $designation, string $station): void
    {
        $staff = $this->makeStaff($designation, $station);

        $this->actingAs($staff)->post(route('staff.patients.store'), $this->patientPayload())
            ->assertRedirect(route('staff.patients.index'));

        $user = User::where('email', 'maria.santos@test.local')->firstOrFail();
        $this->assertSame(User::PATIENT, (int) $user->type);
        $this->assertSame('S-2026-001', $user->patient->patient_unique_id);

        $log = ActivityLog::where('action', 'patient_record')->where('subject_id', $user->patient->id)->first();
        $this->assertNotNull($log, 'registering a patient must leave an audit row');
        $this->assertSame($staff->id, (int) $log->user_id);
    }

    public function test_the_patient_audit_row_records_college_course_year_and_address(): void
    {
        $staff = $this->makeStaff('clinic_staff', 'front_desk');
        $college = College::create(['college_name' => 'College of Nursing']);
        $course = Course::create(['course_name' => 'BS Nursing']);
        $yearLevel = YearLevel::create(['year_level_name' => '2nd Year']);

        $this->actingAs($staff)->post(route('staff.patients.store'), $this->patientPayload([
            'college_id' => $college->id,
            'course_id' => $course->id,
            'year_level_id' => $yearLevel->id,
            'address1' => 'Purok 3, Bantayan',
            'contact' => '9171234567',
        ]))->assertRedirect();

        $log = ActivityLog::where('action', 'patient_record')->firstOrFail();
        $this->assertSame('College of Nursing', $log->college);
        $this->assertSame('BS Nursing - 2nd Year', $log->course_section);
        $this->assertSame('Purok 3, Bantayan', $log->address);
    }

    public function test_staff_without_the_patients_permission_cannot_register_patients(): void
    {
        $pharmacist = $this->makeStaff('pharmacist', 'pharmacy');
        $nurseAtConsultation = $this->makeStaff('nurse', 'medical_consultation');

        \App\Models\Role::findByName('staff')->revokePermissionTo('manage_patients');
        foreach ([$pharmacist, $nurseAtConsultation] as $staff) {
            $this->actingAs($staff)->post(route('staff.patients.store'), $this->patientPayload())->assertForbidden();
            $this->actingAs($staff)->get(route('staff.patients.create'))->assertForbidden();
        }

        $this->assertSame(0, Patient::count());
    }

    public function test_a_doctor_cannot_add_archive_restore_or_reset_a_patient(): void
    {
        $doctor = $this->makeDoctor();
        $patient = $this->makePatient();

        $this->actingAs($doctor);

        // No create form, no store.
        $this->assertFalse(Route::has('doctors.patients.create'));
        $this->assertFalse(Route::has('doctors.patients.store'));
        $this->post('/doctors/patients', $this->patientPayload())->assertStatus(405);
        $this->assertSame(1, Patient::count());

        // No archive, restore or reset-password.
        $this->deleteJson('/doctors/patients/' . $patient->id)->assertStatus(405);
        $this->postJson('/doctors/patients/' . $patient->id . '/restore')->assertNotFound();
        $this->postJson('/doctors/patients/' . $patient->user_id . '/reset-password')->assertNotFound();

        $this->assertNull($patient->fresh()->archived_at);
    }

    public function test_a_doctor_can_still_view_and_edit_an_existing_patient(): void
    {
        $doctor = $this->makeDoctor();
        $patient = $this->makePatient(['email' => 'juan@test.local', 'university_id_number' => 'S-1']);

        $this->actingAs($doctor);
        $this->get(route('doctors.patients.index'))->assertOk();
        $this->get(route('doctors.patients.show', $patient))->assertOk();
        $this->get(route('doctors.patients.edit', $patient))->assertOk();
        $this->get(route('doctors.patients.showMyHistory', $patient))->assertOk();

        $this->put(route('doctors.patients.update', $patient), $this->patientPayload([
            'first_name' => 'Juanito',
            'last_name' => 'Dela Cruz',
            'email' => 'juan@test.local',
            'university_id_number' => 'S-1',
        ]))->assertRedirect(route('doctors.patients.index'));

        $this->assertSame('Juanito', $patient->user->fresh()->first_name);
    }

    public function test_the_patient_list_hides_buttons_a_role_cannot_use(): void
    {
        $doctor = $this->makeDoctor();
        $staff = $this->makeStaff('clinic_staff', 'front_desk');
        $this->makePatient();

        $this->actingAs($doctor);
        $html = $this->get(route('doctors.patients.index'))->assertOk()->getContent();
        $this->assertStringNotContainsString(route('doctors.patients.index') . '/create', $html);

        // The staff "reset password" key pointed at the admin-only route and always answered 403.
        $this->actingAs($staff);
        $this->assertFalse(Route::has('staff.patients.reset.password'));
        $row = view('patients.components.action', ['row' => Patient::with('user')->first()])->render();
        $this->assertStringNotContainsString('patient-reset-password-btn', $row);
        $this->assertStringContainsString('patient-delete-btn', $row);
    }

    public function test_archiving_a_patient_closes_their_open_queue_entries_and_is_audited(): void
    {
        $staff = $this->makeStaff('clinic_staff', 'front_desk');
        $patient = $this->makePatient();
        $waiting = PatientQueue::create([
            'patient_id' => $patient->id, 'added_by' => $staff->id, 'status' => PatientQueue::STATUS_WAITING,
        ]);

        $this->actingAs($staff)->deleteJson(route('staff.patients.destroy', $patient))->assertOk();

        $this->assertNotNull(Patient::withTrashed()->find($patient->id)->archived_at);
        $this->assertSame(PatientQueue::STATUS_CANCELLED, $waiting->fresh()->status);
        $this->assertNotNull($waiting->fresh()->completed_at);

        $log = ActivityLog::where('action', 'deleted_patient')->where('subject_id', $patient->id)->first();
        $this->assertNotNull($log, 'archiving a patient must leave an audit row');
        $this->assertSame($staff->id, (int) $log->user_id);

        // Restoring is audited too.
        $this->postJson(route('staff.patients.restore', $patient->id))->assertOk();
        $this->assertNotNull(ActivityLog::where('action', 'restored_patient')->where('subject_id', $patient->id)->first());
    }

    public function test_a_new_patient_is_immediately_selectable_in_the_prescription_form(): void
    {
        $staff = $this->makeStaff('clinic_staff', 'front_desk');
        $repository = app(PrescriptionRepository::class);

        $this->assertCount(0, $repository->getPatients()); // primes the 10-minute dropdown cache

        $this->actingAs($staff)->post(route('staff.patients.store'), $this->patientPayload())->assertRedirect();

        $names = $repository->getPatients()->values()->all();
        $this->assertContains('Maria Santos', $names);
    }

    public function test_excuse_slips_appear_in_the_patient_history(): void
    {
        $staff = $this->makeStaff('records_officer', 'records_area');
        $patient = $this->makePatient();
        DocumentIssuance::create([
            'document_type' => 'excuse_slip',
            'document_creator_id' => $staff->id,
            'user_id' => $patient->user_id,
            'name' => 'Juan Dela Cruz',
            'age' => 23,
            'gender' => 'Male',
            'address' => 'Dumaguete',
            'requested_at' => now()->toDateString(),
        ]);

        $response = $this->actingAs($staff)->get(route('staff.patients.showMyHistory', $patient))->assertOk();

        $this->assertCount(1, $response->viewData('excuseSlips'));
    }
}
