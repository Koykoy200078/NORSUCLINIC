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

            // Return a success response
            return redirect()->route('request-documents.index')
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
    public function edit(RequestDocuments $requestDocuments)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, RequestDocuments $requestDocuments)
    {
        //
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
                ->with(['user:id,first_name,last_name,dob,gender,contact,emergency_contact_name,emergency_contact_no,campus_id,college_id,course_id,year_level_id,vaccination_id', 'address' => function ($query) {
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


            // Pass the data to a Blade view for the PDF
            $pdf = Pdf::loadView('requests.pdf', compact('requestDocument'));

            // Return the PDF as a download
            return $pdf->download('request_document_' . $id . '.pdf');
        } catch (\Exception $e) {
            // Log the error and redirect back with an error message
            Log::error('Error exporting PDF: ' . $e->getMessage());
            return redirect()->back()->with('error', 'An error occurred while exporting the PDF.');
        }
    }
}
