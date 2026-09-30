<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePatientQueueRequest;
use App\Http\Requests\UpdatePatientQueueRequest;
use App\Models\Patient;
use App\Models\PatientQueue;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PatientQueueController extends Controller
{
    /**
     * Display a listing of the resource (For Staff/Nurse).
     */
    public function index()
    {
        $queues = PatientQueue::with(['patient.user', 'addedBy', 'latestConsultation'])
            ->whereIn('status', [PatientQueue::STATUS_WAITING, PatientQueue::STATUS_IN_PROGRESS])
            ->orderByQueue()
            ->get();

        $todayPatients = PatientQueue::with(['patient.user', 'addedBy'])
            ->whereDate('created_at', today())
            ->orderBy('created_at', 'desc')
            ->get();

        return view('patient_queue.index', compact('queues', 'todayPatients'));
    }

    /**
     * Return only the dynamic queue content as HTML (used by AJAX auto-refresh for staff/admin).
     */
    public function indexPartial()
    {
        $queues = PatientQueue::with(['patient.user', 'addedBy', 'latestConsultation'])
            ->whereIn('status', [PatientQueue::STATUS_WAITING, PatientQueue::STATUS_IN_PROGRESS])
            ->orderByQueue()
            ->get();

        $todayPatients = PatientQueue::with(['patient.user', 'addedBy'])
            ->whereDate('created_at', today())
            ->orderBy('created_at', 'desc')
            ->get();

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

        // Check if patient is already in queue
        $existingQueue = PatientQueue::where('patient_id', $validated['patient_id'])
            ->whereIn('status', [PatientQueue::STATUS_WAITING, PatientQueue::STATUS_IN_PROGRESS])
            ->first();

        if ($existingQueue) {
            return redirect()->back()->with('error', 'This patient is already in the queue.');
        }

        $validated['added_by'] = Auth::id();
        $validated['is_priority'] = $request->has('is_priority') ? true : false;

        // Get patient's latest consultation form if it exists
        $latestConsultation = \App\Models\RequestDocuments::where('user_id', function ($query) use ($validated) {
            $query->select('user_id')
                ->from('patients')
                ->where('id', $validated['patient_id'])
                ->limit(1);
        })
            ->where('document_type', 'consultation_form')
            ->orderBy('created_at', 'desc')
            ->first();

        if ($latestConsultation) {
            $validated['latest_consultation_id'] = $latestConsultation->id;
            $validated['has_consultation_attachment'] = true;
        }

        PatientQueue::create($validated);

        $message = 'Patient added to queue successfully.';
        if ($latestConsultation) {
            $message .= ' Latest consultation form attached.';
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

        // Set timestamps based on status
        if ($request->status === PatientQueue::STATUS_IN_PROGRESS && !$patientQueue->called_at) {
            $validated['called_at'] = now();
        }

        if (in_array($request->status, [PatientQueue::STATUS_COMPLETED, PatientQueue::STATUS_CANCELLED]) && !$patientQueue->completed_at) {
            $validated['completed_at'] = now();
        }

        $patientQueue->update($validated);

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
        $queues = PatientQueue::with(['patient.user', 'addedBy', 'latestConsultation'])
            ->whereIn('status', [PatientQueue::STATUS_WAITING, PatientQueue::STATUS_IN_PROGRESS])
            ->orderByQueue()
            ->get();

        return view('patient_queue.doctor_view', compact('queues'));
    }

    /**
     * Return only the dynamic queue content as HTML (used by AJAX auto-refresh)
     */
    public function doctorQueuePartial()
    {
        $queues = PatientQueue::with(['patient.user', 'addedBy', 'latestConsultation'])
            ->whereIn('status', [PatientQueue::STATUS_WAITING, PatientQueue::STATUS_IN_PROGRESS])
            ->orderByQueue()
            ->get();

        return view('patient_queue.doctor_view_partial', compact('queues'));
    }

    /**
     * Call next patient
     */
    public function callNext(PatientQueue $patientQueue)
    {
        $patientQueue->update([
            'status' => PatientQueue::STATUS_IN_PROGRESS,
            'called_at' => now(),
        ]);

        return redirect()->back()
            ->with('success', 'Patient called successfully.');
    }

    /**
     * Complete patient consultation
     */
    public function complete(Request $request, PatientQueue $patientQueue)
    {
        $patientQueue->update([
            'status' => PatientQueue::STATUS_COMPLETED,
            'completed_at' => now(),
        ]);

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

        // Redirect to patient history with consultation form
        $patientId = $patientQueue->patient->id;
        $consultationId = $patientQueue->latest_consultation_id;

        // Route to patient history page with specific consultation highlighted
        return redirect()->route('doctors.patients.showMyHistory', [
            'patient' => $patientId,
            'consultation_id' => $consultationId
        ])->with('info', 'Viewing consultation form for queue patient: ' . $patientQueue->patient->user->full_name);
    }
}
