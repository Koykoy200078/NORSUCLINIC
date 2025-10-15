<?php

namespace App\Http\Controllers;

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
        $queues = PatientQueue::with(['patient.user', 'addedBy'])
            ->whereIn('status', [PatientQueue::STATUS_WAITING, PatientQueue::STATUS_IN_PROGRESS])
            ->orderByQueue()
            ->get();

        return view('patient_queue.index', compact('queues'));
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
    public function store(Request $request)
    {
        $validated = $request->validate([
            'patient_id' => 'required|exists:patients,id',
            'room_number' => 'nullable|string|max:50',
            'is_priority' => 'boolean',
            'notes' => 'nullable|string|max:500',
        ]);

        // Check if patient is already in queue
        $existingQueue = PatientQueue::where('patient_id', $validated['patient_id'])
            ->whereIn('status', [PatientQueue::STATUS_WAITING, PatientQueue::STATUS_IN_PROGRESS])
            ->first();

        if ($existingQueue) {
            return redirect()->back()->with('error', 'This patient is already in the queue.');
        }

        $validated['added_by'] = Auth::id();
        $validated['is_priority'] = $request->has('is_priority') ? true : false;

        PatientQueue::create($validated);

        return redirect()->route($this->getIndexRoute())
            ->with('success', 'Patient added to queue successfully.');
    }

    /**
     * Display the specified resource (For Doctors).
     */
    public function show(PatientQueue $patientQueue)
    {
        $patientQueue->load(['patient.user', 'addedBy']);
        return view('patient_queue.show', compact('patientQueue'));
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
    public function update(Request $request, PatientQueue $patientQueue)
    {
        $validated = $request->validate([
            'room_number' => 'nullable|string|max:50',
            'is_priority' => 'boolean',
            'notes' => 'nullable|string|max:500',
            'status' => 'in:waiting,in_progress,completed,cancelled',
        ]);

        $validated['is_priority'] = $request->has('is_priority') ? true : false;

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
        $queues = PatientQueue::with(['patient.user', 'addedBy'])
            ->whereIn('status', [PatientQueue::STATUS_WAITING, PatientQueue::STATUS_IN_PROGRESS])
            ->orderByQueue()
            ->get();

        return view('patient_queue.doctor_view', compact('queues'));
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

        return redirect()->back()
            ->with('success', 'Patient consultation completed.');
    }
}
