<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePatientQueueRequest;
use App\Http\Requests\UpdatePatientQueueRequest;
use App\Models\Patient;
use App\Models\PatientQueue;
use App\Support\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PatientQueueController extends Controller
{
    /**
     * Today's open queue (waiting + in progress). Entries left over from earlier days are closed first,
     * and entries whose patient was archived are left out - the screens cannot render a missing patient.
     */
    private function openQueues()
    {
        PatientQueue::closeStaleEntries();

        return PatientQueue::with(['patient.user', 'addedBy', 'latestConsultation'])
            ->withLivePatient()
            ->whereIn('status', PatientQueue::OPEN_STATUSES)
            ->orderByQueue()
            ->get();
    }

    /**
     * One audit row per queue action (the owner's decision, 2026-11-14, STATUS section 6): who added, called, completed,
     * cancelled or removed which patient, and when. Actions: queue_added, queue_called, queue_completed, queue_cancelled,
     * queue_removed.
     *
     * @param  array<string, mixed>  $properties
     */
    private function auditQueue(string $action, PatientQueue $entry, string $what, array $properties = []): void
    {
        $entry->loadMissing('patient.user');
        $name = $entry->patient?->user?->full_name;

        AuditLog::record($action, $what . ': ' . ($name ?? 'an archived patient'), [
            'patient_name' => $name,
            'subject_type' => PatientQueue::class,
            'subject_id' => $entry->id,
            'properties' => array_merge([
                'ticket_number' => $entry->ticket_number,
                'status' => $entry->status,
                'room_number' => $entry->room_number,
                'is_priority' => (bool) $entry->is_priority,
            ], $properties),
        ]);
    }

    private function todayPatients()
    {
        return PatientQueue::with(['patient.user', 'addedBy'])
            ->withLivePatient()
            ->whereDate('created_at', today())
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Display a listing of the resource (For Staff/Nurse).
     */
    public function index()
    {
        $queues = $this->openQueues();
        $todayPatients = $this->todayPatients();

        return view('patient_queue.index', compact('queues', 'todayPatients'));
    }

    /**
     * Return only the dynamic queue content as HTML (used by AJAX auto-refresh for staff/admin).
     */
    public function indexPartial()
    {
        $queues = $this->openQueues();
        $todayPatients = $this->todayPatients();

        return view('patient_queue.index_partial', compact('queues', 'todayPatients'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // Get all registered patients
        $patients = Patient::with('user')
            ->whereHas('user', function ($query) {
                $query->where('status', 1); // Active patients only
            })
            ->get();

        return view('patient_queue.create', compact('patients'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StorePatientQueueRequest $request)
    {
        $validated = $request->validated();

        $validated['added_by'] = Auth::id();
        $validated['is_priority'] = $request->has('is_priority') ? true : false;

        $latestConsultation = null;
        $alreadyQueued = false;

        // Yesterday's forgotten place must not make "already in the queue" true today.
        PatientQueue::closeStaleEntries();

        // The "already in the queue" check and the insert run under a lock on the patient row, so
        // a double click / two front-desk users cannot queue the same patient twice. L-12.
        DB::transaction(function () use (&$validated, &$latestConsultation, &$alreadyQueued) {
            Patient::lockForClinicalWrite((int) $validated['patient_id']);

            $alreadyQueued = PatientQueue::where('patient_id', $validated['patient_id'])
                ->whereIn('status', PatientQueue::OPEN_STATUSES)
                ->exists();

            if ($alreadyQueued) {
                return;
            }

            // The patient's latest consultation form on file (today's, if the nurse has already recorded it).
            $latestConsultation = PatientQueue::latestConsultationFor((int) $validated['patient_id']);

            if ($latestConsultation) {
                $validated['latest_consultation_id'] = $latestConsultation->id;
                $validated['has_consultation_attachment'] = true;
            }

            $this->auditQueue('queue_added', PatientQueue::create($validated), 'Added to the queue');
        });

        if ($alreadyQueued) {
            return redirect()->back()->with('error', 'This patient is already in the queue.');
        }

        $message = 'Patient added to queue successfully.';
        if ($latestConsultation && $latestConsultation->created_at->isToday()) {
            $message .= " Today's consultation form is attached.";
        } elseif ($latestConsultation) {
            $message .= ' The previous consultation form (' . $latestConsultation->created_at->format('M d, Y') . ') is attached. '
                . "Record today's consultation and it will replace it on the doctor's screen.";
        } else {
            $message .= " No consultation form yet: record one and it will appear on the doctor's screen by itself.";
        }

        return redirectByRole('patient-queue.index')
            ->with('success', $message);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(PatientQueue $patientQueue)
    {
        $patients = Patient::with('user')
            ->whereHas('user', function ($query) {
                $query->where('status', 1);
            })
            ->get();

        return view('patient_queue.edit', compact('patientQueue', 'patients'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdatePatientQueueRequest $request, PatientQueue $patientQueue)
    {
        $validated = $request->validated();

        $updated = DB::transaction(function () use ($validated, $request, $patientQueue) {
            // Re-read under a lock: a completed or cancelled entry is history and must not be reopened
            // by a stale edit page, and two simultaneous edits must not interleave.
            $current = PatientQueue::whereKey($patientQueue->getKey())->lockForUpdate()->first();
            if (! $current || ! in_array($current->status, PatientQueue::OPEN_STATUSES, true)) {
                return false;
            }

            // Set timestamps based on status
            if ($request->status === PatientQueue::STATUS_IN_PROGRESS && ! $current->called_at) {
                $validated['called_at'] = now();
            }

            if (in_array($request->status, [PatientQueue::STATUS_COMPLETED, PatientQueue::STATUS_CANCELLED]) && ! $current->completed_at) {
                $validated['completed_at'] = now();
            }

            $before = $current->status;
            $current->update($validated);

            // a change of status through the edit form is one of the queue actions; a room / priority / note edit is not
            $action = $before === $current->status ? null : match ($current->status) {
                PatientQueue::STATUS_IN_PROGRESS => ['queue_called', 'Called from the queue'],
                PatientQueue::STATUS_COMPLETED => ['queue_completed', 'Queue entry completed'],
                PatientQueue::STATUS_CANCELLED => ['queue_cancelled', 'Queue entry cancelled'],
                default => null,
            };
            if ($action) {
                $this->auditQueue($action[0], $current, $action[1], ['from_status' => $before, 'by' => 'edit form']);
            }

            return true;
        });

        if (! $updated) {
            return redirect()->back()
                ->with('error', 'This queue entry is already closed and can no longer be changed.');
        }

        return redirect()->back()
            ->with('success', 'Queue updated successfully.');
    }

    /**
     * Take an entry off the queue. Only an entry that was never called is deleted; an entry already called is kept and
     * marked cancelled, and a finished one (completed or cancelled) is history and stays as it is (the owner's decision,
     * 2026-11-14: a delete used to remove the entry for good in any status). Each change leaves an audit row.
     */
    public function destroy(PatientQueue $patientQueue)
    {
        $outcome = DB::transaction(function () use ($patientQueue) {
            $entry = PatientQueue::whereKey($patientQueue->getKey())->lockForUpdate()->first();
            if (! $entry) {
                return 'gone';
            }

            if ($entry->status === PatientQueue::STATUS_WAITING && $entry->called_at === null) {
                $this->auditQueue('queue_removed', $entry, 'Removed from the queue');
                $entry->delete();

                return 'removed';
            }

            if (in_array($entry->status, PatientQueue::OPEN_STATUSES, true)) {
                $before = $entry->status;
                $entry->update(['status' => PatientQueue::STATUS_CANCELLED, 'completed_at' => $entry->completed_at ?? now()]);
                $this->auditQueue('queue_cancelled', $entry, 'Queue entry cancelled (it was already called, so it is kept)', ['from_status' => $before, 'by' => 'remove button']);

                return 'cancelled';
            }

            return 'closed';
        });

        return match ($outcome) {
            'removed', 'gone' => redirectByRole('patient-queue.index')->with('success', 'Patient removed from queue.'),
            'cancelled' => redirectByRole('patient-queue.index')->with('success', 'The patient was already called, so the queue entry is kept and marked cancelled.'),
            default => redirectByRole('patient-queue.index')->with('error', 'This queue entry is finished and is kept as part of the record.'),
        };
    }


    /**
     * Display queue for doctors
     */
    public function doctorQueue()
    {
        $queues = $this->openQueues();

        return view('patient_queue.doctor_view', compact('queues'));
    }

    /**
     * Return only the dynamic queue content as HTML (used by AJAX auto-refresh)
     */
    public function doctorQueuePartial()
    {
        $queues = $this->openQueues();

        return view('patient_queue.doctor_view_partial', compact('queues'));
    }

    /**
     * Call next patient
     */
    public function callNext(PatientQueue $patientQueue)
    {
        // Only a waiting patient can be called. The status test is part of the UPDATE itself, so two
        // doctors pressing "Call" on the same patient (or a double click / stale page) cannot both win,
        // re-open a closed entry or reset the first call time. L-12 / R3-L18.
        $called = PatientQueue::whereKey($patientQueue->getKey())
            ->where('status', PatientQueue::STATUS_WAITING)
            ->update([
                'status' => PatientQueue::STATUS_IN_PROGRESS,
                'called_at' => now(),
                'updated_at' => now(),
            ]);

        if ($called === 0) {
            return redirect()->back()
                ->with('error', 'This patient is no longer waiting in the queue.');
        }

        $this->auditQueue('queue_called', $patientQueue->fresh(), 'Called from the queue');

        return redirect()->back()
            ->with('success', 'Patient called successfully.');
    }

    /**
     * Complete patient consultation
     */
    public function complete(Request $request, PatientQueue $patientQueue)
    {
        $completed = PatientQueue::whereKey($patientQueue->getKey())
            ->whereIn('status', PatientQueue::OPEN_STATUSES)
            ->update([
                'status' => PatientQueue::STATUS_COMPLETED,
                'completed_at' => now(),
                'updated_at' => now(),
            ]);

        if ($completed === 0) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'This queue entry is already closed.'], 422);
            }

            return redirect()->back()
                ->with('error', 'This queue entry is already closed.');
        }

        $this->auditQueue('queue_completed', $patientQueue->fresh(), 'Queue entry completed');

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'Patient consultation completed.']);
        }

        return redirect()->back()
            ->with('success', 'Patient consultation completed.');
    }

    /**
     * View consultation form for a patient queue entry (Doctor only)
     */
    public function viewConsultation(PatientQueue $patientQueue)
    {
        // Load relationships
        $patientQueue->load(['patient.user', 'latestConsultation']);

        // Check if consultation form exists
        if (!$patientQueue->latestConsultation) {
            return redirect()->back()->with('error', 'No consultation form found for this patient.');
        }

        // Open the attached form itself (complaints, vital signs, ...), with the button to add the assessment and
        // plan. It used to open the patient's whole history table and ignore which form was attached, so the doctor
        // could not tell which row was the new one and never saw the complaint.
        return redirect()->route('doctors.document-issuances.show', $patientQueue->latestConsultation)
            ->with('info', ($patientQueue->attached_form_is_new ? 'This visit\'s' : 'Previous visit\'s')
                . ' consultation form of queue patient: ' . $patientQueue->patient->user->full_name);
    }
}
