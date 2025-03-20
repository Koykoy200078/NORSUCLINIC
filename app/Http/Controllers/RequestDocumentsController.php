<?php

namespace App\Http\Controllers;

use App\Models\RequestDocuments;
use App\Models\User;
use App\Repositories\PatientRepository;
use App\Repositories\RequestRepository;
use Illuminate\Http\Request;

class RequestDocumentsController extends Controller
{
    private $requestRepository;

    public function __construct(RequestRepository $requestRepo)
    {
        $this->requestRepository = $requestRepo;
    }

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

        return view('requests.create', compact('data'));
    }

    public function searchUsers(Request $request)
    {
        $search = $request->input('query');

        // Search users by first name or last name
        $users = User::where(function ($query) use ($search) {
            $query->where('first_name', 'LIKE', "%{$search}%")
                ->orWhere('last_name', 'LIKE', "%{$search}%");
        })
            ->orWhere('email', 'LIKE', "%{$search}%")
            ->select('id', 'first_name', 'last_name', 'dob', 'gender', 'contact', 'campus_id', 'college_id', 'course_id', 'year_level_id', 'vaccination_id')
            ->get();

        return response()->json($users);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // $validatedData = $request->validate([
        //     'name' => 'required|string|max:255',
        //     'age' => 'required|integer',
        //     'gender' => 'required|string|max:50',
        //     'status' => 'required|string|max:50',
        //     'date_of_birth' => 'required|date',
        //     'address' => 'required|string|max:255',
        //     'religion' => 'nullable|string|max:50',
        //     'patient_contact' => 'nullable|string|max:50',
        //     'campus' => 'nullable|string|max:50',
        //     'college' => 'nullable|string|max:50',
        //     'course_year' => 'nullable|string|max:50',
        //     'informant' => 'nullable|string|max:50',
        //     'emergency_contact' => 'nullable|string|max:255',
        //     'complaints' => 'nullable|string|max:255',
        //     'covid_vaccination' => 'nullable|string|max:50',
        //     'comorbidities' => 'nullable|string|max:255',
        //     'allergies' => 'nullable|string|max:255',
        //     'admissions_surgeries' => 'nullable|string|max:255',
        //     'maintenance' => 'nullable|string|max:255',
        //     'pregnancy_status' => 'nullable|string|max:50',
        //     'lmp_aog' => 'nullable|string|max:50',
        //     'vital_signs_bp' => 'nullable|string|max:50',
        //     'vital_signs_pr' => 'nullable|string|max:50',
        //     'vital_signs_temp' => 'nullable|string|max:50',
        //     'vital_signs_rr' => 'nullable|string|max:50',
        //     'vital_signs_o2_sat' => 'nullable|string|max:50',
        //     'vital_signs_weight' => 'nullable|string|max:50',
        //     'pertinent_exam' => 'nullable|string|max:255',
        //     'assessment' => 'nullable|string|max:255',
        //     'plan' => 'nullable|string|max:255',
        // ]);

        // RequestDocuments::create($validatedData);
        dd($request->all());

        // return redirect()->back()->with('success', 'Request created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(RequestDocuments $requestDocuments)
    {
        //
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
}
