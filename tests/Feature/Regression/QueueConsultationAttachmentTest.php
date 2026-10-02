<?php

namespace Tests\Feature\Regression;

use App\Models\DocumentIssuance;
use App\Models\Patient;
use App\Models\PatientQueue;
use App\Models\User;
use App\Services\DeletedRecordRestorer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\BuildsClinicData;
use Tests\TestCase;

/**
 * Queue -> consultation -> doctor: staff/nurse queue a patient, the consultation recorded for that patient (before or
 * after queuing, by a nurse or by the doctor) is attached to the open queue entry, and the doctor's screen - which
 * refreshes itself every 5 seconds from the same partial used here - shows it as the NEW form of this visit, tells it
 * apart from an earlier visit's form, and never points at a form that was deleted.
 */
class QueueConsultationAttachmentTest extends TestCase
{
    use RefreshDatabase;
    use BuildsClinicData;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedAccessControl();
    }

    private function consultationPayload(Patient $patient, User $nurse, array $overrides = []): array
    {
        return array_merge([
            'document_type' => 'consultation_form',
            'user_id' => $patient->user_id,
            'name' => $patient->user->full_name,
            'date_of_birth' => '2003-05-01',
            'gender' => 'Male',
            'requested_at' => now()->toDateString(),
            'consult_mode' => 'physical',
            'complaints' => 'Persistent cough',
            'nursing_incharged' => $nurse->id,
        ], $overrides);
    }

    private function earlierConsultation(Patient $patient, User $creator, int $daysAgo = 40): DocumentIssuance
    {
        $form = DocumentIssuance::create([
            'document_type' => 'consultation_form', 'document_creator_id' => $creator->id, 'user_id' => $patient->user_id,
            'name' => $patient->user->full_name, 'age' => 20, 'gender' => 'Male', 'address' => 'Dumaguete',
            'requested_at' => now()->subDays($daysAgo)->toDateString(), 'complaints' => 'Old headache',
        ]);
        $form->forceFill(['created_at' => now()->subDays($daysAgo), 'updated_at' => now()->subDays($daysAgo)])->save();

        return $form;
    }

    private function doctorScreen(User $doctor)
    {
        // exactly what the doctor's browser fetches every 5 seconds
        return $this->actingAs($doctor)->get(route('doctors.patient-queue.refresh'));
    }

    // ---- nurse queues first, records the form afterwards -------------------------------------------------------------

    public function test_a_consultation_recorded_after_queuing_shows_on_the_doctors_screen_as_the_new_form(): void
    {
        $nurse = $this->makeStaff('nurse', 'triage_area');
        $doctor = $this->makeDoctor();
        $patient = $this->makePatient(['first_name' => 'Nina', 'last_name' => 'Nurseq']);

        $this->actingAs($nurse)->post(route('staff.patient-queue.store'), ['patient_id' => $patient->id])
            ->assertRedirect()->assertSessionHas('success');
        $this->assertStringContainsString('No consultation form yet', session('success'));

        // Nothing recorded yet: the doctor sees the patient, no form, and a way to record one.
        $before = $this->doctorScreen($doctor)->assertOk()->assertSee('Nina Nurseq')->assertDontSee('New form')->assertDontSee('Previous form');
        $before->assertSee('Record form');

        // The nurse records the consultation for this patient ...
        $this->actingAs($nurse)->post(route('staff.document-issuances.store'), $this->consultationPayload($patient, $nurse))->assertRedirect();
        $form = DocumentIssuance::where('document_type', 'consultation_form')->firstOrFail();

        // ... and on the doctor's next refresh it is there, marked as this visit's form.
        $this->doctorScreen($doctor)->assertOk()->assertSee('Nina Nurseq')->assertSee('New form')->assertSee("This visit's consultation:")
            ->assertDontSee('Previous form')->assertDontSee('Record form');

        $entry = PatientQueue::firstOrFail();
        $this->assertSame($form->id, (int) $entry->latest_consultation_id);
        $this->assertTrue($entry->attached_form_is_new);

        // The doctor opens it from the queue and reads what the nurse wrote.
        $this->actingAs($doctor)->get(route('doctors.patient-queue.view-consultation', $entry))
            ->assertRedirect(route('doctors.document-issuances.show', $form));
        $this->get(route('doctors.document-issuances.show', $form))->assertOk()->assertSee('Persistent cough');

        // The staff screen agrees, and no longer offers to record one.
        $this->actingAs($nurse)->get(route('staff.patient-queue.refresh'))->assertOk()->assertSee('Has form (this visit)')
            ->assertDontSee('Earlier form only');
    }

    public function test_the_doctor_sees_it_when_the_doctor_records_the_form_too(): void
    {
        $nurse = $this->makeStaff('nurse', 'triage_area');
        $doctor = $this->makeDoctor();
        $patient = $this->makePatient(['first_name' => 'Dora', 'last_name' => 'Doctorform']);
        $this->actingAs($nurse)->post(route('staff.patient-queue.store'), ['patient_id' => $patient->id, 'is_priority' => 1]);

        $this->actingAs($doctor)->post(route('doctors.document-issuances.store'), $this->consultationPayload($patient, $nurse, ['complaints' => 'Chest pain']))
            ->assertRedirect();

        $this->doctorScreen($doctor)->assertSee('Dora Doctorform')->assertSee('New form');
        $this->assertTrue(PatientQueue::firstOrFail()->attached_form_is_new);
    }

    // ---- the form already exists when the patient is queued ------------------------------------------------------------

    public function test_a_form_recorded_earlier_today_is_attached_the_moment_the_patient_is_queued(): void
    {
        $nurse = $this->makeStaff('nurse', 'triage_area');
        $doctor = $this->makeDoctor();
        $patient = $this->makePatient(['first_name' => 'Early', 'last_name' => 'Bird']);

        $this->actingAs($nurse)->post(route('staff.document-issuances.store'), $this->consultationPayload($patient, $nurse))->assertRedirect();
        $this->post(route('staff.patient-queue.store'), ['patient_id' => $patient->id])->assertSessionHas('success');
        $this->assertStringContainsString("Today's consultation form is attached", session('success'));

        $this->doctorScreen($doctor)->assertSee('Early Bird')->assertSee('New form');
    }

    public function test_an_earlier_visits_form_is_labelled_previous_until_todays_is_recorded(): void
    {
        $nurse = $this->makeStaff('nurse', 'triage_area');
        $doctor = $this->makeDoctor();
        $patient = $this->makePatient(['first_name' => 'Return', 'last_name' => 'Visitor']);
        $old = $this->earlierConsultation($patient, $nurse, 40);

        $this->actingAs($nurse)->post(route('staff.patient-queue.store'), ['patient_id' => $patient->id])->assertSessionHas('success');
        $this->assertStringContainsString('previous consultation form', strtolower(session('success')));

        // The doctor may read the old form, but is told it is not today's.
        $this->doctorScreen($doctor)->assertSee('Return Visitor')->assertSee('Previous form')->assertSee('Previous consultation:')
            ->assertDontSee('New form')->assertSee('Record form');
        $this->assertSame($old->id, (int) PatientQueue::firstOrFail()->latest_consultation_id);
        $this->actingAs($nurse)->get(route('staff.patient-queue.refresh'))->assertSee('Earlier form only');

        // Today's form replaces it.
        $this->actingAs($nurse)->post(route('staff.document-issuances.store'), $this->consultationPayload($patient, $nurse, ['complaints' => 'Fever today']))->assertRedirect();

        $entry = PatientQueue::firstOrFail();
        $this->assertNotSame($old->id, (int) $entry->latest_consultation_id);
        $this->doctorScreen($doctor)->assertSee('New form')->assertDontSee('Previous form');
    }

    // ---- what must NOT be attached -------------------------------------------------------------------------------------

    public function test_a_certificate_or_another_patients_form_is_not_attached(): void
    {
        $nurse = $this->makeStaff('nurse', 'triage_area');
        $waiting = $this->makePatient(['first_name' => 'Waiting', 'last_name' => 'Person', 'email' => 'waiting@test.local']);
        $other = $this->makePatient(['first_name' => 'Other', 'last_name' => 'Person', 'email' => 'other@test.local']);
        $this->actingAs($nurse)->post(route('staff.patient-queue.store'), ['patient_id' => $waiting->id]);

        // a form for somebody else
        $this->post(route('staff.document-issuances.store'), $this->consultationPayload($other, $nurse))->assertRedirect();
        // a certificate for the queued patient
        DocumentIssuance::create([
            'document_type' => 'medical_certificate', 'document_creator_id' => $nurse->id, 'user_id' => $waiting->user_id,
            'name' => 'Waiting Person', 'age' => 20, 'gender' => 'Male', 'address' => 'Dumaguete', 'requested_at' => now()->toDateString(),
        ]);

        $entry = PatientQueue::where('patient_id', $waiting->id)->firstOrFail();
        $this->assertNull($entry->latest_consultation_id);
        $this->assertFalse((bool) $entry->has_consultation_attachment);
    }

    public function test_a_finished_queue_entry_is_not_changed_by_a_later_form(): void
    {
        $nurse = $this->makeStaff('nurse', 'triage_area');
        $patient = $this->makePatient();
        $this->actingAs($nurse)->post(route('staff.patient-queue.store'), ['patient_id' => $patient->id]);
        PatientQueue::firstOrFail()->update(['status' => PatientQueue::STATUS_COMPLETED, 'completed_at' => now()]);

        $this->post(route('staff.document-issuances.store'), $this->consultationPayload($patient, $nurse))->assertRedirect();

        $this->assertNull(PatientQueue::firstOrFail()->latest_consultation_id, 'a closed entry is history');
    }

    // ---- delete / restore ----------------------------------------------------------------------------------------------

    public function test_deleting_the_attached_form_falls_back_to_the_previous_one_or_to_nothing_and_restoring_brings_it_back(): void
    {
        $nurse = $this->makeStaff('nurse', 'triage_area');
        $doctor = $this->makeDoctor();
        $admin = $this->makeAdmin();
        $patient = $this->makePatient(['first_name' => 'Gone', 'last_name' => 'Form']);
        $old = $this->earlierConsultation($patient, $nurse, 40);

        $this->actingAs($nurse)->post(route('staff.patient-queue.store'), ['patient_id' => $patient->id]);
        $this->post(route('staff.document-issuances.store'), $this->consultationPayload($patient, $nurse))->assertRedirect();
        $new = DocumentIssuance::where('complaints', 'Persistent cough')->firstOrFail();
        $this->assertSame($new->id, (int) PatientQueue::firstOrFail()->latest_consultation_id);

        // The nurse deletes today's form: the entry goes back to the earlier visit's form, labelled as such.
        $this->delete(route('staff.document-issuances.destroy', $new))->assertRedirect();
        $entry = PatientQueue::firstOrFail();
        $this->assertSame($old->id, (int) $entry->latest_consultation_id);
        $this->doctorScreen($doctor)->assertSee('Previous form')->assertDontSee('New form');

        // Deleting that one as well leaves nothing attached - on both screens.
        $this->actingAs($admin)->delete(route('document-issuances.destroy', $old))->assertRedirect();
        $entry = PatientQueue::firstOrFail();
        $this->assertNull($entry->latest_consultation_id);
        $this->assertFalse((bool) $entry->has_consultation_attachment);
        $this->doctorScreen($doctor)->assertSee('Gone Form')->assertDontSee('Previous form')->assertDontSee('New form');
        $this->actingAs($nurse)->get(route('staff.patient-queue.refresh'))->assertDontSee('Has form')->assertDontSee('Earlier form only');

        // Restoring today's form attaches it again.
        $this->actingAs($admin);
        app(DeletedRecordRestorer::class)->restoreDocument($new->id);
        $this->assertSame($new->id, (int) PatientQueue::firstOrFail()->latest_consultation_id);
        $this->doctorScreen($doctor)->assertSee('New form');
    }

    public function test_a_flag_left_on_by_a_deleted_form_does_not_show_a_phantom_form_on_either_screen(): void
    {
        $nurse = $this->makeStaff('nurse', 'triage_area');
        $doctor = $this->makeDoctor();
        $patient = $this->makePatient(['first_name' => 'Phantom', 'last_name' => 'Flag']);
        $form = $this->earlierConsultation($patient, $nurse, 0);
        $this->actingAs($nurse)->post(route('staff.patient-queue.store'), ['patient_id' => $patient->id]);

        // delete the form behind the queue's back (old rows, direct database edits)
        $form->delete();
        $this->assertTrue((bool) PatientQueue::firstOrFail()->has_consultation_attachment);

        $this->doctorScreen($doctor)->assertOk()->assertSee('Phantom Flag')->assertDontSee('New form')->assertDontSee('Previous form');
        $this->actingAs($nurse)->get(route('staff.patient-queue.refresh'))->assertOk()->assertSee('Phantom Flag')
            ->assertDontSee('Has form')->assertDontSee('Earlier form only');
    }

    // ---- the Record form button ----------------------------------------------------------------------------------------

    public function test_the_record_form_button_opens_the_form_for_exactly_the_queued_patient(): void
    {
        $nurse = $this->makeStaff('nurse', 'triage_area');
        $doctor = $this->makeDoctor();
        $patient = $this->makePatient(['first_name' => 'Exact', 'last_name' => 'Match']);
        // a namesake, to prove the form is opened for the right person and not found by name
        $this->makePatient(['first_name' => 'Exact', 'last_name' => 'Match', 'email' => 'namesake@test.local']);
        $this->actingAs($nurse)->post(route('staff.patient-queue.store'), ['patient_id' => $patient->id]);

        $createUrl = route('staff.document-issuances.create', ['document_type' => 'consultation_form', 'module' => 'consultation', 'user_id' => $patient->user_id]);

        $this->actingAs($nurse)->get(route('staff.patient-queue.refresh'))->assertSee(e($createUrl), false);
        $this->get($createUrl)->assertOk()->assertSee('Exact Match');

        $doctorCreate = route('doctors.document-issuances.create', ['document_type' => 'consultation_form', 'module' => 'consultation', 'user_id' => $patient->user_id]);
        $this->doctorScreen($doctor)->assertSee(e($doctorCreate), false);
    }

    public function test_staff_without_the_consultations_module_are_not_offered_the_record_button(): void
    {
        $front = $this->makeStaff('clinic_staff', 'front_desk');          // patients + queue + certificates, no consultations
        $patient = $this->makePatient();
        $this->actingAs($front)->post(route('staff.patient-queue.store'), ['patient_id' => $patient->id])->assertRedirect();

        $this->get(route('staff.patient-queue.refresh'))->assertOk()
            ->assertDontSee('document_type=consultation_form');
    }
}
