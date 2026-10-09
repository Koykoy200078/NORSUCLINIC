<?php

namespace Tests\Feature\Regression;

use App\Http\Controllers\PatientQueueController;
use App\Models\DocumentIssuance;
use App\Models\Patient;
use App\Models\PatientQueue;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\Concerns\BuildsClinicData;
use Tests\TestCase;

/**
 * Regression tests for the patient queue (2026-10-01 re-audit): staff/nurse put a patient in the
 * shared queue, any doctor calls and completes them.
 */
class PatientQueueTest extends TestCase
{
    use RefreshDatabase;
    use BuildsClinicData;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedAccessControl();
    }

    private function queueEntry(Patient $patient, User $addedBy, array $overrides = []): PatientQueue
    {
        $entry = (new PatientQueue())->forceFill(array_merge([
            'patient_id' => $patient->id,
            'added_by' => $addedBy->id,
            'status' => PatientQueue::STATUS_WAITING,
        ], $overrides));
        $entry->save();

        return $entry;
    }

    public function test_staff_queue_a_patient_and_a_doctor_calls_and_completes_them(): void
    {
        $front = $this->makeStaff('clinic_staff', 'front_desk');
        $doctor = $this->makeDoctor();
        $patient = $this->makePatient(['first_name' => 'Queuey', 'last_name' => 'McQueueface']);

        $this->actingAs($front)->post(route('staff.patient-queue.store'), [
            'patient_id' => $patient->id,
            'is_priority' => 1,
            'room_number' => 'Room 2',
            'notes' => 'fever',
        ])->assertRedirect(route('staff.patient-queue.index'));

        $entry = PatientQueue::firstOrFail();
        $this->assertSame(PatientQueue::STATUS_WAITING, $entry->status);
        $this->assertSame($front->id, (int) $entry->added_by);
        $this->assertTrue($entry->is_priority);

        // The staff screen and the doctor screen (full page and the auto-refresh partial) show them.
        $this->get(route('staff.patient-queue.index'))->assertOk()->assertSee('Queuey McQueueface');
        $this->get(route('staff.patient-queue.refresh'))->assertOk()->assertSee('Queuey McQueueface');

        $this->actingAs($doctor);
        $this->get(route('doctors.patient-queue.index'))->assertOk()->assertSee('Queuey McQueueface');
        $this->get(route('doctors.patient-queue.refresh'))->assertOk()->assertSee('Queuey McQueueface');

        $this->post(route('doctors.patient-queue.call-next', $entry))->assertRedirect();
        $this->assertSame(PatientQueue::STATUS_IN_PROGRESS, $entry->fresh()->status);
        $this->assertNotNull($entry->fresh()->called_at);

        $this->postJson(route('doctors.patient-queue.complete', $entry))->assertOk()->assertJson(['success' => true]);
        $this->assertSame(PatientQueue::STATUS_COMPLETED, $entry->fresh()->status);
        $this->assertNotNull($entry->fresh()->completed_at);
    }

    public function test_only_staff_with_the_patients_permission_may_queue_and_roles_stay_in_their_lane(): void
    {
        $doctor = $this->makeDoctor();
        $nurse = $this->makeNurse();
        $patient = $this->makePatient();

        // The queue belongs to the patients permission: without it a staff account cannot queue anybody.
        \App\Models\Role::findByName('staff')->revokePermissionTo('manage_patients');
        $this->actingAs($nurse->fresh())->post(route('staff.patient-queue.store'), ['patient_id' => $patient->id])->assertForbidden();
        $this->actingAs($doctor)->post(route('staff.patient-queue.store'), ['patient_id' => $patient->id])->assertForbidden();
        $this->assertSame(0, PatientQueue::count());

        \App\Models\Role::findByName('staff')->givePermissionTo('manage_patients');
        $nurse = $nurse->fresh();

        // Doctors do not use the staff screens and staff do not call or complete from the doctor screens.
        $entry = $this->queueEntry($patient, $nurse);
        $this->actingAs($nurse)->get(route('doctors.patient-queue.index'))->assertForbidden();
        $this->actingAs($nurse)->post(route('doctors.patient-queue.call-next', $entry))->assertForbidden();

        $this->actingAs($nurse)->post(route('staff.patient-queue.store'), ['patient_id' => $patient->id])
            ->assertSessionHas('error'); // already queued
        $this->assertSame(1, PatientQueue::count());
    }

    public function test_an_archived_or_deactivated_patient_cannot_be_queued(): void
    {
        $front = $this->makeStaff('clinic_staff', 'front_desk');
        $archived = $this->makePatient();
        $archived->delete();
        $inactive = $this->makePatient();
        $inactive->user->update(['status' => 0]);

        $this->actingAs($front);
        $this->post(route('staff.patient-queue.store'), ['patient_id' => $archived->id])->assertSessionHasErrors();
        $this->assertStringContainsString('archived', session('errors')->getBag('default')->first());
        $this->post(route('staff.patient-queue.store'), ['patient_id' => $inactive->id])->assertSessionHasErrors();
        $this->assertStringContainsString('not active', session('errors')->getBag('default')->first());

        $this->assertSame(0, PatientQueue::count());
    }

    public function test_the_queue_screens_survive_a_queued_patient_who_was_archived_or_whose_adder_was_removed(): void
    {
        $front = $this->makeStaff('clinic_staff', 'front_desk');
        $doctor = $this->makeDoctor();
        $adder = $this->makeStaff('nurse', 'triage_area');
        $archivedPatient = $this->makePatient();
        $healthyPatient = $this->makePatient(['first_name' => 'Healthy', 'last_name' => 'Person']);
        $this->queueEntry($archivedPatient, $front);
        $this->queueEntry($healthyPatient, $adder);

        // Old data: the patient was archived without the queue being touched, and the staff member who
        // queued the other patient has since been archived.
        DB::table('patients')->where('id', $archivedPatient->id)->update(['archived_at' => now()]);
        $adder->delete();

        foreach ([
            [$front, 'staff.patient-queue.index'],
            [$front, 'staff.patient-queue.refresh'],
            [$doctor, 'doctors.patient-queue.index'],
            [$doctor, 'doctors.patient-queue.refresh'],
        ] as [$user, $routeName]) {
            $this->actingAs($user)->get(route($routeName))->assertOk()->assertSee('Healthy Person');
        }
    }

    public function test_entries_left_over_from_a_previous_day_are_closed_and_do_not_block_requeuing(): void
    {
        $front = $this->makeStaff('clinic_staff', 'front_desk');
        $patient = $this->makePatient();
        $yesterday = now()->subDay()->setTime(10, 0);
        $stale = $this->queueEntry($patient, $front, ['created_at' => $yesterday, 'updated_at' => $yesterday]);
        $staleInProgress = $this->queueEntry($this->makePatient(), $front, [
            'status' => PatientQueue::STATUS_IN_PROGRESS, 'called_at' => $yesterday,
            'created_at' => $yesterday, 'updated_at' => $yesterday,
        ]);

        // Today the patient can be queued again - yesterday's forgotten place used to block this forever.
        $this->actingAs($front)->post(route('staff.patient-queue.store'), ['patient_id' => $patient->id])
            ->assertSessionMissing('error')
            ->assertRedirect(route('staff.patient-queue.index'));

        $this->assertSame(PatientQueue::STATUS_CANCELLED, $stale->fresh()->status);
        $this->assertSame(PatientQueue::STATUS_CANCELLED, $staleInProgress->fresh()->status);
        $this->assertNotNull($stale->fresh()->completed_at);
        $this->assertSame(1, PatientQueue::where('patient_id', $patient->id)->where('status', PatientQueue::STATUS_WAITING)->count());
    }

    public function test_the_doctor_screen_does_not_list_yesterdays_waiting_patients(): void
    {
        $front = $this->makeStaff('clinic_staff', 'front_desk');
        $doctor = $this->makeDoctor();
        $yesterday = now()->subDay();
        $this->queueEntry($this->makePatient(['first_name' => 'Yesterday', 'last_name' => 'Visitor']), $front, [
            'created_at' => $yesterday, 'updated_at' => $yesterday,
        ]);

        $this->actingAs($doctor)->get(route('doctors.patient-queue.index'))->assertOk()->assertDontSee('Yesterday Visitor');
    }

    public function test_a_closed_queue_entry_cannot_be_reopened_by_editing_it(): void
    {
        $front = $this->makeStaff('clinic_staff', 'front_desk');
        $entry = $this->queueEntry($this->makePatient(), $front, [
            'status' => PatientQueue::STATUS_COMPLETED, 'completed_at' => now(),
        ]);

        $this->actingAs($front)->put(route('staff.patient-queue.update', $entry), [
            'status' => PatientQueue::STATUS_WAITING,
            'room_number' => 'Room 9',
        ])->assertSessionHas('error');

        $this->assertSame(PatientQueue::STATUS_COMPLETED, $entry->fresh()->status);
        $this->assertNull($entry->fresh()->room_number);

        // An open entry can still be edited and cancelled.
        $open = $this->queueEntry($this->makePatient(), $front);
        $this->put(route('staff.patient-queue.update', $open), ['status' => PatientQueue::STATUS_CANCELLED, 'room_number' => 'Room 3'])
            ->assertSessionHas('success');
        $this->assertSame(PatientQueue::STATUS_CANCELLED, $open->fresh()->status);
        $this->assertNotNull($open->fresh()->completed_at);
    }

    public function test_two_doctors_cannot_call_the_same_patient(): void
    {
        $front = $this->makeStaff('clinic_staff', 'front_desk');
        $entry = $this->queueEntry($this->makePatient(), $front);

        // Doctor B opened the page while the patient was still waiting; doctor A then called them.
        $stale = PatientQueue::findOrFail($entry->id);
        $calledAt = now()->subMinutes(5)->startOfSecond();
        $entry->update(['status' => PatientQueue::STATUS_IN_PROGRESS, 'called_at' => $calledAt]);

        $response = app(PatientQueueController::class)->callNext($stale);

        $this->assertSame('This patient is no longer waiting in the queue.', $response->getSession()->get('error'));
        $this->assertEquals($calledAt, $entry->fresh()->called_at, 'the first call time must not be overwritten');
    }

    public function test_a_consultation_recorded_after_queuing_is_attached_to_the_open_queue_entry(): void
    {
        $nurse = $this->makeStaff('nurse', 'triage_area');
        $patient = $this->makePatient();
        $entry = $this->queueEntry($patient, $nurse);
        $this->assertNull($entry->latest_consultation_id);

        $this->actingAs($nurse)->post(route('staff.document-issuances.store'), [
            'document_type' => 'consultation_form',
            'user_id' => $patient->user_id,
            'name' => 'Juan Dela Cruz',
            'date_of_birth' => '2003-05-01',
            'gender' => 'Male',
            'requested_at' => now()->toDateString(),
            'consult_mode' => 'physical',
            'complaints' => 'Cough',
            'nursing_incharged' => $nurse->id,
        ])->assertRedirect();

        $consultation = DocumentIssuance::where('document_type', 'consultation_form')->firstOrFail();
        $entry->refresh();
        $this->assertSame($consultation->id, (int) $entry->latest_consultation_id);
        $this->assertTrue((bool) $entry->has_consultation_attachment);

        // ... so the doctor can open the form from the queue.
        $doctor = $this->makeDoctor();
        $this->actingAs($doctor)->get(route('doctors.patient-queue.view-consultation', $entry))
            ->assertRedirect(route('doctors.document-issuances.show', $consultation));
    }
}
