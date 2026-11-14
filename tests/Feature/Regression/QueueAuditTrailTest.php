<?php

namespace Tests\Feature\Regression;

use App\Models\ActivityLog;
use App\Models\PatientQueue;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\BuildsClinicData;
use Tests\TestCase;

/**
 * STATUS section 6, "Audit trail for the queue" (the owner, 2026-11-14: log the queue actions). Adding, calling, completing,
 * cancelling and removing a queue entry each leave an audit row (who, whom, when). Deleting an entry that was already
 * called no longer removes it: it is kept and marked cancelled; a finished entry (completed or cancelled) stays as it is.
 */
class QueueAuditTrailTest extends TestCase
{
    use RefreshDatabase;
    use BuildsClinicData;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedAccessControl();
    }

    private function rows(string $action, PatientQueue $entry): int
    {
        return ActivityLog::where('action', $action)->where('subject_type', PatientQueue::class)->where('subject_id', $entry->id)->count();
    }

    public function test_adding_calling_and_completing_are_written_to_the_audit_trail(): void
    {
        $nurse = $this->makeNurse();
        $doctor = $this->makeDoctor();
        $patient = $this->makePatient(['first_name' => 'Ana', 'last_name' => 'Reyes']);

        $this->actingAs($nurse)->post(route('staff.patient-queue.store'), ['patient_id' => $patient->id])->assertRedirect();
        $entry = PatientQueue::where('patient_id', $patient->id)->firstOrFail();
        $this->assertSame(1, $this->rows('queue_added', $entry));
        $added = ActivityLog::where('action', 'queue_added')->firstOrFail();
        $this->assertSame($nurse->id, (int) $added->user_id);
        $this->assertStringContainsString('Ana Reyes', $added->description);

        $this->actingAs($doctor)->post(route('doctors.patient-queue.call-next', $entry))->assertRedirect();
        $this->assertSame(1, $this->rows('queue_called', $entry));

        $this->post(route('doctors.patient-queue.complete', $entry))->assertRedirect();
        $this->assertSame(1, $this->rows('queue_completed', $entry));
        $this->assertSame('Queue Completed', ActivityLog::where('action', 'queue_completed')->first()->formatted_action);

        // a refused second completion writes nothing
        $this->post(route('doctors.patient-queue.complete', $entry));
        $this->assertSame(1, $this->rows('queue_completed', $entry));
    }

    public function test_the_edit_form_logs_a_call_and_a_cancellation(): void
    {
        $this->actingAs($this->makeNurse());
        $entry = PatientQueue::create(['patient_id' => $this->makePatient()->id, 'added_by' => auth()->id(), 'status' => PatientQueue::STATUS_WAITING]);

        $this->put(route('staff.patient-queue.update', $entry), ['status' => PatientQueue::STATUS_IN_PROGRESS])->assertRedirect();
        $this->assertSame(1, $this->rows('queue_called', $entry));

        $this->put(route('staff.patient-queue.update', $entry), ['status' => PatientQueue::STATUS_CANCELLED])->assertRedirect();
        $this->assertSame(1, $this->rows('queue_cancelled', $entry));

        // only the status changes are actions: a room change on an open entry writes no row
        $other = PatientQueue::create(['patient_id' => $this->makePatient(['email' => 'other@test.local'])->id, 'added_by' => auth()->id(), 'status' => PatientQueue::STATUS_WAITING]);
        $this->put(route('staff.patient-queue.update', $other), ['room_number' => '2'])->assertRedirect();
        $this->assertSame(0, ActivityLog::where('subject_type', PatientQueue::class)->where('subject_id', $other->id)->count());
    }

    public function test_removing_a_waiting_entry_deletes_it_and_a_called_entry_is_cancelled_instead(): void
    {
        $this->actingAs($this->makeNurse());
        $make = fn (string $status, array $extra = []) => PatientQueue::create(array_merge([
            'patient_id' => $this->makePatient(['email' => uniqid('q') . '@test.local'])->id, 'added_by' => auth()->id(), 'status' => $status,
        ], $extra));

        $waiting = $make(PatientQueue::STATUS_WAITING);
        $this->delete(route('staff.patient-queue.destroy', $waiting))->assertRedirect()->assertSessionHas('success');
        $this->assertNull(PatientQueue::find($waiting->id));
        $this->assertSame(1, $this->rows('queue_removed', $waiting));

        $called = $make(PatientQueue::STATUS_IN_PROGRESS, ['called_at' => now()->subMinutes(5)]);
        $this->delete(route('staff.patient-queue.destroy', $called))->assertRedirect()->assertSessionHas('success');
        $this->assertSame(PatientQueue::STATUS_CANCELLED, $called->fresh()->status);
        $this->assertNotNull($called->fresh()->completed_at);
        $this->assertSame(1, $this->rows('queue_cancelled', $called));
        $this->assertSame(0, $this->rows('queue_removed', $called));

        $completed = $make(PatientQueue::STATUS_COMPLETED, ['called_at' => now()->subHour(), 'completed_at' => now()->subMinutes(30)]);
        $this->delete(route('staff.patient-queue.destroy', $completed))->assertRedirect()->assertSessionHas('error');
        $this->assertSame(PatientQueue::STATUS_COMPLETED, $completed->fresh()->status);
        $this->assertSame(0, ActivityLog::where('subject_type', PatientQueue::class)->where('subject_id', $completed->id)->count());
    }
}
