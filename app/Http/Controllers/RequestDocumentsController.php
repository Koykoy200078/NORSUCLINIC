<?php

namespace App\Http\Controllers;

use App\Models\Campus;
use App\Models\College;
use App\Models\Course;
use App\Models\Diagnose;
use App\Models\Patient;
use App\Models\RequestDocuments;
use App\Models\Staff;
use App\Models\User;
use App\Models\Vaccination;
use App\Models\YearLevel;
use App\Repositories\PatientRepository;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RequestDocumentsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('requests.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(PatientRepository $patientRepository, Request $request)
    {
        $data = $patientRepository->getData();

        // Add departments and offices data
        $data['departments'] = \App\Models\Department::toBase()->pluck('department_name', 'id');
        $data['offices'] = \App\Models\Office::toBase()->pluck('office_name', 'id');

        $user = auth()->user(); // Get the authenticated user

        // If user_id is provided (from patient history), get that patient's data
        if ($request->has('user_id')) {
            $userId = $request->get('user_id');
            $user = \App\Models\User::with('patient.address')->find($userId);

            if (!$user) {
                return redirect()->back()->with('error', 'Patient not found.');
            }
        }

        // Get patient data if user is a patient or user_id is provided
        $patient = null;
        if ($user && $user->type == 3) {
            $patient = $user->patient;
        }

        return view('requests.create', compact('data', 'user', 'patient'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $data = $request->except('_token');

        try {
            if ($data['document_type'] === 'medical_certificate') {
                $this->storeMedicalCertificate($data);
            } elseif ($data['document_type'] === 'consultation_form') {
                $this->storeConsultationForm($data);
            }

            // Check if we should redirect to patient history
            if ($request->has('redirect_to_patient') && $request->redirect_to_patient) {
                $user = User::find($data['user_id']);
                if ($user && $user->patient) {
                    $patientId = $user->patient->id;

                    // Determine success message based on document type
                    $successMessage = $data['document_type'] === 'medical_certificate'
                        ? 'Medical certificate created successfully.'
                        : 'Consultation form created successfully.';

                    // Role-based patient history redirect
                    if (isRole('clinic_admin')) {
                        return redirect()->route('patients.showMyHistory', ['patient' => $patientId])
                            ->with('success', $successMessage);
                    } elseif (isRole('staff')) {
                        return redirect()->route('staff.patients.showMyHistory', ['patient' => $patientId])
                            ->with('success', $successMessage);
                    } elseif (isRole('doctor')) {
                        return redirect()->route('doctors.patients.showMyHistory', ['patient' => $patientId])
                            ->with('success', $successMessage);
                    }
                }
            }

            // Default: Return to request documents index with role-based redirect
            $redirectRoute = isRole('clinic_admin') ? 'request-documents.index' : (isRole('staff') ? 'staff.request-documents.index' : (isRole('doctor') ? 'doctors.request-documents.index' : 'request-documents.index'));

            return redirect()->route($redirectRoute)
                ->with('success', 'Request document created successfully.');
        } catch (\Exception $e) {
            // Log the error for debugging
            Log::error('Error in store method: ' . $e->getMessage());

            // Return an error response
            return redirect()->back()
                ->with('error', 'An error occurred while creating the request document: ' . $e->getMessage());
        }
    }

    /**
     * Store a medical certificate document.
     */
    private function storeMedicalCertificate(array $data)
    {
        // Prepare the data for insertion
        $data['vital_signs_bp'] = $data['vital_signs_bp_2'] . '/' . $data['vital_signs_bp_22'];
        $data['vital_signs_pr'] = $data['vital_signs_pr_2'];
        $data['vital_signs_rr'] = $data['vital_signs_rr_2'];
        $data['vital_signs_temp'] = $data['vital_signs_temp_2'];
        $data['vital_signs_height'] = $data['vital_signs_height_2'];
        $data['vital_signs_weight'] = $data['vital_signs_weight_2'];

        // Log the user_id for debugging
        Log::info('Attempting to create medical certificate for user_id: ' . ($data['user_id'] ?? 'NULL'));

        // Retrieve the user and related IDs
        $user = User::with(['campus', 'college', 'course', 'yearLevel'])->find($data['user_id']);
        if (!$user) {
            Log::error('User not found with ID: ' . ($data['user_id'] ?? 'NULL'));
            throw new \Exception('User not found. Please select a patient.');
        }

        // Retrieve the names using relationships
        $data['campus'] = $user->campus->campus_name ?? 'Unknown Campus';
        $data['college'] = $user->college->college_name ?? 'Unknown College';
        $data['course'] = $user->course->course_name ?? 'Unknown Course';
        $data['year_level'] = $user->yearLevel->year_level_name ?? 'Unknown Year Level';

        // Insert the data into the database
        RequestDocuments::create([
            'document_type' => $data['document_type'],
            'document_creator_id' => $data['document_creator_id'], // Use the authenticated user ID
            'user_id' => $data['user_id'],
            'name' => $data['name'],
            'age' => $data['age'],
            'gender' => $data['gender'],
            'date_of_birth' => $user->dob,
            'address' => $data['address'],
            'request_of' => $data['request_of'],
            'requested_at' => now()->format('Y-m-d'),
            'campus' => $data['campus'],
            'college' => $data['college'],
            'course' => $data['course'],
            'year_level' => $data['year_level'],
            'examined_on' => $data['examined_on'],
            'complaints_diagnosis' => $data['complaints_diagnosis'],
            'vital_signs_bp' => $data['vital_signs_bp'],
            'vital_signs_pr' => $data['vital_signs_pr'],
            'vital_signs_temp' => $data['vital_signs_temp'],
            'vital_signs_rr' => $data['vital_signs_rr'],
            'vital_signs_height' => $data['vital_signs_height'],
            'vital_signs_weight' => $data['vital_signs_weight'],
            'medical_cert_remarks' => $data['medical_cert_remarks'],
            'doc_lic_no' => $data['doc_lic_no'],
            'doc_prt_no' => $data['doc_prt_no'],
        ]);
    }

    /**
     * Store a consultation form document.
     */
    private function storeConsultationForm(array $data)
    {
        // Map related names for numeric fields using their IDs
        $data['campus'] = Campus::find($data['campus_id'])->campus_name ?? 'Unknown Campus';
        $data['college'] = College::find($data['college_id'])->college_name ?? 'Unknown College';
        $data['course'] = Course::find($data['course_id'])->course_name ?? 'Unknown Course';
        $data['year_level'] = YearLevel::find($data['year_level_id'])->year_level_name ?? 'Unknown Year Level';
        $data['vaccination_id'] = Vaccination::find($data['vaccination_id'])->vaccination_status ?? 'Unknown Vaccination';
        $data['comorbidities_id'] = isset($data['comorbidities_id']) && $data['comorbidities_id'] ? Diagnose::find($data['comorbidities_id'])->diagnoses ?? 'None' : 'None';

        $requestDocument = RequestDocuments::create([
            'document_type' => $data['document_type'],
            'document_creator_id' => $data['document_creator_id'],
            'user_id' => $data['user_id'],
            'name' => $data['name'],
            'age' => $data['age'],
            'gender' => $data['gender'],
            'status' => $data['status'],
            'date_of_birth' => $data['date_of_birth'],
            'address' => $data['address'],
            'religion' => $data['religion'],
            'patient_contact' => $data['patient_contact'],
            'campus' => $data['campus'],
            'college' => $data['college'],
            'course' => $data['course'],
            'year_level' => $data['year_level'],
            'informant' => $data['informant'],
            'emergency_contact' => $data['emergency_contact'],
            'requested_at' => $data['requested_at'],
            'complaints' => $data['complaints'],
            'covid_vaccination' => $data['vaccination_id'],
            'comorbidities' => $data['comorbidities_id'],
            'allergies' => $data['allergies'] ?? null,
            'admissions_surgeries' => $data['admissions_surgeries'] ?? null,
            'maintenance' => $data['maintenance'] ?? null,
            'pregnancy_status' => $data['pregnancy_status'] ?? null,
            'lmp_aog' => $data['lmp_aog'] ?? null,
            'vital_signs_bp' => $data['vital_signs_bp'],
            'vital_signs_pr' => $data['vital_signs_pr'],
            'vital_signs_temp' => $data['vital_signs_temp'],
            'vital_signs_rr' => $data['vital_signs_rr'] ?? null,
            'vital_signs_o2_sat' => $data['vital_signs_o2_sat'],
            'vital_signs_height' => $data['vital_signs_height'] ?? null,
            'vital_signs_weight' => $data['vital_signs_weight'] ?? null,
            'pertinent_exam' => $data['pertinent_exam'],
            'assessment' => $data['assessment'],
            'plan' => $data['plan'],
            'consult_mode' => $data['consult_mode'],
            'nursing_intervention' => $data['nursing_intervention'],
            'nursing_incharged_id' => $data['nursing_incharged'],
        ]);

        // Handle image uploads with custom path (Patient Name/Timestamp)
        if (request()->hasFile('consultation_images')) {
            try {
                // Create folder name from patient full name and timestamp
                $patientName = str_replace(' ', '_', $data['name']); // Replace spaces with underscores
                $timestamp = now()->format('Y-m-d_H-i-s'); // e.g., 2025-10-09_14-30-45
                $folderPath = "consultation_images/{$patientName}/{$timestamp}";

                $uploadedImages = [];

                foreach (request()->file('consultation_images') as $image) {
                    try {
                        // Get file size before moving (important: must be done before move())
                        $fileSize = $image->getSize();

                        // Validate file size (5MB max)
                        if ($fileSize <= 5 * 1024 * 1024) {
                            $fileName = $image->getClientOriginalName();

                            // Store image directly to public/uploads/consultation_images/[PatientName]/[Timestamp]/
                            $destinationPath = public_path('uploads/' . $folderPath);

                            // Create directory if it doesn't exist
                            if (!file_exists($destinationPath)) {
                                mkdir($destinationPath, 0777, true);
                            }

                            // Move the file
                            $image->move($destinationPath, $fileName);

                            // Add to array for database storage (use stored size, not getSize() after move)
                            $uploadedImages[] = [
                                'path' => $folderPath . '/' . $fileName,
                                'name' => $fileName,
                                'size' => $fileSize,
                                'uploaded_at' => now()->toDateTimeString(),
                            ];
                        }
                    } catch (\Exception $e) {
                        // Log individual file upload error but continue with other files
                        \Log::error('Error uploading consultation image: ' . $e->getMessage());
                    }
                }

                // Save image paths to database as JSON
                if (!empty($uploadedImages)) {
                    $requestDocument->consultation_images = json_encode($uploadedImages);
                    $requestDocument->save();
                }
            } catch (\Exception $e) {
                // Log error but don't fail the entire consultation form submission
                \Log::error('Error handling consultation images: ' . $e->getMessage());
            }
        }

        // Handle medicine deduction
        $this->handleMedicineDeduction($requestDocument, $data);

        return $requestDocument;
    }

    /**
     * Display the specified resource.
     */
    public function show(RequestDocuments $requestDocument)
    {
        return view('requests.view', compact('requestDocument'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(RequestDocuments $requestDocument, PatientRepository $patientRepository)
    {
        $campuses = Campus::all();
        $colleges = College::all();
        $courses = Course::all();
        $yearLevels = YearLevel::all();
        $vaccinations = Vaccination::all();
        $diagnoses = Diagnose::all();
        $nursingStaff = User::where('type', 'staff')->get(); // adjust as needed

        return view('requests.edit', compact(
            'requestDocument',
            'campuses',
            'colleges',
            'courses',
            'yearLevels',
            'vaccinations',
            'diagnoses',
            'nursingStaff'
        ));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, RequestDocuments $requestDocument)
    {
        $data = $request->except(['_token', '_method']);

        try {
            if ($requestDocument->document_type === 'medical_certificate') {
                $this->updateMedicalCertificate($requestDocument, $data);
            } elseif ($requestDocument->document_type === 'consultation_form') {
                $this->updateConsultationForm($requestDocument, $data);
            }

            // Check if we have a redirect_patient_id (from patient history page)
            if ($request->has('redirect_patient_id')) {
                $patientId = $request->input('redirect_patient_id');

                // Redirect back to patient history
                if (isRole('clinic_admin')) {
                    return redirect()->route('patients.showMyHistory', ['patient' => $patientId])
                        ->with('success', 'Request document updated successfully.');
                } elseif (isRole('staff')) {
                    return redirect()->route('staff.patients.showMyHistory', ['patient' => $patientId])
                        ->with('success', 'Request document updated successfully.');
                } elseif (isRole('doctor')) {
                    return redirect()->route('doctors.patients.showMyHistory', ['patient' => $patientId])
                        ->with('success', 'Request document updated successfully.');
                }
            }

            // Default: Redirect to request documents index
            $redirectRoute = isRole('clinic_admin') ? 'request-documents.index' : (isRole('staff') ? 'staff.request-documents.index' : (isRole('doctor') ? 'doctors.request-documents.index' : 'request-documents.index'));

            return redirect()->route($redirectRoute)
                ->with('success', 'Request document updated successfully.');
        } catch (\Exception $e) {
            Log::error('Error in update method: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'An error occurred while updating the request document.');
        }
    }

    /**
     * Update a medical certificate document.
     */
    private function updateMedicalCertificate(RequestDocuments $requestDocument, array $data)
    {
        // Prepare the data for insertion
        $data['vital_signs_bp'] = (isset($data['vital_signs_bp_2']) && isset($data['vital_signs_bp_22']))
            ? $data['vital_signs_bp_2'] . '/' . $data['vital_signs_bp_22']
            : $requestDocument->vital_signs_bp;
        $data['vital_signs_pr'] = $data['vital_signs_pr_2'] ?? $requestDocument->vital_signs_pr;
        $data['vital_signs_rr'] = $data['vital_signs_rr_2'] ?? $requestDocument->vital_signs_rr;
        $data['vital_signs_temp'] = $data['vital_signs_temp_2'] ?? $requestDocument->vital_signs_temp;
        $data['vital_signs_height'] = $data['vital_signs_height_2'] ?? $requestDocument->vital_signs_height;
        $data['vital_signs_weight'] = $data['vital_signs_weight_2'] ?? $requestDocument->vital_signs_weight;

        // Only update user/campus/college/course/year_level if user_id is present
        if (isset($data['user_id'])) {
            $user = User::with(['campus', 'college', 'course', 'yearLevel'])->find($data['user_id']);
            if ($user) {
                $data['campus'] = $user->campus->campus_name ?? $requestDocument->campus;
                $data['college'] = $user->college->college_name ?? $requestDocument->college;
                $data['course'] = $user->course->course_name ?? $requestDocument->course;
                $data['year_level'] = $user->yearLevel->year_level_name ?? $requestDocument->year_level;
                $data['date_of_birth'] = $user->dob ?? $requestDocument->date_of_birth;
            } else {
                $data['campus'] = $requestDocument->campus;
                $data['college'] = $requestDocument->college;
                $data['course'] = $requestDocument->course;
                $data['year_level'] = $requestDocument->year_level;
                $data['date_of_birth'] = $requestDocument->date_of_birth;
            }
        } else {
            $data['campus'] = $requestDocument->campus;
            $data['college'] = $requestDocument->college;
            $data['course'] = $requestDocument->course;
            $data['year_level'] = $requestDocument->year_level;
            $data['date_of_birth'] = $requestDocument->date_of_birth;
        }

        $requestDocument->update([
            'name' => $data['name'] ?? $requestDocument->name,
            'age' => $data['age'] ?? $requestDocument->age,
            'gender' => $data['gender'] ?? $requestDocument->gender,
            'date_of_birth' => $data['date_of_birth'],
            'address' => $data['address'] ?? $requestDocument->address,
            'request_of' => $data['request_of'] ?? $requestDocument->request_of,
            'examined_on' => $data['examined_on'] ?? $requestDocument->examined_on,
            'complaints_diagnosis' => $data['complaints_diagnosis'] ?? $requestDocument->complaints_diagnosis,
            'vital_signs_bp' => $data['vital_signs_bp'],
            'vital_signs_pr' => $data['vital_signs_pr'],
            'vital_signs_temp' => $data['vital_signs_temp'],
            'vital_signs_rr' => $data['vital_signs_rr'],
            'vital_signs_height' => $data['vital_signs_height'],
            'vital_signs_weight' => $data['vital_signs_weight'],
            'medical_cert_remarks' => $data['medical_cert_remarks'] ?? $requestDocument->medical_cert_remarks,
            'doc_lic_no' => $data['doc_lic_no'] ?? $requestDocument->doc_lic_no,
            'doc_prt_no' => $data['doc_prt_no'] ?? $requestDocument->doc_prt_no,
            'campus' => $data['campus'],
            'college' => $data['college'],
            'course' => $data['course'],
            'year_level' => $data['year_level'],
        ]);
    }

    /**
     * Update a consultation form document.
     */
    private function updateConsultationForm(RequestDocuments $requestDocument, array $data)
    {
        // Map related names for numeric fields using their IDs
        $data['campus'] = isset($data['campus_id']) ? (Campus::find($data['campus_id'])->campus_name ?? $requestDocument->campus) : $requestDocument->campus;
        $data['college'] = isset($data['college_id']) ? (College::find($data['college_id'])->college_name ?? $requestDocument->college) : $requestDocument->college;
        $data['course'] = isset($data['course_id']) ? (Course::find($data['course_id'])->course_name ?? $requestDocument->course) : $requestDocument->course;
        $data['year_level'] = isset($data['year_level_id']) ? (YearLevel::find($data['year_level_id'])->year_level_name ?? $requestDocument->year_level) : $requestDocument->year_level;
        $data['covid_vaccination'] = isset($data['vaccination_id']) ? (Vaccination::find($data['vaccination_id'])->vaccination_status ?? $requestDocument->covid_vaccination) : $requestDocument->covid_vaccination;
        $data['comorbidities'] = isset($data['comorbidities_id']) ? (Diagnose::find($data['comorbidities_id'])->diagnoses ?? $requestDocument->comorbidities) : $requestDocument->comorbidities;
        $data['nursing_incharge_id'] = $data['nursing_incharged'] ?? $requestDocument->nursing_incharged_id;

        $requestDocument->update([
            'name' => $data['name'] ?? $requestDocument->name,
            'age' => $data['age'] ?? $requestDocument->age,
            'gender' => $data['gender'] ?? $requestDocument->gender,
            'status' => $data['status'] ?? $requestDocument->status,
            'date_of_birth' => $data['date_of_birth'] ?? $requestDocument->date_of_birth,
            'address' => $data['address'] ?? $requestDocument->address,
            'religion' => $data['religion'] ?? $requestDocument->religion,
            'patient_contact' => $data['patient_contact'] ?? $requestDocument->patient_contact,
            'campus' => $data['campus'],
            'college' => $data['college'],
            'course' => $data['course'],
            'year_level' => $data['year_level'],
            'informant' => $data['informant'] ?? $requestDocument->informant,
            'emergency_contact' => $data['emergency_contact'] ?? $requestDocument->emergency_contact,
            'requested_at' => $data['requested_at'] ?? $requestDocument->requested_at,
            'complaints' => $data['complaints'] ?? $requestDocument->complaints,
            'covid_vaccination' => $data['covid_vaccination'],
            'comorbidities' => $data['comorbidities'],
            'allergies' => $data['allergies'] ?? $requestDocument->allergies,
            'admissions_surgeries' => $data['admissions_surgeries'] ?? $requestDocument->admissions_surgeries,
            'maintenance' => $data['maintenance'] ?? $requestDocument->maintenance,
            'pregnancy_status' => $data['pregnancy_status'] ?? $requestDocument->pregnancy_status,
            'lmp_aog' => $data['lmp_aog'] ?? $requestDocument->lmp_aog,
            'vital_signs_bp' => $data['vital_signs_bp'] ?? $requestDocument->vital_signs_bp,
            'vital_signs_pr' => $data['vital_signs_pr'] ?? $requestDocument->vital_signs_pr,
            'vital_signs_temp' => $data['vital_signs_temp'] ?? $requestDocument->vital_signs_temp,
            'vital_signs_rr' => $data['vital_signs_rr'] ?? $requestDocument->vital_signs_rr,
            'vital_signs_o2_sat' => $data['vital_signs_o2_sat'] ?? $requestDocument->vital_signs_o2_sat,
            'vital_signs_height' => $data['vital_signs_height'] ?? $requestDocument->vital_signs_height,
            'vital_signs_weight' => $data['vital_signs_weight'] ?? $requestDocument->vital_signs_weight,
            'pertinent_exam' => $data['pertinent_exam'] ?? $requestDocument->pertinent_exam,
            'assessment' => $data['assessment'] ?? $requestDocument->assessment,
            'plan' => $data['plan'] ?? $requestDocument->plan,
            'consult_mode' => $data['consult_mode'] ?? $requestDocument->consult_mode,
            'nursing_intervention' => $data['nursing_intervention'] ?? $requestDocument->nursing_intervention,
            'nursing_incharged_id' => $data['nursing_incharge_id'],
        ]);

        // Handle image updates
        $this->handleImageUpdates($requestDocument, $data);
    }

    /**
     * Handle image uploads and removals for consultation form updates
     */
    private function handleImageUpdates(RequestDocuments $requestDocument, array $data)
    {
        // Get existing images
        $existingImages = $requestDocument->consultation_images
            ? (is_string($requestDocument->consultation_images)
                ? json_decode($requestDocument->consultation_images, true)
                : $requestDocument->consultation_images)
            : [];

        // Handle removed images
        if (isset($data['removed_images']) && !empty($data['removed_images'])) {
            $removedIndices = json_decode($data['removed_images'], true);

            if (is_array($removedIndices)) {
                foreach ($removedIndices as $index) {
                    if (isset($existingImages[$index])) {
                        // Delete the physical file from public/uploads/
                        $filePath = public_path('uploads/' . $existingImages[$index]['path']);
                        if (file_exists($filePath)) {
                            unlink($filePath);
                        }
                        // Remove from array
                        unset($existingImages[$index]);
                    }
                }
                // Re-index array
                $existingImages = array_values($existingImages);
            }
        }

        // Handle new image uploads
        if (request()->hasFile('consultation_images')) {
            $patientName = str_replace(' ', '_', $requestDocument->name);
            $timestamp = now()->format('Y-m-d_H-i-s');
            $folderPath = "consultation_images/{$patientName}/{$timestamp}";
            $destinationPath = public_path('uploads/' . $folderPath);

            // Create directory if it doesn't exist
            if (!file_exists($destinationPath)) {
                mkdir($destinationPath, 0777, true);
            }

            foreach (request()->file('consultation_images') as $image) {
                // Validate file size (5MB max)
                if ($image->getSize() <= 5 * 1024 * 1024) {
                    $fileName = $image->getClientOriginalName();

                    // Move image to public/uploads/
                    $image->move($destinationPath, $fileName);

                    // Add to existing images array
                    $existingImages[] = [
                        'path' => $folderPath . '/' . $fileName,
                        'name' => $fileName,
                        'size' => $image->getSize(),
                        'uploaded_at' => now()->toDateTimeString(),
                    ];
                }
            }
        }

        // Update database with modified images array
        $requestDocument->consultation_images = !empty($existingImages) ? json_encode($existingImages) : null;
        $requestDocument->save();
    }

    /**
     * Handle medicine deduction from inventory
     */
    private function handleMedicineDeduction(RequestDocuments $requestDocument, array $data)
    {
        if (!isset($data['medicines']) || !is_array($data['medicines'])) {
            return;
        }

        // Process medicines from both Plan and Nursing Intervention
        foreach ($data['medicines'] as $usedFor => $medicines) {
            if (!is_array($medicines)) {
                continue;
            }

            foreach ($medicines as $medicineData) {
                if (empty($medicineData['medicine_id']) || empty($medicineData['quantity'])) {
                    continue;
                }

                $medicineId = $medicineData['medicine_id'];
                $quantity = (int) $medicineData['quantity'];
                $dosageInstructions = $medicineData['dosage_instructions'] ?? null;

                // Find the medicine
                $medicine = \App\Models\Medicine::find($medicineId);

                if (!$medicine) {
                    continue;
                }

                // Check if enough stock is available
                if ($medicine->available_quantity < $quantity) {
                    throw new \Exception("Insufficient stock for {$medicine->name}. Available: {$medicine->available_quantity}, Requested: {$quantity}");
                }

                // Deduct from available quantity
                $medicine->available_quantity -= $quantity;
                $medicine->save();

                // Clear medicine cache to ensure UI updates immediately
                cache()->forget('medicine_' . $medicineId);
                cache()->forget('medicines_list');

                // Record the medicine usage in consultation_medicines table
                \App\Models\ConsultationMedicine::create([
                    'request_document_id' => $requestDocument->id,
                    'medicine_id' => $medicineId,
                    'quantity' => $quantity,
                    'used_for' => $usedFor, // 'plan' or 'nursing'
                    'dosage_instructions' => $dosageInstructions,
                ]);

                // Log the medicine deduction
                \Log::info('Medicine deducted from inventory', [
                    'consultation_id' => $requestDocument->id,
                    'medicine_id' => $medicineId,
                    'medicine_name' => $medicine->name,
                    'quantity_used' => $quantity,
                    'remaining_stock' => $medicine->available_quantity,
                    'used_for' => $usedFor,
                ]);
            }
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(RequestDocuments $request_document)
    {
        try {
            Log::info('Destroy method called', [
                'certificate_id' => $request_document->id,
                'has_redirect_patient_id' => request()->has('redirect_patient_id'),
                'redirect_patient_id' => request()->input('redirect_patient_id'),
                'request_all' => request()->all()
            ]);

            // Delete the request document
            $request_document->delete();

            Log::info('Certificate deleted successfully', ['certificate_id' => $request_document->id]);

            // Check if we have a redirect_patient_id (from patient history page)
            if (request()->has('redirect_patient_id')) {
                $patientId = request()->input('redirect_patient_id');

                Log::info('Redirecting to patient history', ['patient_id' => $patientId]);

                // Redirect back to patient history
                if (isRole('clinic_admin')) {
                    return redirect()->route('patients.showMyHistory', ['patient' => $patientId])
                        ->with('success', 'Medical certificate deleted successfully.');
                } elseif (isRole('staff')) {
                    return redirect()->route('staff.patients.showMyHistory', ['patient' => $patientId])
                        ->with('success', 'Medical certificate deleted successfully.');
                } elseif (isRole('doctor')) {
                    return redirect()->route('doctors.patients.showMyHistory', ['patient' => $patientId])
                        ->with('success', 'Medical certificate deleted successfully.');
                }
            }

            Log::info('Redirecting to documents index');

            // Default: Redirect to request documents index
            $redirectRoute = isRole('clinic_admin') ? 'request-documents.index' : (isRole('staff') ? 'staff.request-documents.index' : (isRole('doctor') ? 'doctors.request-documents.index' :
                'request-documents.index'));

            return redirect()->route($redirectRoute)
                ->with('success', 'Request document deleted successfully.');
        } catch (\Exception $e) {
            Log::error('Error deleting request document: ' . $e->getMessage());

            return redirect()->back()
                ->with('error', 'An error occurred while deleting the document.');
        }
    }


    public function searchUsers(Request $request)
    {
        try {
            $search = $request->input('query');

            if (empty($search)) {
                return response()->json(['error' => 'Query parameter is required'], 400);
            }

            // Search patients by first name, last name
            $patients = Patient::whereHas('user', function ($query) use ($search) {
                $query->where('first_name', 'LIKE', "%{$search}%")
                    ->orWhere('last_name', 'LIKE', "%{$search}%");
            })
                ->select('id', 'patient_unique_id', 'user_id')
                ->with(['user:id,first_name,last_name,dob,gender,contact,emergency_contact_name,emergency_contact_no,emergency_relationship,campus_id,college_id,course_id,year_level_id,vaccination_id', 'address' => function ($query) {
                    $query->select('id', 'owner_id', 'owner_type', 'address1', 'country_id', 'state_id', 'city_id', 'postal_code');
                }])
                ->get();

            return response()->json($patients);
        } catch (\Exception $e) {
            // Log the error for debugging
            Log::error('Error in searchUsers: ' . $e->getMessage());

            // Return a generic error response
            return response()->json(['error' => 'An error occurred while processing your request'], 500);
        }
    }

    public function exportPdf($id)
    {
        try {
            // Fetch the request document by ID
            $requestDocument = RequestDocuments::findOrFail($id);

            // Choose the PDF layout based on document type
            if ($requestDocument->document_type === 'medical_certificate') {
                $view = 'requests.pdf_medical_certificate';
                $pdf = Pdf::loadView($view, compact('requestDocument'))->setPaper([0, 0, 612, 396], 'landscape'); // 5.5"x8.5" in points
            } else {
                $view = 'requests.pdf_consultation_form';
                $pdf = Pdf::loadView($view, compact('requestDocument'))->setPaper([0, 0, 612, 936], 'portrait'); // 8.5"x13"
            }

            // Check if request wants to stream (for printing) or download
            if (request()->has('action') && request()->get('action') === 'print') {
                // Stream PDF for printing (opens in browser)
                return $pdf->stream('request_document_' . $id . '.pdf');
            }

            // Default: Return the PDF as a download
            return $pdf->download('request_document_' . $id . '.pdf');
        } catch (\Exception $e) {
            // Log the error and redirect back with an error message
            Log::error('Error exporting PDF: ' . $e->getMessage());
            return redirect()->back()->with('error', 'An error occurred while exporting the PDF.');
        }
    }
}
