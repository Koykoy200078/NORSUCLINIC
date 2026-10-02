<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePatientQueueRequest;
use App\Http\Requests\UpdatePatientQueueRequest;
use App\Models\Patient;
use App\Models\PatientQueue;
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
            Patient::whereKey($validated['patient_id'])->lockForUpdate()->first();

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

            PatientQueue::create($validated);
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

        return redirect()->route($this->getIndexRoute())
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

            $current->update($validated);

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
     * Remove the specified resource from storage.
     */
    public function destroy(PatientQueue $patientQueue)
    {
        $patientQueue->delete();

        return redirect()->route($this->getIndexRoute())
            ->with('success', 'Patient removed from queue.');
    }

    /**
     * Get the appropriate index route based on user role
     */
    private function getIndexRoute()
    {
        if (isRole('clinic_admin')) {
            return 'patient-queue.index';
        } elseif (isRole('staff')) {
            return 'staff.patient-queue.index';
        } elseif (isRole('doctor')) {
            return 'doctors.patient-queue.index';
        }

        return 'patient-queue.index'; // default fallback
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
