<?php

namespace App\Http\Controllers;

use App\Models\Campus;
use App\Models\College;
use App\Models\Course;
use App\Models\LabRequest;
use App\Models\LabRequestItem;
use App\Models\LabTest;
use App\Models\Patient;
use App\Models\User;
use App\Models\YearLevel;
use App\Traits\LogsActivity;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class LabRequestController extends Controller
{
    use LogsActivity;

    // =========================================================================
    // INDEX
    // =========================================================================

    public function index()
    {
        return view('lab_requests.index');
    }

    // =========================================================================
    // CREATE
    // =========================================================================

    public function create(Request $request)
    {
        // Patients are view-only — they may not create lab requests. VIEW-ONLY.
        abort_if(isRole('patient'), 403);

        $labTestsGrouped = LabTest::groupedByCategory();

        // Pre-populate patient if user is a patient OR user_id is passed
        $user    = null;
        $patient = null;

        if (isRole('patient')) {
            $user = Auth::user()->load(['patient', 'campus', 'college', 'course', 'yearLevel', 'patient.address', 'address.barangay', 'address.city', 'address.state']);
            if ($user->type === User::PATIENT) {
                $patient = $user->patient;
            }
        } elseif ($request->has('user_id')) {
            $userId = $request->get('user_id');
            $user   = User::with(['patient', 'campus', 'college', 'course', 'yearLevel', 'patient.address', 'address.barangay', 'address.city', 'address.state'])
                ->find($userId);

            if ($user && $user->type === User::PATIENT) {
                $patient = $user->patient;
            }
        }

        // Requesting physician list (doctors + admins acting as physicians)
        $physicians = User::whereIn('type', [User::DOCTOR])
            ->with('doctor')
            ->orderBy('first_name')
            ->get();

        return view('lab_requests.create', compact(
            'labTestsGrouped',
            'user',
            'patient',
            'physicians'
        ));
    }

    // =========================================================================
    // STORE
    // =========================================================================

    public function store(Request $request)
    {
        // Patients are view-only — they may not create lab requests. VIEW-ONLY.
        abort_if(isRole('patient'), 403);

        $request->validate([
            'patient_user_id' => isRole('patient') ? 'nullable' : 'required|exists:users,id',
            'patient_name'    => 'required|string|max:255',
            'requested_at'    => 'required|date',
            'test_ids'        => 'nullable|array',
            'test_ids.*'      => 'integer|exists:lab_tests,id',
            'custom_tests'    => 'nullable|array',
            'custom_tests.*'  => 'nullable|string|max:255',
        ]);

        try {
            $patientUserId = isRole('patient') ? Auth::id() : $request->patient_user_id;
            $selectedTestIds = array_values(array_unique(array_map('intval', $request->input('test_ids', []))));
            $customTests = $this->extractCustomTests($request->input('custom_tests', []));

            if (empty($selectedTestIds) && empty($customTests)) {
                return redirect()->back()
                    ->withInput()
                    ->withErrors([
                        'test_ids' => 'Please select at least one laboratory/medical test or add at least one custom test in Others.',
                    ]);
            }

            // Resolve patient demographics from user record
            $patientUser = User::with(['campus', 'college', 'course', 'yearLevel', 'department', 'office', 'patient.address', 'address.barangay', 'address.city', 'address.state'])
                ->findOrFail($patientUserId);

            $patient = $patientUser->patient;

            $labRequest = LabRequest::create([
                'request_number'       => generateUniqueLabRequestNumber(),
                'document_creator_id'  => Auth::id(),
                'patient_user_id'      => $patientUser->id,
                'patient_name'         => $request->patient_name,
                'patient_age'          => $request->patient_age,
                'patient_gender'       => $request->patient_gender,
                'patient_dob'          => $patientUser->dob ?? $request->patient_dob,
                'patient_contact'      => $request->patient_contact,
                'address'              => $request->address,
                'campus'               => $patientUser->campus?->campus_name ?? $request->campus,
                'college'              => $patientUser->college?->college_name ?? $request->college,
                'course'               => $patientUser->course?->course_name ?? $request->course,
                'year_level'           => $patientUser->yearLevel?->year_level_name ?? $request->year_level,
                'department'           => $patientUser->department?->department_name ?? $request->department,
                'office'               => $patientUser->office?->office_name ?? $request->office,
                'status_affiliation'   => $request->status_affiliation,
                'requested_at'         => $request->requested_at,
                'clinical_indication'  => $request->clinical_indication,
                'remarks'              => $request->remarks,
                'requesting_physician' => $request->requesting_physician,
                'physician_license_no' => $request->physician_license_no,
                'status'               => LabRequest::STATUS_PENDING,
            ]);

            // Attach selected tests as items (snapshot key data)
            $tests = LabTest::whereIn('id', $selectedTestIds)->get();
            foreach ($tests as $test) {
                LabRequestItem::create([
                    'lab_request_id' => $labRequest->id,
                    'lab_test_id'    => $test->id,
                    'test_name'      => $test->name,
                    'test_category'  => $test->category,
                    'unit'           => $test->unit,
                    'normal_range'   => $test->normal_range,
                    'result_status'  => LabRequestItem::RESULT_PENDING,
                ]);
            }

            // Attach custom/ad-hoc tests from Others input
            foreach ($customTests as $customTestName) {
                LabRequestItem::create([
                    'lab_request_id' => $labRequest->id,
                    'lab_test_id'    => null,
                    'test_name'      => $customTestName,
                    'test_category'  => 'Other',
                    'unit'           => null,
                    'normal_range'   => null,
                    'result_status'  => LabRequestItem::RESULT_PENDING,
                ]);
            }

            // Activity log
            self::logActivity(
                'lab_request_created',
                "Lab request #{$labRequest->request_number} created for: {$labRequest->patient_name}",
                [
                    'patient_name'   => $labRequest->patient_name,
                    'patient_age'    => $labRequest->patient_age,
                    'patient_gender' => $labRequest->patient_gender,
                    'subject_type'   => 'LabRequest',
                    'subject_id'     => $labRequest->id,
                    'date'           => $labRequest->requested_at->toDateString(),
                ]
            );

            $indexRoute = $this->getIndexRoute();
            return redirect()->route($indexRoute)
                ->with('success', "Lab request #{$labRequest->request_number} created successfully.");
        } catch (\Exception $e) {
            Log::error('LabRequest store error: ' . $e->getMessage());
            return redirect()->back()
                ->withInput()
                ->with('error', 'An error occurred while creating the lab request: ' . $e->getMessage());
        }
    }

    // =========================================================================
    // SHOW
    // =========================================================================

    public function show(LabRequest $lab_request)
    {
        // A patient may only view their OWN lab request. AUTH-2.
        if (isRole('patient')) {
            abort_unless((int) $lab_request->patient_user_id === (int) getLogInUserId(), 403);
        }

        $lab_request->load(['items', 'creator', 'patient']);
        return view('lab_requests.show', compact('lab_request'));
    }

    // =========================================================================
    // EDIT
    // =========================================================================

    public function edit(LabRequest $lab_request)
    {
        if ($lab_request->isTerminal()) {
            return redirect()->route($this->getIndexRoute())
                ->with('error', 'This lab request is already in a terminal state and cannot be edited.');
        }

        $lab_request->load(['items.labTest', 'creator', 'patient']);
        $labTestsGrouped = LabTest::groupedByCategory();
        $selectedTestIds = $lab_request->items->pluck('lab_test_id')->filter()->toArray();
        $customTestItems = $lab_request->items->whereNull('lab_test_id')->values();

        $physicians = User::whereIn('type', [User::DOCTOR, User::ADMIN])->orderBy('first_name')->get();

        $allowedStatuses = LabRequest::allowedTransitions()[$lab_request->status] ?? [];

        return view('lab_requests.edit', compact(
            'lab_request',
            'labTestsGrouped',
            'selectedTestIds',
            'customTestItems',
            'physicians',
            'allowedStatuses'
        ));
    }

    // =========================================================================
    // UPDATE
    // =========================================================================

    public function update(Request $request, LabRequest $lab_request)
    {
        if ($lab_request->isTerminal()) {
            return redirect()->back()->with('error', 'Cannot update a terminal lab request.');
        }

        $request->validate([
            'patient_name'   => 'required|string|max:255',
            'requested_at'   => 'required|date',
            'test_ids'       => 'nullable|array',
            'test_ids.*'     => 'integer|exists:lab_tests,id',
            'custom_tests'   => 'nullable|array',
            'custom_tests.*' => 'nullable|string|max:255',
        ]);

        try {
            $newIds = array_values(array_unique(array_map('intval', $request->input('test_ids', []))));
            $incomingCustomTests = $this->extractCustomTests($request->input('custom_tests', []));

            if (empty($newIds) && empty($incomingCustomTests)) {
                return redirect()->back()
                    ->withInput()
                    ->withErrors([
                        'test_ids' => 'Please keep at least one laboratory/medical test or add at least one custom test in Others.',
                    ]);
            }

            // Update main record
            $lab_request->update([
                'patient_name'         => $request->patient_name,
                'patient_age'          => $request->patient_age,
                'patient_gender'       => $request->patient_gender,
                'patient_contact'      => $request->patient_contact,
                'address'              => $request->address,
                'status_affiliation'   => $request->status_affiliation,
                'requested_at'         => $request->requested_at,
                'clinical_indication'  => $request->clinical_indication,
                'remarks'              => $request->remarks,
                'requesting_physician' => $request->requesting_physician,
                'physician_license_no' => $request->physician_license_no,
            ]);

            // Sync test items — delete removed, add new
            $existingIds  = $lab_request->items->pluck('lab_test_id')->filter()->toArray();

            // Delete removed
            $removedIds = array_diff($existingIds, $newIds);
            if (!empty($removedIds)) {
                LabRequestItem::where('lab_request_id', $lab_request->id)
                    ->whereIn('lab_test_id', $removedIds)
                    ->delete();
            }

            // Add newly selected
            $addedIds = array_diff($newIds, $existingIds);
            if (!empty($addedIds)) {
                $newTests = LabTest::whereIn('id', $addedIds)->get();
                foreach ($newTests as $test) {
                    LabRequestItem::create([
                        'lab_request_id' => $lab_request->id,
                        'lab_test_id'    => $test->id,
                        'test_name'      => $test->name,
                        'test_category'  => $test->category,
                        'unit'           => $test->unit,
                        'normal_range'   => $test->normal_range,
                        'result_status'  => LabRequestItem::RESULT_PENDING,
                    ]);
                }
            }

            // Sync custom/ad-hoc tests (lab_test_id = null)
            $existingCustomItems = $lab_request->items()
                ->whereNull('lab_test_id')
                ->get();

            $existingCustomByKey = [];
            foreach ($existingCustomItems as $item) {
                $existingCustomByKey[$this->normalizeCustomTestName($item->test_name)] = $item;
            }

            $incomingCustomByKey = [];
            foreach ($incomingCustomTests as $customTestName) {
                $incomingCustomByKey[$this->normalizeCustomTestName($customTestName)] = $customTestName;
            }

            $customKeysToDelete = array_diff(array_keys($existingCustomByKey), array_keys($incomingCustomByKey));
            if (!empty($customKeysToDelete)) {
                $deleteIds = collect($customKeysToDelete)
                    ->map(function ($key) use ($existingCustomByKey) {
                        return $existingCustomByKey[$key]->id;
                    })
                    ->all();

                if (!empty($deleteIds)) {
                    LabRequestItem::where('lab_request_id', $lab_request->id)
                        ->whereIn('id', $deleteIds)
                        ->delete();
                }
            }

            $customKeysToAdd = array_diff(array_keys($incomingCustomByKey), array_keys($existingCustomByKey));
            foreach ($customKeysToAdd as $key) {
                LabRequestItem::create([
                    'lab_request_id' => $lab_request->id,
                    'lab_test_id'    => null,
                    'test_name'      => $incomingCustomByKey[$key],
                    'test_category'  => 'Other',
                    'unit'           => null,
                    'normal_range'   => null,
                    'result_status'  => LabRequestItem::RESULT_PENDING,
                ]);
            }

            // Update existing item results if provided
            if ($request->has('results')) {
                foreach ($request->results as $itemId => $resultData) {
                    LabRequestItem::where('id', $itemId)
                        ->where('lab_request_id', $lab_request->id)
                        ->update([
                            'result_value'  => $resultData['result_value'] ?? null,
                            'result_status' => $resultData['result_status'] ?? LabRequestItem::RESULT_PENDING,
                            'notes'         => $resultData['notes'] ?? null,
                        ]);
                }
            }

            // Activity log
            self::logActivity(
                'lab_request_updated',
                "Lab request #{$lab_request->request_number} updated for: {$lab_request->patient_name}",
                [
                    'patient_name' => $lab_request->patient_name,
                    'subject_type' => 'LabRequest',
                    'subject_id'   => $lab_request->id,
                ]
            );

            $indexRoute = $this->getIndexRoute();
            return redirect()->route($indexRoute)
                ->with('success', "Lab request #{$lab_request->request_number} updated successfully.");
        } catch (\Exception $e) {
            Log::error('LabRequest update error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'An error occurred: ' . $e->getMessage());
        }
    }

    // =========================================================================
    // DESTROY
    // =========================================================================

    public function destroy(LabRequest $lab_request)
    {
        try {
            $requestNumber = $lab_request->request_number;
            $lab_request->delete(); // boot() cascades delete to items

            self::logActivity(
                'lab_request_deleted',
                "Lab request #{$requestNumber} deleted",
                [
                    'subject_type' => 'LabRequest',
                    'subject_id'   => $lab_request->id,
                ]
            );

            $indexRoute = $this->getIndexRoute();
            return redirect()->route($indexRoute)
                ->with('success', "Lab request #{$requestNumber} deleted successfully.");
        } catch (\Exception $e) {
            Log::error('LabRequest destroy error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to delete lab request.');
        }
    }

    // =========================================================================
    // UPDATE STATUS (quick status transitions)
    // =========================================================================

    public function updateStatus(Request $request, LabRequest $lab_request)
    {
        $request->validate([
            'status'      => 'required|in:pending,collected,processing,completed,cancelled,referred,rejected',
            'status_note' => 'nullable|string|max:1000',
        ]);

        $newStatus = $request->status;
        $allowed   = LabRequest::allowedTransitions()[$lab_request->status] ?? [];

        if (!in_array($newStatus, $allowed)) {
            return redirect()->back()->with('error', "Cannot transition from '{$lab_request->status}' to '{$newStatus}'.");
        }

        try {
            $updateData = [
                'status'      => $newStatus,
                'status_note' => $request->status_note,
            ];

            // Record timestamp for the new status
            $timestampField = match ($newStatus) {
                'collected'  => 'collected_at',
                'processing' => 'processed_at',
                'completed'  => 'completed_at',
                'cancelled'  => 'cancelled_at',
                'referred'   => 'referred_at',
                'rejected'   => 'rejected_at',
                default      => null,
            };

            if ($timestampField) {
                $updateData[$timestampField] = now();
            }

            $lab_request->update($updateData);

            self::logActivity(
                'lab_request_status_updated',
                "Lab request #{$lab_request->request_number} status changed to: {$newStatus}",
                [
                    'patient_name' => $lab_request->patient_name,
                    'subject_type' => 'LabRequest',
                    'subject_id'   => $lab_request->id,
                ]
            );

            return redirect()->back()->with('success', "Status updated to " . ucfirst($newStatus) . ".");
        } catch (\Exception $e) {
            Log::error('LabRequest status update error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to update status.');
        }
    }

    // =========================================================================
    // EXPORT PDF
    // =========================================================================

    public function exportPdf(int $id)
    {
        $labRequest = LabRequest::with(['items.labTest', 'creator', 'patient'])->findOrFail($id);

        // A patient may only download their OWN lab request. AUTH-2.
        if (isRole('patient')) {
            abort_unless((int) $labRequest->patient_user_id === (int) getLogInUserId(), 403);
        }

        $pdf = Pdf::loadView('lab_requests.pdf', compact('labRequest'))
            ->setPaper('a4', 'portrait');

        $filename = 'LabRequest_' . $labRequest->request_number . '_' . $labRequest->patient_name . '.pdf';
        $filename = str_replace(' ', '_', $filename);

        return $pdf->download($filename);
    }

    // =========================================================================
    // AJAX: Search Users (reuse existing pattern)
    // =========================================================================

    public function searchUsers(Request $request)
    {
        $query = $request->get('query', '');

        $users = User::where('type', User::PATIENT)
            ->where(function ($q) use ($query) {
                $q->where('first_name', 'like', "%{$query}%")
                    ->orWhere('last_name', 'like', "%{$query}%")
                    ->orWhere('email', 'like', "%{$query}%")
                    ->orWhere('university_id_number', 'LIKE', "%{$query}%")
                    ->orWhere('employee_id', 'LIKE', "%{$query}%");
            })
            ->with(['patient.address.barangay', 'patient.address.city', 'patient.address.state', 'campus', 'college', 'course', 'yearLevel', 'department', 'office', 'address.barangay', 'address.city', 'address.state'])
            ->get()
            ->map(function ($user) {

                return [
                    'id'         => $user->id,
                    'name'       => $user->full_name,
                    'email'      => $user->email,
                    'unique_id'  => $user->university_id_number ? $user->university_id_number : $user->employee_id,
                    'age'        => $user->dob ? \Carbon\Carbon::parse($user->dob)->age : null,
                    'gender'     => $user->gender === User::MALE ? 'Male' : 'Female',
                    'contact'    => $user->contact,
                    'dob'        => $user->dob,
                    'campus'     => $user->campus?->campus_name,
                    'college'    => $user->college?->college_name,
                    'course'     => $user->course?->course_name,
                    'year_level' => $user->yearLevel?->year_level_name,
                    'department' => $user->department?->department_name,
                    'office'     => $user->office?->office_name,
                    'address'    => $user->address?->full_address ?? ($user->patient?->address?->full_address ?? ''),
                    'status_affiliation' => User::STATUS_AFFILIATION[$user->patient?->patient_type_id] ?? 'guest',
                ];
            });

        return response()->json($users);
    }

    // =========================================================================
    // Private helpers
    // =========================================================================

    /**
     * Normalize custom test rows from request input and remove empty/duplicates.
     */
    private function extractCustomTests($rawCustomTests): array
    {
        $tests = is_array($rawCustomTests) ? $rawCustomTests : [$rawCustomTests];
        $normalized = [];

        foreach ($tests as $testName) {
            $trimmedName = trim((string) $testName);
            if ($trimmedName === '') {
                continue;
            }

            $key = $this->normalizeCustomTestName($trimmedName);
            if ($key === '') {
                continue;
            }

            $normalized[$key] = preg_replace('/\s+/', ' ', $trimmedName);
        }

        return array_values($normalized);
    }

    private function normalizeCustomTestName(string $testName): string
    {
        $normalized = preg_replace('/\s+/', ' ', trim($testName));

        return strtolower((string) $normalized);
    }

    /**
     * Get the correct named route for the index page based on current role.
     */
    private function getIndexRoute(): string
    {
        if (isRole('clinic_admin')) {
            return 'lab-requests.index';
        } elseif (isRole('staff')) {
            return 'staff.lab-requests.index';
        } elseif (isRole('doctor')) {
            return 'doctors.lab-requests.index';
        } elseif (isRole('patient')) {
            return 'patients.lab-requests.index';
        }
        return 'lab-requests.index';
    }
}
