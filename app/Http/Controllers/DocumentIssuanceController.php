<?php

namespace App\Http\Controllers;

use App\Models\Campus;
use App\Models\College;
use App\Models\Course;
use App\Models\Department;
use App\Models\Diagnose;
use App\Models\Doctor;
use App\Models\Office;
use App\Models\Patient;
use App\Models\DocumentIssuance;
use App\Models\Staff;
use App\Models\User;
use App\Models\Vaccination;
use App\Models\YearLevel;
use App\Repositories\PatientRepository;
use App\Traits\LogsActivity;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DocumentIssuanceController extends Controller
{
    use LogsActivity;
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('document_issuances.index');
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
        if ($user && $user->type === User::PATIENT) {
            $patient = $user->patient;
        }

        $availableDoctors = $this->getAvailableCertificateDoctors();

        return view('document_issuances.create', compact('data', 'user', 'patient', 'availableDoctors'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $data = $request->except('_token');
        $documentType = $request->input('document_type');
        
        try {
            if ($documentType === 'medical_certificate' || $documentType === 'excuse_slip') {
                $this->storeMedicalCertificate($data);
            } elseif ($documentType === 'consultation_form') {
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
                        : ($data['document_type'] === 'excuse_slip' ? 'Excuse slip created successfully.' : 'Consultation form created successfully.');

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
            $redirectRoute = isRole('clinic_admin') ? 'document-issuances.index' : (isRole('staff') ? 'staff.document-issuances.index' : (isRole('doctor') ? 'doctors.document-issuances.index' : 'document-issuances.index'));
            $documentModule = ($data['document_type'] ?? null) === 'consultation_form' ? 'consultation' : 'certificate';

            return redirect()->route($redirectRoute, ['module' => $documentModule])
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
        $data['vital_signs_bp'] = ($data['vital_signs_bp_2'] ?? '') . '/' . ($data['vital_signs_bp_22'] ?? '');
        $data['vital_signs_pr'] = $data['vital_signs_pr_2'] ?? null;
        $data['vital_signs_rr'] = $data['vital_signs_rr_2'] ?? null;
        $data['vital_signs_temp'] = $data['vital_signs_temp_2'] ?? null;
        $data['vital_signs_height'] = $data['vital_signs_height_2'] ?? null;
        $data['vital_signs_weight'] = $data['vital_signs_weight_2'] ?? null;

        // Log the user_id for debugging
        Log::info('Attempting to create medical certificate for user_id: ' . ($data['user_id'] ?? 'NULL'));

        // Retrieve the user and related IDs (allow null for manual entries)
        $user = !empty($data['user_id']) ? User::with(['campus', 'college', 'course', 'yearLevel'])->find($data['user_id']) : null;
        
        // Use request data for campus/college/course/year_level if provided (allows manual override)
        $data['campus'] = $data['campus'] ?? ($user ? ($user->campus->campus_name ?? 'Unknown Campus') : 'N/A');
        $data['college'] = $data['college'] ?? ($user ? ($user->college->college_name ?? 'Unknown College') : 'N/A');
        
        // Calculate age from DOB if not provided in request
        if (!isset($data['age']) || $data['age'] === '') {
            if ($user && $user->dob) {
                try {
                    $data['age'] = \Carbon\Carbon::parse($user->dob)->age;
                } catch (\Exception $e) {
                    $data['age'] = 0;
                }
            } else {
                $data['age'] = 0;
            }
        }

        $data['gender'] = $data['gender'] ?? ($user ? ($user->gender == 1 ? 'Male' : 'Female') : 'N/A');
        
        // Improve address fetching
        if (!isset($data['address']) || $data['address'] === '' || $data['address'] === 'N/A') {
            if ($user) {
                $data['address'] = $user->patient?->address?->full_address 
                                ?? $user->patient?->address?->address1 
                                ?? $user->address?->full_address 
                                ?? $user->address?->address1 
                                ?? 'N/A';
            } else {
                $data['address'] = 'N/A';
            }
        }

        $data['dob'] = $user ? $user->dob : null;

        // Insert the data into the database
        $requestDocument = DocumentIssuance::create([
            'document_type' => $data['document_type'],
            'document_creator_id' => $data['document_creator_id'] ?? auth()->id(),
            'user_id' => $data['user_id'] ?? null,
            'name' => $data['name'] ?? 'N/A',
            'age' => $data['age'],
            'gender' => $data['gender'],
            'date_of_birth' => $data['dob'] ?? null,
            'address' => $data['address'],
            'request_of' => $data['request_of'] ?? ($data['name'] ?? 'N/A'),
            'requested_at' => now()->format('Y-m-d'),
            'campus' => $data['campus'],
            'college' => $data['college'],
            'course' => $data['course'] ?? null,
            'year_level' => $data['year_level'] ?? null,
            'examined_on' => $data['examined_on'] ?? null,
            'complaints_diagnosis' => $data['complaints_diagnosis'] ?? null,
            'vital_signs_bp' => $data['vital_signs_bp'],
            'vital_signs_pr' => $data['vital_signs_pr'],
            'vital_signs_temp' => $data['vital_signs_temp'],
            'vital_signs_rr' => $data['vital_signs_rr'],
            'vital_signs_height' => $data['vital_signs_height'],
            'vital_signs_weight' => $data['vital_signs_weight'],
            'medical_cert_remarks' => $data['medical_cert_remarks'] ?? null,
            'doc_lic_no' => $data['doc_lic_no'] ?? null,
            'doc_prt_no' => $data['doc_prt_no'] ?? null,
            'subjects' => $data['subjects'] ?? null,
        ]);

        // Log medical certificate creation activity
        self::logMedicalCertificateCreation($requestDocument);
    }

    /**
     * Store a consultation form document.
     */
    private function storeConsultationForm(array $data)
    {
        // Retrieve the user and related IDs (allow null for manual entries)
        $user = !empty($data['user_id']) ? User::with(['campus', 'college', 'course', 'yearLevel'])->find($data['user_id']) : null;

        // Map related names for numeric fields using their IDs
        $data['campus'] = $data['campus'] ?? (Campus::find($data['campus_id'] ?? null)?->campus_name ?? ($user?->campus?->campus_name ?? 'Unknown Campus'));
        $data['college'] = $data['college'] ?? (College::find($data['college_id'] ?? null)?->college_name ?? ($user?->college?->college_name ?? 'Unknown College'));
        $data['course'] = $data['course'] ?? (Course::find($data['course_id'] ?? null)?->course_name ?? ($user?->course?->course_name ?? 'Unknown Course'));
        $data['year_level'] = $data['year_level'] ?? (YearLevel::find($data['year_level_id'] ?? null)?->year_level_name ?? ($user?->yearLevel?->year_level_name ?? 'Unknown Year Level'));
        $data['covid_vaccination'] = $data['covid_vaccination'] ?? (Vaccination::find($data['vaccination_id'] ?? null)?->vaccination_status ?? ($user?->vaccination?->vaccination_status ?? 'Unknown Vaccination'));

        // Handle comorbidities - accept custom input or predefined values
        $data['comorbidities_value'] = $data['comorbidities_custom'] ?? 'None';

        // Calculate age from DOB if not provided in request
        if (!isset($data['age']) || $data['age'] === '') {
            if ($user && $user->dob) {
                try {
                    $data['age'] = \Carbon\Carbon::parse($user->dob)->age;
                } catch (\Exception $e) {
                    $data['age'] = 0;
                }
            } else {
                $data['age'] = 0;
            }
        }

        $data['gender'] = $data['gender'] ?? ($user ? ($user->gender == 1 ? 'Male' : 'Female') : 'N/A');
        
        // Improve address fetching
        if (!isset($data['address']) || $data['address'] === '' || $data['address'] === 'N/A') {
            if ($user) {
                $data['address'] = $user->patient?->address?->full_address 
                                ?? $user->patient?->address?->address1 
                                ?? $user->address?->full_address 
                                ?? $user->address?->address1 
                                ?? 'N/A';
            } else {
                $data['address'] = 'N/A';
            }
        }

        $requestDocument = DocumentIssuance::create([
            'document_type' => $data['document_type'] ?? 'consultation_form',
            'document_creator_id' => $data['document_creator_id'] ?? auth()->id(),
            'user_id' => $data['user_id'] ?? null,
            'name' => $data['name'] ?? 'N/A',
            'age' => $data['age'],
            'gender' => $data['gender'],
            'status' => $data['status'] ?? 'N/A',
            'date_of_birth' => $data['date_of_birth'] ?? ($user?->dob ?? null),
            'address' => $data['address'],
            'religion' => $data['religion'] ?? null,
            'patient_contact' => $data['patient_contact'] ?? ($user?->contact ?? null),
            'campus' => $data['campus'],
            'college' => $data['college'],
            'course' => $data['course'],
            'year_level' => $data['year_level'],
            'informant' => $data['informant'] ?? null,
            'emergency_contact' => $data['emergency_contact'] ?? null,
            'requested_at' => $data['requested_at'] ?? now()->format('Y-m-d'),
            'complaints' => $data['complaints'] ?? null,
            'note' => $data['note'] ?? null,
            'covid_vaccination' => $data['covid_vaccination'],
            'comorbidities' => $data['comorbidities_value'],
            'allergies' => $data['allergies'] ?? null,
            'admissions_surgeries' => $data['admissions_surgeries'] ?? null,
            'maintenance' => $data['maintenance'] ?? null,
            'pregnancy_status' => $data['pregnancy_status'] ?? null,
            'lmp_aog' => $data['lmp_aog'] ?? null,
            'vital_signs_bp' => $data['vital_signs_bp'] ?? null,
            'vital_signs_pr' => $data['vital_signs_pr'] ?? null,
            'vital_signs_temp' => $data['vital_signs_temp'] ?? null,
            'vital_signs_rr' => $data['vital_signs_rr'] ?? null,
            'vital_signs_o2_sat' => $data['vital_signs_o2_sat'] ?? null,
            'vital_signs_height' => $data['vital_signs_height'] ?? null,
            'vital_signs_weight' => $data['vital_signs_weight'] ?? null,
            'pertinent_exam' => $data['pertinent_exam'] ?? null,
            'assessment' => $data['assessment'] ?? null,
            'plan' => $data['plan'] ?? null,
            'consult_mode' => $data['consult_mode'] ?? 'physical',
            'nursing_intervention' => $data['nursing_intervention'] ?? null,
            'nursing_incharged_id' => $data['nursing_incharged'] ?? null,
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
                        Log::error('Error uploading consultation image: ' . $e->getMessage());
                    }
                }

                // Save image paths to database as JSON
                if (!empty($uploadedImages)) {
                    $requestDocument->consultation_images = json_encode($uploadedImages);
                    $requestDocument->save();
                }
            } catch (\Exception $e) {
                // Log error but don't fail the entire consultation form submission
                Log::error('Error handling consultation images: ' . $e->getMessage());
            }
        }

        // Handle medicine deduction
        $this->handleMedicineDeduction($requestDocument, $data);

        // Log consultation form creation activity
        self::logConsultationCreation($requestDocument);

        return $requestDocument;
    }

    /**
     * Display the specified resource.
     */
    public function show(DocumentIssuance $document_issuance)
    {
        $requestDocument = $document_issuance;
        $medicalCertificateDoctorName = ($requestDocument->document_type === 'medical_certificate' || $requestDocument->document_type === 'excuse_slip')
            ? $this->resolveMedicalCertificateDoctorName($requestDocument)
            : null;

        return view('document_issuances.view', compact('requestDocument', 'medicalCertificateDoctorName'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(DocumentIssuance $document_issuance, PatientRepository $patientRepository)
    {
        $requestDocument = $document_issuance;
        $campuses = Campus::all();
        $colleges = College::all();
        $courses = Course::all();
        $yearLevels = YearLevel::all();
        $departments = Department::all();
        $offices = Office::all();
        $vaccinations = Vaccination::all();
        $diagnoses = Diagnose::all();
        $nursingStaff = User::where('type', 'staff')->get(); // adjust as needed
        $availableDoctors = $this->getAvailableCertificateDoctors();

        // Get the user data associated with this request document
        $user = User::find($requestDocument->user_id);

        // Load existing consultation medicines with medicine details
        $existingMedicines = $requestDocument->consultationMedicines()->with('medicine')->get();

        return view('document_issuances.edit', compact(
            'requestDocument',
            'campuses',
            'colleges',
            'courses',
            'yearLevels',
            'departments',
            'offices',
            'vaccinations',
            'diagnoses',
            'nursingStaff',
            'availableDoctors',
            'user',
            'existingMedicines'
        ));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, DocumentIssuance $document_issuance)
    {
        $requestDocument = $document_issuance;
        $data = $request->except(['_token', '_method']);
        $documentType = $request->input('document_type', $requestDocument->document_type);
        
        try {
            if ($documentType === 'medical_certificate' || $documentType === 'excuse_slip') {
                $this->updateMedicalCertificate($requestDocument, $data);
            } elseif ($documentType === 'consultation_form') {
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
            $redirectRoute = isRole('clinic_admin') ? 'document-issuances.index' : (isRole('staff') ? 'staff.document-issuances.index' : (isRole('doctor') ? 'doctors.document-issuances.index' : 'document-issuances.index'));
            $documentModule = $request->input('redirect_module', $requestDocument->document_type === 'consultation_form' ? 'consultation' : 'certificate');

            return redirect()->route($redirectRoute, ['module' => $documentModule])
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
    private function updateMedicalCertificate(DocumentIssuance $requestDocument, array $data)
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
        if (isset($data['user_id']) && !empty($data['user_id'])) {
            $user = User::with(['campus', 'college', 'course', 'yearLevel'])->find($data['user_id']);
            if ($user) {
                $data['campus'] = $user->campus->campus_name ?? $requestDocument->campus;
                $data['college'] = $user->college->college_name ?? $requestDocument->college;
                $data['course'] = $user->course->course_name ?? $requestDocument->course;
                $data['year_level'] = $user->yearLevel->year_level_name ?? $requestDocument->year_level;
                $data['date_of_birth'] = $user->dob ?? $requestDocument->date_of_birth;
                
                if ((!isset($data['age']) || $data['age'] === '') && $user->dob) {
                    try {
                        $data['age'] = \Carbon\Carbon::parse($user->dob)->age;
                    } catch (\Exception $e) {
                        $data['age'] = $requestDocument->age;
                    }
                }
                
                if (!isset($data['gender']) || empty($data['gender'])) {
                    $data['gender'] = ($user->gender == 1 ? 'Male' : 'Female');
                }

                if (!isset($data['address']) || empty($data['address']) || $data['address'] === 'N/A') {
                    $data['address'] = $user->patient?->address?->full_address 
                                    ?? $user->patient?->address?->address1 
                                    ?? $user->address?->full_address 
                                    ?? $user->address?->address1 
                                    ?? $requestDocument->address;
                }
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
            'subjects' => $data['subjects'] ?? $requestDocument->subjects,
        ]);

        // Log medical certificate update (will update existing log instead of creating new)
        self::logMedicalCertificateCreation($requestDocument->fresh());
    }

    /**
     * Update a consultation form document.
     */
    private function updateConsultationForm(DocumentIssuance $requestDocument, array $data)
    {
        // Retrieve the user and related IDs (allow null for manual entries)
        $user = !empty($data['user_id']) ? User::with(['campus', 'college', 'course', 'yearLevel'])->find($data['user_id']) : null;

        // Map related names for numeric fields using their IDs
        $data['campus'] = isset($data['campus_id']) ? (Campus::find($data['campus_id'])->campus_name ?? $requestDocument->campus) : $requestDocument->campus;
        $data['college'] = isset($data['college_id']) ? (College::find($data['college_id'])->college_name ?? $requestDocument->college) : $requestDocument->college;
        $data['course'] = isset($data['course_id']) ? (Course::find($data['course_id'])->course_name ?? $requestDocument->course) : $requestDocument->course;
        $data['year_level'] = isset($data['year_level_id']) ? (YearLevel::find($data['year_level_id'])->year_level_name ?? $requestDocument->year_level) : $requestDocument->year_level;
        $data['covid_vaccination'] = isset($data['vaccination_id']) ? (Vaccination::find($data['vaccination_id'])->vaccination_status ?? $requestDocument->covid_vaccination) : $requestDocument->covid_vaccination;

        // Calculate age from DOB if not provided in request
        if ((!isset($data['age']) || $data['age'] === '') && $user && $user->dob) {
            try {
                $data['age'] = \Carbon\Carbon::parse($user->dob)->age;
            } catch (\Exception $e) {
                $data['age'] = $requestDocument->age;
            }
        }

        if (!isset($data['gender']) || empty($data['gender'])) {
            if ($user) {
                $data['gender'] = ($user->gender == 1 ? 'Male' : 'Female');
            }
        }

        if (!isset($data['address']) || empty($data['address']) || $data['address'] === 'N/A') {
            if ($user) {
                $data['address'] = $user->patient?->address?->full_address 
                                ?? $user->patient?->address?->address1 
                                ?? $user->address?->full_address 
                                ?? $user->address?->address1 
                                ?? $requestDocument->address;
            }
        }

        // Handle comorbidities - accept custom input or keep existing value
        $data['comorbidities'] = isset($data['comorbidities_custom']) ? $data['comorbidities_custom'] : $requestDocument->comorbidities;

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
            'note' => $data['note'] ?? $requestDocument->note,
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

        // Handle medicine updates (restore removed, deduct newly added)
        $this->handleMedicineUpdates($requestDocument, $data);

        // Log consultation form update (will update existing log instead of creating new)
        self::logConsultationCreation($requestDocument->fresh());
    }

    /**
     * Handle image uploads and removals for consultation form updates
     */
    private function handleImageUpdates(DocumentIssuance $requestDocument, array $data)
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
                // Get file size BEFORE moving (important: must be done before move())
                $fileSize = $image->getSize();

                // Validate file size (5MB max)
                if ($fileSize <= 5 * 1024 * 1024) {
                    $fileName = $image->getClientOriginalName();

                    // Move image to public/uploads/
                    $image->move($destinationPath, $fileName);

                    // Add to existing images array (use stored size, not getSize() after move)
                    $existingImages[] = [
                        'path' => $folderPath . '/' . $fileName,
                        'name' => $fileName,
                        'size' => $fileSize,
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
     * Handle medicine updates during consultation form edit
     * This method compares existing medicines with new submission and:
     * 1. Restores stock for removed medicines
     * 2. Deducts stock only for newly added medicines
     */
    private function handleMedicineUpdates(DocumentIssuance $requestDocument, array $data)
    {
        // Get existing medicines from database
        $existingMedicines = $requestDocument->consultationMedicines()->get();

        // Create a map of existing medicines for easy lookup
        // Key format: "medicineId_dosage_usedFor"
        $existingMedicinesMap = [];
        foreach ($existingMedicines as $existingMedicine) {
            $key = "{$existingMedicine->medicine_id}_{$existingMedicine->dosage}_{$existingMedicine->used_for}";
            $existingMedicinesMap[$key] = $existingMedicine;
        }

        // Create a map of new medicines from form submission
        $newMedicinesMap = [];
        if (isset($data['medicines']) && is_array($data['medicines'])) {
            foreach ($data['medicines'] as $usedFor => $medicines) {
                if (!is_array($medicines)) {
                    continue;
                }

                foreach ($medicines as $medicineData) {
                    if (empty($medicineData['medicine_id']) || empty($medicineData['quantity'])) {
                        continue;
                    }

                    $medicineId = $medicineData['medicine_id'];
                    $dosage = $medicineData['dosage'] ?? null;
                    $key = "{$medicineId}_{$dosage}_{$usedFor}";

                    $newMedicinesMap[$key] = [
                        'medicine_id' => $medicineId,
                        'dosage' => $dosage,
                        'quantity' => (int) $medicineData['quantity'],
                        'used_for' => $usedFor,
                        'dosage_instructions' => $medicineData['dosage_instructions'] ?? null,
                    ];
                }
            }
        }

        // STEP 1: Restore stock for removed medicines (exists in DB but not in new submission)
        foreach ($existingMedicinesMap as $key => $existingMedicine) {
            if (!isset($newMedicinesMap[$key])) {
                // This medicine was removed, restore its stock
                $this->restoreMedicineStock($existingMedicine);

                // Delete the consultation medicine record
                $existingMedicine->delete();

                Log::info('Medicine removed and stock restored during edit', [
                    'consultation_id' => $requestDocument->id,
                    'medicine_id' => $existingMedicine->medicine_id,
                    'dosage' => $existingMedicine->dosage,
                    'quantity_restored' => $existingMedicine->quantity,
                    'used_for' => $existingMedicine->used_for,
                ]);
            }
        }

        // STEP 2: Handle quantity changes for medicines that still exist
        foreach ($existingMedicinesMap as $key => $existingMedicine) {
            if (isset($newMedicinesMap[$key])) {
                $newQuantity = $newMedicinesMap[$key]['quantity'];
                $oldQuantity = $existingMedicine->quantity;

                if ($newQuantity != $oldQuantity) {
                    $quantityDiff = $newQuantity - $oldQuantity;

                    if ($quantityDiff > 0) {
                        // Quantity increased, deduct more stock
                        $this->deductMedicineStock(
                            $existingMedicine->medicine_id,
                            $existingMedicine->dosage,
                            $quantityDiff,
                            $requestDocument
                        );
                    } else if ($quantityDiff < 0) {
                        // Quantity decreased, restore some stock
                        $this->restoreMedicineStockAmount(
                            $existingMedicine->medicine_id,
                            $existingMedicine->dosage,
                            abs($quantityDiff)
                        );
                    }

                    // Update the consultation medicine record
                    $existingMedicine->quantity = $newQuantity;
                    $existingMedicine->dosage_instructions = $newMedicinesMap[$key]['dosage_instructions'];
                    $existingMedicine->save();

                    Log::info('Medicine quantity updated during edit', [
                        'consultation_id' => $requestDocument->id,
                        'medicine_id' => $existingMedicine->medicine_id,
                        'dosage' => $existingMedicine->dosage,
                        'old_quantity' => $oldQuantity,
                        'new_quantity' => $newQuantity,
                        'quantity_diff' => $quantityDiff,
                    ]);
                }
            }
        }

        // STEP 3: Deduct stock for newly added medicines (exists in new submission but not in DB)
        foreach ($newMedicinesMap as $key => $newMedicine) {
            if (!isset($existingMedicinesMap[$key])) {
                // This is a new medicine, deduct its stock
                $this->deductMedicineStock(
                    $newMedicine['medicine_id'],
                    $newMedicine['dosage'],
                    $newMedicine['quantity'],
                    $requestDocument
                );

                // Create new consultation medicine record
                \App\Models\ConsultationMedicine::create([
                    'request_document_id' => $requestDocument->id,
                    'medicine_id' => $newMedicine['medicine_id'],
                    'quantity' => $newMedicine['quantity'],
                    'dosage' => $newMedicine['dosage'],
                    'used_for' => $newMedicine['used_for'],
                    'dosage_instructions' => $newMedicine['dosage_instructions'],
                ]);

                Log::info('New medicine added during edit', [
                    'consultation_id' => $requestDocument->id,
                    'medicine_id' => $newMedicine['medicine_id'],
                    'dosage' => $newMedicine['dosage'],
                    'quantity_deducted' => $newMedicine['quantity'],
                    'used_for' => $newMedicine['used_for'],
                ]);
            }
        }
    }

    /**
     * Restore medicine stock when medicine is removed from consultation
     */
    private function restoreMedicineStock(\App\Models\ConsultationMedicine $consultationMedicine)
    {
        $medicine = \App\Models\Medicine::find($consultationMedicine->medicine_id);

        if (!$medicine) {
            return;
        }

        // Restore to medicine's total available quantity
        $medicine->available_quantity += $consultationMedicine->quantity;
        $medicine->save();

        // Restore to the most recent batch of the same dosage (LIFO - Last In, First Out for restoration)
        $remainingToRestore = $consultationMedicine->quantity;
        $purchasedMedicines = \App\Models\PurchasedMedicine::where('medicine_id', $consultationMedicine->medicine_id)
            ->where('dosage', $consultationMedicine->dosage)
            ->orderBy('manufacturing_date', 'desc') // LIFO: newest first for restoration
            ->get();

        foreach ($purchasedMedicines as $purchasedMedicine) {
            if ($remainingToRestore <= 0) {
                break;
            }

            // Add the quantity back to this batch
            $purchasedMedicine->quantity += $remainingToRestore;
            $purchasedMedicine->save();
            $remainingToRestore = 0;
        }

        // Clear medicine cache
        cache()->forget('medicine_' . $consultationMedicine->medicine_id);
        cache()->forget('medicines_list');
    }

    /**
     * Restore a specific amount of medicine stock
     */
    private function restoreMedicineStockAmount($medicineId, $dosage, $quantity)
    {
        $medicine = \App\Models\Medicine::find($medicineId);

        if (!$medicine) {
            return;
        }

        // Restore to medicine's total available quantity
        $medicine->available_quantity += $quantity;
        $medicine->save();

        // Restore to the most recent batch of the same dosage
        $purchasedMedicine = \App\Models\PurchasedMedicine::where('medicine_id', $medicineId)
            ->where('dosage', $dosage)
            ->orderBy('manufacturing_date', 'desc')
            ->first();

        if ($purchasedMedicine) {
            $purchasedMedicine->quantity += $quantity;
            $purchasedMedicine->save();
        }

        // Clear medicine cache
        cache()->forget('medicine_' . $medicineId);
        cache()->forget('medicines_list');
    }

    /**
     * Deduct medicine stock from inventory
     */
    private function deductMedicineStock($medicineId, $dosage, $quantity, DocumentIssuance $requestDocument)
    {
        $medicine = \App\Models\Medicine::find($medicineId);

        if (!$medicine) {
            throw new \Exception("Medicine not found (ID: {$medicineId})");
        }

        // Check if enough stock is available in the specific dosage
        $availableDosageQty = \App\Models\PurchasedMedicine::where('medicine_id', $medicineId)
            ->where('dosage', $dosage)
            ->sum('quantity');

        if ($availableDosageQty < $quantity) {
            throw new \Exception("Insufficient stock for {$medicine->name} ({$dosage}). Available: {$availableDosageQty}, Requested: {$quantity}");
        }

        // Deduct from specific dosage quantities (FIFO - First In, First Out)
        $remainingToDeduct = $quantity;
        $purchasedMedicines = \App\Models\PurchasedMedicine::where('medicine_id', $medicineId)
            ->where('dosage', $dosage)
            ->where('quantity', '>', 0)
            ->orderBy('manufacturing_date', 'asc') // FIFO: oldest first
            ->get();

        foreach ($purchasedMedicines as $purchasedMedicine) {
            if ($remainingToDeduct <= 0) {
                break;
            }

            if ($purchasedMedicine->quantity >= $remainingToDeduct) {
                // This batch has enough quantity
                $purchasedMedicine->quantity -= $remainingToDeduct;
                $purchasedMedicine->save();
                $remainingToDeduct = 0;
            } else {
                // Use all from this batch and continue
                $remainingToDeduct -= $purchasedMedicine->quantity;
                $purchasedMedicine->quantity = 0;
                $purchasedMedicine->save();
            }
        }

        // Deduct from medicine's total available quantity
        $medicine->available_quantity -= $quantity;
        $medicine->save();

        // Clear medicine cache
        cache()->forget('medicine_' . $medicineId);
        cache()->forget('medicines_list');

        // Log medicine usage activity
        self::logMedicineUsage($medicine, $quantity, $requestDocument->name, $requestDocument);
    }

    /**
     * Handle medicine deduction from inventory
     */
    private function handleMedicineDeduction(DocumentIssuance $requestDocument, array $data)
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
                $dosage = $medicineData['dosage'] ?? null;
                $dosageInstructions = $medicineData['dosage_instructions'] ?? null;

                // Find the medicine
                $medicine = \App\Models\Medicine::find($medicineId);

                if (!$medicine) {
                    continue;
                }

                // Check if enough stock is available in the specific dosage
                $availableDosageQty = \App\Models\PurchasedMedicine::where('medicine_id', $medicineId)
                    ->where('dosage', $dosage)
                    ->sum('quantity');

                if ($availableDosageQty < $quantity) {
                    throw new \Exception("Insufficient stock for {$medicine->name} ({$dosage}). Available: {$availableDosageQty}, Requested: {$quantity}");
                }

                // Deduct from specific dosage quantities (FIFO - First In, First Out)
                $remainingToDeduct = $quantity;
                $purchasedMedicines = \App\Models\PurchasedMedicine::where('medicine_id', $medicineId)
                    ->where('dosage', $dosage)
                    ->where('quantity', '>', 0)
                    ->orderBy('manufacturing_date', 'asc') // FIFO: oldest first
                    ->get();

                foreach ($purchasedMedicines as $purchasedMedicine) {
                    if ($remainingToDeduct <= 0) {
                        break;
                    }

                    if ($purchasedMedicine->quantity >= $remainingToDeduct) {
                        // This batch has enough quantity
                        $purchasedMedicine->quantity -= $remainingToDeduct;
                        $purchasedMedicine->save();
                        $remainingToDeduct = 0;
                    } else {
                        // Use all from this batch and continue
                        $remainingToDeduct -= $purchasedMedicine->quantity;
                        $purchasedMedicine->quantity = 0;
                        $purchasedMedicine->save();
                    }
                }

                // Deduct from medicine's total available quantity
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
                    'dosage' => $dosage,
                    'used_for' => $usedFor, // 'plan' or 'nursing'
                    'dosage_instructions' => $dosageInstructions,
                ]);

                // Log medicine usage activity
                self::logMedicineUsage($medicine, $quantity, $requestDocument->name, $requestDocument);

                // Log the medicine deduction
                Log::info('Medicine deducted from inventory', [
                    'consultation_id' => $requestDocument->id,
                    'medicine_id' => $medicineId,
                    'medicine_name' => $medicine->name,
                    'dosage' => $dosage,
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
    public function destroy(DocumentIssuance $document_issuance)
    {
        $request_document = $document_issuance;
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
            $redirectRoute = isRole('clinic_admin') ? 'document-issuances.index' : (isRole('staff') ? 'staff.document-issuances.index' : (isRole('doctor') ? 'doctors.document-issuances.index' :
                'document-issuances.index'));
            $documentModule = request()->input('redirect_module', $request_document->document_type === 'consultation_form' ? 'consultation' : 'certificate');

            return redirect()->route($redirectRoute, ['module' => $documentModule])
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
            $patients = Patient::whereHas('user', function ($query) {
                $query->where('type', User::PATIENT);
            })
                ->whereHas('user', function ($query) use ($search) {
                    $query->where('first_name', 'LIKE', "%{$search}%")
                        ->orWhere('last_name', 'LIKE', "%{$search}%")
                        ->orWhere('university_id_number', 'LIKE', "%{$search}%")
                        ->orWhere('employee_id', 'LIKE', "%{$search}%");
                })
                ->select('id', 'patient_unique_id', 'user_id', 'allergies', 'comorbidities', 'admissions_surgeries', 'maintenance')
                ->with([
                    'user' => function ($query) {
                        $query->select('id', 'first_name', 'last_name', 'dob', 'gender', 'contact', 'campus_id', 'college_id', 'course_id', 'year_level_id', 'university_id_number', 'employee_id')
                            ->with(['campus', 'college', 'course', 'yearLevel']);
                    },
                    'address' => function ($query) {
                        $query->select('id', 'owner_id', 'owner_type', 'address1', 'country_id', 'state_id', 'city_id', 'barangay_id', 'postal_code')
                            ->with(['barangay:id,name,city_id', 'city:id,name,state_id', 'state:id,name']);
                    }
                ])
                ->get();

            // Add full_address and use university_id_number as the primary patient identifier.
            $patients->each(function ($patient) {
                $patient->patient_unique_id = $patient->user->university_id_number ?: $patient->patient_unique_id;

                if ($patient->address) {
                    $patient->address->full_address = $patient->address->full_address;
                }
            });

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
            // Set a higher time limit for PDF generation if needed
            set_time_limit(120);

            // Fetch the request document by ID
            $requestDocument = DocumentIssuance::findOrFail($id);
            $medicalCertificateDoctorName = null;

            // Optimization: Convert logos to base64 to avoid local HTTP requests or slow file lookups in DomPDF
            $norsuLogoPath = public_path('assets/image/norsu_logo.png');
            $clinicLogoPath = public_path('assets/image/norsu_clinic_logo.png');
            
            $norsuLogoBase64 = '';
            $clinicLogoBase64 = '';
            
            if (file_exists($norsuLogoPath)) {
                $type = pathinfo($norsuLogoPath, PATHINFO_EXTENSION);
                $data = file_get_contents($norsuLogoPath);
                $norsuLogoBase64 = 'data:image/' . $type . ';base64,' . base64_encode($data);
            }
            
            if (file_exists($clinicLogoPath)) {
                $type = pathinfo($clinicLogoPath, PATHINFO_EXTENSION);
                $data = file_get_contents($clinicLogoPath);
                $clinicLogoBase64 = 'data:image/' . $type . ';base64,' . base64_encode($data);
            }

            // Choose the PDF layout based on document type
            if ($requestDocument->document_type === 'medical_certificate') {
                $view = 'document_issuances.pdf_medical_certificate';
                $medicalCertificateDoctorName = $this->resolveMedicalCertificateDoctorName($requestDocument);
                $pdf = Pdf::loadView($view, compact('requestDocument', 'medicalCertificateDoctorName', 'norsuLogoBase64', 'clinicLogoBase64'))
                    ->setPaper([0, 0, 612, 396], 'landscape') // 5.5"x8.5" in points
                    ->setWarnings(false);
            } elseif ($requestDocument->document_type === 'excuse_slip') {
                $view = 'document_issuances.pdf_excuse_slip';
                $medicalCertificateDoctorName = $this->resolveMedicalCertificateDoctorName($requestDocument);
                $pdf = Pdf::loadView($view, compact('requestDocument', 'medicalCertificateDoctorName', 'norsuLogoBase64', 'clinicLogoBase64'))
                    ->setPaper([0, 0, 612, 396], 'landscape')
                    ->setWarnings(false);
            } else {
                $view = 'document_issuances.pdf_consultation_form';
                $pdf = Pdf::loadView($view, compact('requestDocument'))
                    ->setPaper([0, 0, 612, 936], 'portrait') // 8.5"x13"
                    ->setWarnings(false);
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

    public function getLastConsultation(Request $request)
    {
        try {
            $userId = $request->input('user_id');

            if (empty($userId)) {
                return response()->json(['error' => 'User ID is required'], 400);
            }

            // Get the latest consultation for this user
            $lastConsultation = DocumentIssuance::where('user_id', $userId)
                ->where('document_type', 'consultation_form')
                ->orderBy('requested_at', 'desc')
                ->orderBy('id', 'desc')
                ->first();

            if (!$lastConsultation) {
                $patientProfile = Patient::query()
                    ->where('user_id', $userId)
                    ->select('comorbidities', 'allergies', 'admissions_surgeries', 'maintenance')
                    ->first();

                return response()->json([
                    'success' => true,
                    'fallback' => true,
                    'message' => 'No previous consultation found. Loaded patient profile values.',
                    'data' => [
                        'status' => null,
                        'religion' => null,
                        'comorbidities' => $patientProfile->comorbidities ?? null,
                        'allergies' => $patientProfile->allergies ?? null,
                        'admissions_surgeries' => $patientProfile->admissions_surgeries ?? null,
                        'maintenance' => $patientProfile->maintenance ?? null,
                        'pregnancy_status' => null,
                        'lmp_aog' => null,
                        'vital_signs_bp' => null,
                        'vital_signs_pr' => null,
                        'vital_signs_temp' => null,
                        'vital_signs_rr' => null,
                        'vital_signs_o2_sat' => null,
                        'vital_signs_weight' => null,
                        'vital_signs_height' => null,
                    ],
                ]);
            }

            // Return the relevant fields
            return response()->json([
                'success' => true,
                'data' => [
                    'status' => $lastConsultation->status,
                    'religion' => $lastConsultation->religion,
                    'comorbidities' => $lastConsultation->comorbidities,
                    'allergies' => $lastConsultation->allergies,
                    'admissions_surgeries' => $lastConsultation->admissions_surgeries,
                    'maintenance' => $lastConsultation->maintenance,
                    'pregnancy_status' => $lastConsultation->pregnancy_status,
                    'lmp_aog' => $lastConsultation->lmp_aog,
                    'vital_signs_bp' => $lastConsultation->vital_signs_bp,
                    'vital_signs_pr' => $lastConsultation->vital_signs_pr,
                    'vital_signs_temp' => $lastConsultation->vital_signs_temp,
                    'vital_signs_rr' => $lastConsultation->vital_signs_rr,
                    'vital_signs_o2_sat' => $lastConsultation->vital_signs_o2_sat,
                    'vital_signs_weight' => $lastConsultation->vital_signs_weight,
                    'vital_signs_height' => $lastConsultation->vital_signs_height,
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Error in getLastConsultation: ' . $e->getMessage());
            return response()->json(['error' => 'An error occurred while fetching consultation data'], 500);
        }
    }

    public function getLastMedicalCertificate(Request $request)
    {
        try {
            $userId = $request->input('user_id');

            if (empty($userId)) {
                return response()->json(['error' => 'User ID is required'], 400);
            }

            $lastMedicalCertificate = DocumentIssuance::where('user_id', $userId)
                ->where('document_type', 'medical_certificate')
                ->orderBy('requested_at', 'desc')
                ->orderBy('id', 'desc')
                ->first();

            if (!$lastMedicalCertificate) {
                return response()->json([
                    'success' => true,
                    'fallback' => true,
                    'message' => 'No previous medical certificate found for this patient.',
                    'data' => [
                        'complaints_diagnosis' => null,
                        'vital_signs_bp' => null,
                        'vital_signs_pr' => null,
                        'vital_signs_rr' => null,
                        'vital_signs_temp' => null,
                        'vital_signs_height' => null,
                        'vital_signs_weight' => null,
                        'medical_cert_remarks' => null,
                        'request_of' => null,
                    ],
                ]);
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'complaints_diagnosis' => $lastMedicalCertificate->complaints_diagnosis,
                    'vital_signs_bp' => $lastMedicalCertificate->vital_signs_bp,
                    'vital_signs_pr' => $lastMedicalCertificate->vital_signs_pr,
                    'vital_signs_rr' => $lastMedicalCertificate->vital_signs_rr,
                    'vital_signs_temp' => $lastMedicalCertificate->vital_signs_temp,
                    'vital_signs_height' => $lastMedicalCertificate->vital_signs_height,
                    'vital_signs_weight' => $lastMedicalCertificate->vital_signs_weight,
                    'medical_cert_remarks' => $lastMedicalCertificate->medical_cert_remarks,
                    'request_of' => $lastMedicalCertificate->request_of,
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Error in getLastMedicalCertificate: ' . $e->getMessage());
            return response()->json(['error' => 'An error occurred while fetching medical certificate data'], 500);
        }
    }

    private function getAvailableCertificateDoctors()
    {
        return User::query()
            ->where('type', User::DOCTOR)
            ->whereHas('doctor')
            ->with(['doctor:id,user_id,prc_license_number,ptr_number'])
            ->orderBy('first_name')
            ->orderBy('last_name')
            ->get(['id', 'first_name', 'last_name']);
    }

    private function resolveMedicalCertificateDoctorName(DocumentIssuance $requestDocument): ?string
    {
        $docLicNo = trim((string) ($requestDocument->doc_lic_no ?? ''));
        $docPtrNo = trim((string) ($requestDocument->doc_prt_no ?? ''));

        if ($docLicNo !== '') {
            $doctor = Doctor::with(['user:id,first_name,last_name'])
                ->where('prc_license_number', $docLicNo)
                ->first();

            if ($doctor && $doctor->user) {
                return trim($doctor->user->first_name . ' ' . $doctor->user->last_name);
            }
        }

        if ($docPtrNo !== '') {
            $doctor = Doctor::with(['user:id,first_name,last_name'])
                ->where('ptr_number', $docPtrNo)
                ->first();

            if ($doctor && $doctor->user) {
                return trim($doctor->user->first_name . ' ' . $doctor->user->last_name);
            }
        }

        return null;
    }
}
