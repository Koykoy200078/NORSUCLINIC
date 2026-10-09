<?php

namespace Tests\Feature\Regression;

use App\Models\Doctor;
use App\Models\DocumentIssuance;
use App\Models\Patient;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\BuildsClinicData;
use Tests\TestCase;

/**
 * 2026-10-02 route/permission audit: every route was visited as every kind of user (guest, admin, doctor, each
 * staff designation + station, nurse role, patient, deactivated). These are the defects that audit found.
 */
class RouteAccessAuditTest extends TestCase
{
    use RefreshDatabase;
    use BuildsClinicData;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedAccessControl();
    }

    // ---- a route that lists several modules lets in anyone who has ANY of them -------------------------------

    /**
     * `staff.module:a,b` is split by Laravel at the comma into two middleware parameters, and the middleware only
     * took one, so only the FIRST module was ever checked: front-desk and records staff (certificates) could not
     * search for the patient of a certificate, and the pharmacist could not load the medicine list.
     */
    public function test_a_route_open_to_two_modules_admits_staff_who_have_only_the_second_one(): void
    {
        $certificatesOnly = [
            $this->makeStaff('clinic_staff', 'front_desk'),
            $this->makeStaff('records_officer', 'records_area'),
        ];

        foreach ($certificatesOnly as $user) {
            $this->actingAs($user)->getJson(route('staff.document-issuances.search-users', ['query' => 'ab', 'document_type' => 'medical_certificate']))
                ->assertSuccessful();
            $this->get(route('staff.document-issuances.search-users', ['query' => 'ab']))
                ->assertSuccessful();
        }

        $this->actingAs($this->makeStaff('pharmacist', 'pharmacy'))
            ->get(route('staff.medicines.by.category'))->assertStatus(200);
    }

    public function test_a_consultations_only_nurse_can_read_the_latest_consultation_of_a_patient(): void
    {
        $patient = $this->makePatient();
        $nurse = $this->makeStaff('nurse', 'medical_consultation');
        \App\Models\Role::findByName('staff')->revokePermissionTo('manage_patients');

        $this->actingAs($nurse)->get('/api/patient/' . $patient->id . '/latest-consultation')->assertSuccessful();
    }

    public function test_staff_without_either_module_are_still_refused(): void
    {
        $observation = $this->makeStaff('nurse', 'observation_room');   // patients + queue only
        $pharmacist = $this->makeStaff('pharmacist', 'pharmacy');

        \App\Models\Role::findByName('staff')->revokePermissionTo(['manage_request_documents', 'manage_medicines']);
        $this->actingAs($observation->fresh())->get(route('staff.document-issuances.search-users', ['query' => 'ab']))->assertForbidden();
        $this->get(route('staff.medicines.by.category'))->assertForbidden();
        $this->actingAs($pharmacist)->get(route('staff.document-issuances.search-users', ['query' => 'ab']))->assertForbidden();
    }

    // ---- public and shared pages -----------------------------------------------------------------------------

    public function test_the_public_doctors_page_survives_a_doctor_with_no_specialization(): void
    {
        $this->makeDoctor();   // no specialization row

        $this->get(route('medicalDoctors'))->assertOk();
    }

    public function test_email_verification_and_password_confirmation_send_each_role_to_its_own_dashboard(): void
    {
        $this->actingAs($this->makeDoctor())->get(route('verification.notice'))->assertRedirect(route('doctors.dashboard'));
        $this->actingAs($this->makeStaff('nurse', 'triage_area'))->get(route('verification.notice'))->assertRedirect(route('staff.dashboard'));
        $this->actingAs($this->makeAdmin())->get(route('verification.notice'))->assertRedirect(route('admin.dashboard'));

        $doctor = $this->makeDoctor(['password' => bcrypt('Secret#123')]);
        $this->actingAs($doctor)->post(route('password.confirm'), ['password' => 'Secret#123'])->assertRedirect(route('doctors.dashboard'));
    }

    public function test_leaving_impersonation_when_not_impersonating_goes_to_the_own_dashboard(): void
    {
        $this->actingAs($this->makeDoctor())->get(route('impersonate.leave'))->assertRedirect(route('doctors.dashboard'));
        $this->actingAs($this->makeStaff('nurse', 'triage_area'))->get(route('impersonate.leave'))->assertRedirect(route('staff.dashboard'));
    }

    // ---- settings -------------------------------------------------------------------------------------------------

    public function test_saving_settings_without_a_section_is_refused_not_a_server_error(): void
    {
        $this->actingAs($this->makeAdmin());

        $this->post(route('setting.update'), [])->assertSessionHasErrors();
        $this->postJson(route('setting.update'), [])->assertStatus(422);
    }

    // ---- an incomplete update must not blank a consultation ------------------------------------------------------

    public function test_an_update_that_arrives_without_the_form_fields_does_not_erase_the_consultation(): void
    {
        $doctor = $this->makeDoctor();
        $patient = $this->makePatient();
        $visit = DocumentIssuance::create([
            'document_type' => 'consultation_form', 'document_creator_id' => $doctor->id, 'user_id' => $patient->user_id,
            'name' => 'Juan Dela Cruz', 'age' => 20, 'gender' => 'Male', 'address' => 'Dumaguete', 'requested_at' => '2026-03-10',
            'complaints' => 'cough for 3 days', 'assessment' => 'URTI', 'plan' => 'rest', 'allergies' => 'penicillin',
            'vital_signs_bp' => '120/80', 'informant' => 'Student', 'consult_mode' => 'physical',
            'nursing_intervention' => 'given paracetamol',
        ]);

        $this->actingAs($doctor)->put(route('doctors.document-issuances.update', $visit), []);

        $visit->refresh();
        $this->assertSame('cough for 3 days', $visit->complaints);
        $this->assertSame('URTI', $visit->assessment);
        $this->assertSame('rest', $visit->plan);
        $this->assertSame('penicillin', $visit->allergies);
        $this->assertSame('120/80', $visit->vital_signs_bp);
        $this->assertSame('given paracetamol', $visit->nursing_intervention);
        $this->assertSame('Juan Dela Cruz', $visit->name);
    }
}
