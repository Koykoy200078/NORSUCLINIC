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
    public function create(PatientRepository $patientRepository)
    {
        $data = $patientRepository->getData();
        $user = auth()->user(); // Get the authenticated user

        return view('requests.create', compact('data', 'user'));
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

            // Return a success response with role-based redirect
            $redirectRoute = isRole('clinic_admin') ? 'request-documents.index' : (isRole('staff') ? 'staff.request-documents.index' : (isRole('doctor') ? 'doctors.request-documents.index' : 'request-documents.index'));

            return redirect()->route($redirectRoute)
                ->with('success', 'Request document created successfully.');
        } catch (\Exception $e) {
            // Log the error for debugging
            Log::error('Error in store method: ' . $e->getMessage());

            // Return an error response
            return redirect()->back()
                ->with('error', 'An error occurred while creating the request document.');
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

        // Retrieve the user and related IDs
        $user = User::with(['campus', 'college', 'course', 'yearLevel'])->find($data['user_id']);
        if (!$user) {
            throw new \Exception('User not found');
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
        $data['comorbidities_id'] = Diagnose::find($data['comorbidities_id'])->diagnoses ?? 'Unknown Comorbidity';

        RequestDocuments::create([
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
            'allergies' => $data['allergies'],
            'admissions_surgeries' => $data['admissions_surgeries'],
            'maintenance' => $data['maintenance'],
            'pregnancy_status' => $data['pregnancy_status'],
            'lmp_aog' => $data['lmp_aog'],
            'vital_signs_bp' => $data['vital_signs_bp'],
            'vital_signs_pr' => $data['vital_signs_pr'],
            'vital_signs_temp' => $data['vital_signs_temp'],
            'vital_signs_rr' => $data['vital_signs_rr'],
            'vital_signs_o2_sat' => $data['vital_signs_o2_sat'],
            'vital_signs_height' => $data['vital_signs_height'],
            'vital_signs_weight' => $data['vital_signs_weight'],
            'pertinent_exam' => $data['pertinent_exam'],
            'assessment' => $data['assessment'],
            'plan' => $data['plan'],
            'consult_mode' => $data['consult_mode'],
            'nursing_intervention' => $data['nursing_intervention'],
            'nursing_incharged_id' => $data['nursing_incharged'],
        ]);
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

            // Return a success response with role-based redirect
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
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(RequestDocuments $requestDocuments)
    {

        //
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

            // Return the PDF as a download
            return $pdf->download('request_document_' . $id . '.pdf');
        } catch (\Exception $e) {
            // Log the error and redirect back with an error message
            Log::error('Error exporting PDF: ' . $e->getMessage());
            return redirect()->back()->with('error', 'An error occurred while exporting the PDF.');
        }
    }
}
