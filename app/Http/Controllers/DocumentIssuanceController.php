<?php

namespace App\Http\Controllers;

use App\Models\Campus;
use App\Models\College;
use App\Models\Course;
use App\Models\Department;
use App\Models\Diagnose;
use App\Models\Doctor;
use App\Models\ActivityLog;
use App\Models\Office;
use App\Models\Patient;
use App\Models\DocumentIssuance;
use App\Models\PatientType;
use App\Models\Staff;
use App\Models\User;
use App\Models\Vaccination;
use App\Models\YearLevel;
use App\Repositories\PatientRepository;
use App\Services\MedicineInventoryService;
use App\Traits\LogsActivity;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;

class DocumentIssuanceController extends Controller
{
    use LogsActivity;

    private const DOCUMENT_TYPES = ['consultation_form', 'medical_certificate', 'excuse_slip'];

    private const CONSULTATION_IMAGE_RULES = [
        'consultation_images' => 'nullable|array|max:20',
        // "mimes" checks the real file content, and Laravel additionally refuses any
        // .php/.phtml/.phar upload, so only genuine pictures are accepted.
        'consultation_images.*' => 'file|mimes:jpeg,jpg,png,gif,webp|max:5120',
    ];

    private const CONSULTATION_IMAGE_MESSAGES = [
        'consultation_images.max' => 'You can attach at most 20 images at a time.',
        'consultation_images.*.mimes' => 'Consultation images must be JPEG, PNG, GIF or WEBP pictures.',
        'consultation_images.*.max' => 'Each consultation image must be 5MB or smaller.',
        'consultation_images.*.file' => 'A consultation image failed to upload. Please try again.',
    ];

    private MedicineInventoryService $medicineInventoryService;

    /**
     * Files written to the private consultation_images disk during this request, so they
     * can be removed again if the surrounding DB transaction is rolled back.
     */
    private array $imagesStoredThisRequest = [];

    public function __construct(MedicineInventoryService $medicineInventoryService)
    {
        $this->medicineInventoryService = $medicineInventoryService;
    }

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
            $user = \App\Models\User::with(['patient.address', 'patient.patientType'])->find($userId);

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
        $request->validate(array_merge([
            'document_type' => 'required|in:' . implode(',', self::DOCUMENT_TYPES),
            'user_id' => 'nullable|integer',
            'dob' => 'nullable|date|before_or_equal:today',
            'date_of_birth' => 'nullable|date|before_or_equal:today',
            'nursing_incharged' => 'nullable|integer|exists:users,id',
        ], self::CONSULTATION_IMAGE_RULES), array_merge([
            'document_type.required' => 'Please choose the type of document to create.',
            'document_type.in' => 'Unknown document type.',
            'dob.before_or_equal' => 'Date of Birth cannot be in the future.',
            'date_of_birth.before_or_equal' => 'Date of Birth cannot be in the future.',
        ], self::CONSULTATION_IMAGE_MESSAGES));

        // document_creator_id is always the signed-in user; never trust it from the form.
        $data = $request->except(['_token', 'document_creator_id', 'consultation_images']);
        $data = $this->forceOwnNursingInCharge($data);
        $documentType = $request->input('document_type');

        try {
            // Wrap document creation + medicine deduction in ONE transaction so a mid-way
            // failure (e.g. insufficient stock on a later medicine) rolls back the document
            // and all prior deductions instead of leaving partial data. DISP-2.
            DB::beginTransaction();

            if ($documentType === 'medical_certificate' || $documentType === 'excuse_slip') {
                $this->storeMedicalCertificate($data);
            } elseif ($documentType === 'consultation_form') {
                // If no patient is selected, create one from the consultation form input.
                $this->prepareConsultationPatient($data);
                $this->storeConsultationForm($data);
            }

            DB::commit();

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
        } catch (\Throwable $e) {
            DB::rollBack();
            $this->discardImagesStoredThisRequest();

            // Log the error for debugging
            Log::error('Error in store method: ' . $e->getMessage());

            // Keep what the user typed so a rejected consultation does not have to be re-entered.
            return redirect()->back()
                ->withInput($request->except(['_token', 'consultation_images']))
                ->with('error', $this->userFacingErrorMessage($e, 'An error occurred while creating the request document.'));
        }
    }

    /**
     * A staff/nurse account can only record itself as the nurse in charge (the form shows
     * their own name read-only); admins and doctors pick from the list.
     */
    private function forceOwnNursingInCharge(array $data): array
    {
        if (array_key_exists('nursing_incharged', $data) && (isRole('staff') || isRole('nurse'))) {
            $data['nursing_incharged'] = auth()->id();
        }

        return $data;
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
        $user = !empty($data['user_id']) ? User::with(['campus', 'college', 'course', 'yearLevel', 'patient.patientType'])->find($data['user_id']) : null;

        // Use request data for campus/college/course/year_level if provided (allows manual override)
        $data['campus'] = $data['campus'] ?? ($user ? ($user->campus->campus_name ?? 'Unknown Campus') : 'N/A');
        $data['college'] = $data['college'] ?? ($user ? ($user->college->college_name ?? 'Unknown College') : 'N/A');

        $data['dob'] = $this->normalizeNullableString(
            $data['date_of_birth'] ?? ($data['dob'] ?? ($user?->dob ?? null))
        );
        $data['age'] = $this->resolveAgeFromDateOfBirth($data['dob']);

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

        // Insert the data into the database
        $requestDocument = DocumentIssuance::create([
            'document_type' => $data['document_type'],
            'document_creator_id' => auth()->id(),
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
        $user = !empty($data['user_id']) ? User::with(['campus', 'college', 'course', 'yearLevel', 'patient.patientType'])->find($data['user_id']) : null;

        // Map related names for numeric fields using their IDs
        $data['campus'] = $data['campus'] ?? (Campus::find($data['campus_id'] ?? null)?->campus_name ?? ($user?->campus?->campus_name ?? 'Unknown Campus'));
        $data['college'] = $data['college'] ?? (College::find($data['college_id'] ?? null)?->college_name ?? ($user?->college?->college_name ?? 'Unknown College'));
        $data['course'] = $data['course'] ?? (Course::find($data['course_id'] ?? null)?->course_name ?? ($user?->course?->course_name ?? 'Unknown Course'));
        $data['year_level'] = $data['year_level'] ?? (YearLevel::find($data['year_level_id'] ?? null)?->year_level_name ?? ($user?->yearLevel?->year_level_name ?? 'Unknown Year Level'));
        $data['covid_vaccination'] = $data['covid_vaccination'] ?? (Vaccination::find($data['vaccination_id'] ?? null)?->vaccination_status ?? ($user?->vaccination?->vaccination_status ?? 'Unknown Vaccination'));

        // Handle comorbidities - accept custom input or predefined values
        $data['comorbidities_value'] = $data['comorbidities_custom'] ?? 'None';

        $data['date_of_birth'] = $this->normalizeNullableString(
            $data['date_of_birth'] ?? ($user?->dob ?? null)
        );
        $data['age'] = $this->resolveAgeFromDateOfBirth($data['date_of_birth']);

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

        $data['informant'] = $this->resolveConsultationInformantLabel(
            $data['informant'] ?? null,
            $this->normalizeNullableInt($data['year_level_id'] ?? null),
            $user
        );

        $requestDocument = DocumentIssuance::create([
            'document_type' => 'consultation_form',
            'document_creator_id' => auth()->id(),
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
            'informant' => $data['informant'],
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

        // Consultation photos go to the private consultation_images disk (see storeConsultationImages()).
        $uploadedImages = $this->storeConsultationImages($requestDocument);
        if (! empty($uploadedImages)) {
            $requestDocument->consultation_images = $uploadedImages;
            $requestDocument->save();
        }

        // Handle medicine deduction
        $this->handleMedicineDeduction($requestDocument, $data);

        // Log consultation form creation activity
        self::logConsultationCreation($requestDocument);

        return $requestDocument;
    }

    /**
     * Ensure consultation submissions are linked to a patient user.
     * If no user is selected, create a new patient profile from form input.
     */
    private function prepareConsultationPatient(array &$data): void
    {
        $data['informant'] = $this->resolveConsultationInformantLabel(
            $data['informant'] ?? null,
            $this->normalizeNullableInt($data['year_level_id'] ?? null)
        );

        $userId = $this->normalizeNullableInt($data['user_id'] ?? null);

        if ($userId !== null) {
            $user = User::query()
                ->with('patient.patientType')
                ->where('id', $userId)
                ->where('type', User::PATIENT)
                ->first();

            if (! $user) {
                throw new \RuntimeException('Selected patient is invalid. Please choose a valid patient from search.');
            }

            $data['informant'] = $this->resolveConsultationInformantLabel(
                $data['informant'] ?? null,
                $this->normalizeNullableInt($data['year_level_id'] ?? null),
                $user
            );

            $this->syncPatientProfileFromConsultationData($user, $data);
            $data['user_id'] = $user->id;

            return;
        }

        $matchedUser = $this->findMatchingPatientForConsultationData($data);
        if ($matchedUser) {
            // Auto-linked (not picked by the user): never rewrite the existing patient's
            // identity (name, date of birth, sex) from a walk-in form.
            $this->syncPatientProfileFromConsultationData($matchedUser, $data, false);
            $data['user_id'] = $matchedUser->id;

            return;
        }

        $createdUser = $this->createPatientFromConsultationData($data);
        $data['user_id'] = $createdUser->id;
    }

    /**
     * Try to find an existing patient using consultation identity fields
     * so repeated walk-in entries don't create duplicate patient accounts.
     */
    private function findMatchingPatientForConsultationData(array $data): ?User
    {
        $incomingName = $this->normalizeNullableString($data['name'] ?? null);
        $dateOfBirth = $this->normalizeNullableString($data['date_of_birth'] ?? null);
        $gender = $this->normalizeGenderForUser($data['gender'] ?? null);

        if ($dateOfBirth === null || $gender === null) {
            return null;
        }

        $incomingAge = $this->normalizeNullableInt($data['age'] ?? null);
        if ($incomingAge === null) {
            try {
                $incomingAge = \Carbon\Carbon::parse($dateOfBirth)->age;
            } catch (\Exception $e) {
                return null;
            }
        }

        if ($incomingAge === null) {
            return null;
        }

        $candidates = User::query()
            ->where('type', User::PATIENT)
            ->whereDate('dob', $dateOfBirth)
            ->where('gender', $gender)
            ->orderByDesc('updated_at')
            ->orderByDesc('id')
            ->get();

        $candidates = $candidates
            ->filter(function (User $candidate) use ($incomingAge) {
                if ($candidate->dob === null) {
                    return false;
                }

                try {
                    return \Carbon\Carbon::parse($candidate->dob)->age === $incomingAge;
                } catch (\Exception $e) {
                    return false;
                }
            })
            ->values();

        if ($candidates->isEmpty()) {
            return null;
        }

        $nameTokens = $this->tokenizeNameForMatching($incomingName);
        if (! $this->passesNameTokenSafetyThreshold($nameTokens)) {
            return null;
        }

        // Automatic linking requires the SAME name, not a similar one: every name word typed
        // on the form must be one of the patient's name words, and the patient's first and
        // last name must both be present (only the middle name may be omitted). Fuzzy /
        // substring matching used to merge different people ("Ana Cruz" -> "Juliana Cruzado").
        $candidates = $candidates
            ->filter(function (User $candidate) use ($nameTokens) {
                return $this->isExactNameMatchForAutoLink($nameTokens, $candidate);
            })
            ->values();

        if ($candidates->isEmpty()) {
            return null;
        }

        if ($candidates->count() > 1) {
            throw new \RuntimeException(
                'More than one existing patient has this name, date of birth and sex. '
                . 'Please select the correct patient with the patient search box before saving.'
            );
        }

        return $candidates->first();
    }

    private function isExactNameMatchForAutoLink(array $nameTokens, User $candidate): bool
    {
        $incomingTokens = $this->filterMeaningfulNameTokens($nameTokens);
        $candidateTokens = $this->filterMeaningfulNameTokens(
            $this->tokenizeNameForMatching($this->buildFullNameForMatching($candidate))
        );
        $requiredTokens = $this->filterMeaningfulNameTokens(array_merge(
            $this->tokenizeNameForMatching($candidate->first_name),
            $this->tokenizeNameForMatching($candidate->last_name)
        ));

        if (count($incomingTokens) < 2 || empty($requiredTokens)) {
            return false;
        }

        return empty(array_diff($incomingTokens, $candidateTokens))
            && empty(array_diff($requiredTokens, $incomingTokens));
    }

    private function tokenizeNameForMatching(?string $name): array
    {
        $normalizedName = $this->normalizeNullableString($name);

        if ($normalizedName === null) {
            return [];
        }

        $normalizedName = mb_strtolower(preg_replace('/\s+/u', ' ', $normalizedName));

        // Dots are separators so a middle initial ("M.") becomes a one-letter token, which
        // filterMeaningfulNameTokens() ignores.
        $tokens = preg_split('/[\s,.]+/u', $normalizedName, -1, PREG_SPLIT_NO_EMPTY) ?: [];

        return array_values(array_unique(array_map(function ($token) {
            return trim((string) $token);
        }, $tokens)));
    }

    private function passesNameTokenSafetyThreshold(array $nameTokens): bool
    {
        return count($this->filterMeaningfulNameTokens($nameTokens)) >= 2;
    }

    private function buildFullNameForMatching(User $user): string
    {
        $fullName = trim(implode(' ', array_filter([
            $this->normalizeNullableString($user->first_name),
            $this->normalizeNullableString($user->middle_name),
            $this->normalizeNullableString($user->last_name),
        ])));

        return mb_strtolower(preg_replace('/\s+/u', ' ', $fullName));
    }

    private function filterMeaningfulNameTokens(array $nameTokens): array
    {
        $noiseTokens = ['de', 'del', 'dela', 'la', 'jr', 'sr', 'ii', 'iii', 'iv'];

        $tokens = array_values(array_filter(array_map(function ($token) {
            return mb_strtolower(trim((string) $token));
        }, $nameTokens), function ($token) use ($noiseTokens) {
            if ($token === '') {
                return false;
            }

            if (in_array($token, $noiseTokens, true)) {
                return false;
            }

            return mb_strlen($token) >= 2;
        }));

        return array_values(array_unique($tokens));
    }

    /**
     * Create a patient account/profile from consultation form data.
     */
    private function createPatientFromConsultationData(array $data): User
    {
        [$firstName, $middleName, $lastName] = $this->splitFullName($data['name'] ?? null);

        if ($firstName === '' || $lastName === '') {
            throw new \RuntimeException('Patient name is required to create a patient account.');
        }

        $dateOfBirth = $this->normalizeNullableString($data['date_of_birth'] ?? null);
        if ($dateOfBirth === null) {
            throw new \RuntimeException('Date of birth is required to create a patient account.');
        }

        $gender = $this->normalizeGenderForUser($data['gender'] ?? null);
        if ($gender === null) {
            throw new \RuntimeException('Gender is required to create a patient account.');
        }

        [$emergencyName, $emergencyNumber, $emergencyRelationship] = $this->extractEmergencyContactParts($data['emergency_contact'] ?? null);

        $user = User::create([
            'first_name' => $firstName,
            'middle_name' => $middleName,
            'last_name' => $lastName,
            'contact' => $this->normalizeNullableString($data['patient_contact'] ?? null),
            'emergency_contact_name' => $emergencyName,
            'emergency_contact_no' => $emergencyNumber,
            'emergency_relationship' => $emergencyRelationship,
            'dob' => $dateOfBirth,
            'gender' => $gender,
            'status' => true,
            'language' => 'en',
            'type' => User::PATIENT,
            'password' => Hash::make('123456'),
            'email_verified_at' => now()->setTimezone('Asia/Manila')->toDateTimeString(),
            'campus_id' => $this->normalizeNullableInt($data['campus_id'] ?? null),
            'college_id' => $this->normalizeNullableInt($data['college_id'] ?? null),
            'course_id' => $this->normalizeNullableInt($data['course_id'] ?? null),
            'year_level_id' => $this->normalizeNullableInt($data['year_level_id'] ?? null),
            'department_id' => $this->normalizeNullableInt($data['department_id'] ?? null),
            'office_id' => $this->normalizeNullableInt($data['office_id'] ?? null),
            'vaccination_id' => $this->normalizeNullableInt($data['vaccination_id'] ?? null),
        ]);

        if (! $user->hasRole('patient')) {
            $user->assignRole('patient');
        }

        $this->syncPatientProfileFromConsultationData($user, $data);

        return $user;
    }

    /**
     * Sync consultation form fields into the linked patient profile.
     */
    private function syncPatientProfileFromConsultationData(User $user, array $data, bool $overwriteIdentity = true): void
    {
        [$firstName, $middleName, $lastName] = $this->splitFullName($data['name'] ?? null);
        [$emergencyName, $emergencyNumber, $emergencyRelationship] = $this->extractEmergencyContactParts($data['emergency_contact'] ?? null);
        $hasIncomingName = $this->normalizeNullableString($data['name'] ?? null) !== null;

        $gender = $this->normalizeGenderForUser($data['gender'] ?? null);

        $userUpdates = [];

        if ($overwriteIdentity && $firstName !== '') {
            $userUpdates['first_name'] = $firstName;
        }

        if ($overwriteIdentity && $hasIncomingName) {
            $userUpdates['middle_name'] = $middleName;
        }

        if ($overwriteIdentity && $lastName !== '') {
            $userUpdates['last_name'] = $lastName;
        }

        if (($contact = $this->normalizeNullableString($data['patient_contact'] ?? null)) !== null) {
            $userUpdates['contact'] = $contact;
        }

        if ($overwriteIdentity && ($dob = $this->normalizeNullableString($data['date_of_birth'] ?? null)) !== null) {
            $userUpdates['dob'] = $dob;
        }

        if ($overwriteIdentity && $gender !== null) {
            $userUpdates['gender'] = $gender;
        }

        if ($emergencyName !== null) {
            $userUpdates['emergency_contact_name'] = $emergencyName;
        }

        if ($emergencyNumber !== null) {
            $userUpdates['emergency_contact_no'] = $emergencyNumber;
        }

        if ($emergencyRelationship !== null) {
            $userUpdates['emergency_relationship'] = $emergencyRelationship;
        }

        if (($campusId = $this->normalizeNullableInt($data['campus_id'] ?? null)) !== null) {
            $userUpdates['campus_id'] = $campusId;
        }

        if (($collegeId = $this->normalizeNullableInt($data['college_id'] ?? null)) !== null) {
            $userUpdates['college_id'] = $collegeId;
        }

        if (($courseId = $this->normalizeNullableInt($data['course_id'] ?? null)) !== null) {
            $userUpdates['course_id'] = $courseId;
        }

        if (($yearLevelId = $this->normalizeNullableInt($data['year_level_id'] ?? null)) !== null) {
            $userUpdates['year_level_id'] = $yearLevelId;
        }

        if (($departmentId = $this->normalizeNullableInt($data['department_id'] ?? null)) !== null) {
            $userUpdates['department_id'] = $departmentId;
        }

        if (($officeId = $this->normalizeNullableInt($data['office_id'] ?? null)) !== null) {
            $userUpdates['office_id'] = $officeId;
        }

        if (($vaccinationId = $this->normalizeNullableInt($data['vaccination_id'] ?? null)) !== null) {
            $userUpdates['vaccination_id'] = $vaccinationId;
        }

        if (! empty($userUpdates)) {
            $userUpdates['type'] = User::PATIENT;
            $user->update($userUpdates);
        }

        $data['informant'] = $this->resolveConsultationInformantLabel(
            $data['informant'] ?? null,
            $this->normalizeNullableInt($data['year_level_id'] ?? null),
            $user
        );

        $patient = $user->patient;
        $patientTypeId = $this->resolvePatientTypeId(
            $this->normalizeNullableInt($data['year_level_id'] ?? null),
            $data['informant'] ?? null,
            $patient === null
        );

        $covidVaccination = null;
        if (isset($vaccinationId) && $vaccinationId !== null) {
            $covidVaccination = Vaccination::query()->whereKey($vaccinationId)->value('vaccination_status');
        }

        // Clinical history on the patient master record (allergies, comorbidities, past
        // admissions, maintenance medication) is only ever ADDED/CHANGED from a consultation,
        // never cleared: a blank field on the form means "not re-entered this visit", not
        // "the patient no longer has this allergy". Clearing it silently removed drug-allergy
        // information that later prescribers rely on.
        $patientPayload = array_filter([
            'allergies' => $this->normalizeNullableString($data['allergies'] ?? null),
            'comorbidities' => $this->normalizeComorbiditiesValue($data),
            'admissions_surgeries' => $this->normalizeNullableString($data['admissions_surgeries'] ?? null),
            'maintenance' => $this->normalizeNullableString($data['maintenance'] ?? null),
        ], function ($value) {
            return $value !== null;
        });

        if ($patientTypeId !== null) {
            $patientPayload['patient_type_id'] = $patientTypeId;
        }

        $resolvedCovidVaccination = $covidVaccination ?? $this->normalizeNullableString($data['covid_vaccination'] ?? null);
        if ($resolvedCovidVaccination !== null) {
            $patientPayload['covid_vaccination'] = $resolvedCovidVaccination;
        }

        $syncedImmunizationRecord = $this->buildSyncedImmunizationRecord(
            $patient?->immunization_record,
            $resolvedCovidVaccination
        );

        if ($syncedImmunizationRecord !== null) {
            $patientPayload['immunization_record'] = $syncedImmunizationRecord;
        }

        if (! $patient) {
            $patientUniqueId = $this->normalizeNullableString($data['university_id_number'] ?? null);
            if ($patientUniqueId !== null) {
                $patientUniqueId = strtoupper($patientUniqueId);
            }

            $patient = $user->patient()->create(array_merge($patientPayload, [
                'patient_unique_id' => $patientUniqueId ?: Patient::generatePatientUniqueId(),
            ]));
        } else {
            $patient->update($patientPayload);
        }

        if (($addressText = $this->normalizeNullableString($data['address'] ?? null)) !== null) {
            if ($patient->address) {
                $patient->address->update(['address1' => $addressText]);
            } else {
                $patient->address()->create(['address1' => $addressText]);
            }
        }

        if (! $user->hasRole('patient')) {
            $user->assignRole('patient');
        }
    }

    private function resolvePatientTypeId(?int $yearLevelId, ?string $informant = null, bool $defaultToStudent = false): ?int
    {
        $patientTypeCode = $this->resolvePatientTypeCode($yearLevelId, $informant, $defaultToStudent);

        if ($patientTypeCode === null) {
            return null;
        }

        return PatientType::query()->where('code', $patientTypeCode)->value('id');
    }

    private function resolvePatientTypeCode(?int $yearLevelId, ?string $informant = null, bool $defaultToStudent = false): ?string
    {
        $normalizedInformant = $this->normalizeInformantLabel($informant);

        if ($normalizedInformant !== null) {
            return strtolower($normalizedInformant);
        }

        if ($yearLevelId !== null) {
            if ($yearLevelId === 7) {
                return 'faculty';
            }

            if ($yearLevelId === 8) {
                return 'staff';
            }

            if ($yearLevelId === 9) {
                return 'guest';
            }

            return 'student';
        }

        return $defaultToStudent ? 'student' : null;
    }

    private function resolveConsultationInformantLabel(?string $informant, ?int $yearLevelId = null, ?User $user = null): string
    {
        $normalizedInformant = $this->normalizeInformantLabel($informant);
        $resolvedYearLevelId = $yearLevelId ?? $this->normalizeNullableInt($user?->year_level_id ?? null);

        $patientTypeCodeFromProfile = $this->resolvePatientTypeCodeFromUser($user);
        $informantFromProfile = $this->resolveInformantLabelFromPatientTypeCode($patientTypeCodeFromProfile);

        if ($informantFromProfile !== null) {
            if ($normalizedInformant === null) {
                return $informantFromProfile;
            }

            if (
                strtolower($normalizedInformant) === 'student'
                && strtolower($informantFromProfile) !== 'student'
            ) {
                return $informantFromProfile;
            }
        }

        if ($normalizedInformant !== null) {
            return $normalizedInformant;
        }

        $resolvedPatientTypeCode = $this->resolvePatientTypeCode($resolvedYearLevelId, null, true);

        return $this->resolveInformantLabelFromPatientTypeCode($resolvedPatientTypeCode) ?? 'Student';
    }

    private function resolvePatientTypeCodeFromUser(?User $user): ?string
    {
        if (! $user) {
            return null;
        }

        $user->loadMissing('patient.patientType');

        $patientTypeCode = $this->normalizeNullableString($user->patient?->patientType?->code);
        return $patientTypeCode !== null ? strtolower($patientTypeCode) : null;
    }

    private function resolveInformantLabelFromPatientTypeCode(?string $patientTypeCode): ?string
    {
        return $this->normalizeInformantLabel($patientTypeCode);
    }

    private function normalizeInformantLabel(?string $informant): ?string
    {
        $normalizedInformant = $this->normalizeNullableString($informant);

        if ($normalizedInformant === null) {
            return null;
        }

        $informantKey = strtolower(trim(preg_replace('/\s+/', ' ', $normalizedInformant)));

        $informantMap = [
            'student' => 'Student',
            'staff' => 'Staff',
            'employee' => 'Staff',
            'faculty' => 'Faculty',
            'guest' => 'Guest',
            'visitor' => 'Guest',
        ];

        return $informantMap[$informantKey] ?? null;
    }

    private function splitFullName($fullName): array
    {
        $normalized = preg_replace('/\s+/', ' ', trim((string) $fullName));

        if ($normalized === '') {
            return ['', null, ''];
        }

        $parts = explode(' ', $normalized);

        if (count($parts) === 1) {
            return [$parts[0], null, $parts[0]];
        }

        $firstName = array_shift($parts);
        $lastName = array_pop($parts);
        $middleName = ! empty($parts) ? implode(' ', $parts) : null;

        return [$firstName, $middleName, $lastName];
    }

    private function extractEmergencyContactParts($value): array
    {
        $normalized = $this->normalizeNullableString($value);

        if ($normalized === null) {
            return [null, null, null];
        }

        $name = null;
        $number = null;
        $relationship = null;

        if (preg_match('/^(.+?)(?:\s*\/\s*([^\(]+))?(?:\s*\(([^\)]+)\))?$/', $normalized, $matches)) {
            $name = $this->normalizeNullableString($matches[1] ?? null);
            $number = $this->normalizeNullableString($matches[2] ?? null);
            $relationship = $this->normalizeNullableString($matches[3] ?? null);
        }

        return [$name, $number, $relationship];
    }

    private function normalizeGenderForUser($gender): ?int
    {
        if ($gender === null || $gender === '') {
            return null;
        }

        if (is_numeric($gender)) {
            $normalized = (int) $gender;

            return in_array($normalized, [User::MALE, User::FEMALE], true) ? $normalized : null;
        }

        $normalized = strtolower(trim((string) $gender));

        if (in_array($normalized, ['male', 'm'], true)) {
            return User::MALE;
        }

        if (in_array($normalized, ['female', 'f'], true)) {
            return User::FEMALE;
        }

        return null;
    }

    private function normalizeComorbiditiesValue(array $data): ?string
    {
        $value = $data['comorbidities_custom'] ?? ($data['comorbidities'] ?? null);

        if (is_array($value)) {
            $value = implode(', ', array_filter(array_map(function ($item) {
                return trim((string) $item);
            }, $value)));
        }

        return $this->normalizeNullableString($value);
    }

    private function buildSyncedImmunizationRecord(?string $currentRecord, ?string $covidVaccination): ?string
    {
        $normalizedRecord = $this->normalizeNullableString($currentRecord);
        $normalizedVaccination = $this->normalizeNullableString($covidVaccination);

        if ($normalizedVaccination === null) {
            return $normalizedRecord;
        }

        if (in_array(strtolower($normalizedVaccination), ['unknown vaccination', 'unknown', 'n/a', 'na'], true)) {
            return $normalizedRecord;
        }

        $covidLine = 'COVID-19 Vaccination Status: ' . $normalizedVaccination;

        $existingLines = preg_split('/\R+/', (string) ($normalizedRecord ?? '')) ?: [];
        $existingLines = array_values(array_filter(array_map(function ($line) {
            return trim((string) $line);
        }, $existingLines), function ($line) {
            return $line !== '';
        }));

        $updatedLines = [];
        $hasCovidLine = false;

        foreach ($existingLines as $line) {
            if (preg_match('/^covid(?:-19)?\s+vaccination\s+status\s*:/i', $line)) {
                if (! $hasCovidLine) {
                    $updatedLines[] = $covidLine;
                    $hasCovidLine = true;
                }

                continue;
            }

            $updatedLines[] = $line;
        }

        if (! $hasCovidLine) {
            $updatedLines[] = $covidLine;
        }

        return implode(PHP_EOL, $updatedLines);
    }

    private function normalizeNullableInt($value): ?int
    {
        if ($value === null) {
            return null;
        }

        if (is_string($value)) {
            $value = trim($value);
        }

        if ($value === '') {
            return null;
        }

        return is_numeric($value) ? (int) $value : null;
    }

    private function normalizeNullableString($value): ?string
    {
        if ($value === null) {
            return null;
        }

        $value = trim((string) $value);

        return $value === '' ? null : $value;
    }

    private function resolveAgeFromDateOfBirth(?string $dateOfBirth, int $fallbackAge = 0): int
    {
        $normalizedDate = $this->normalizeNullableString($dateOfBirth);
        $resolvedFallbackAge = max(0, $fallbackAge);

        if ($normalizedDate === null) {
            return $resolvedFallbackAge;
        }

        try {
            return \Carbon\Carbon::parse($normalizedDate)->age;
        } catch (\Exception $e) {
            return $resolvedFallbackAge;
        }
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
        $nursingStaff = User::where('type', User::STAFF)->orderBy('first_name')->orderBy('last_name')->get();
        $availableDoctors = $this->getAvailableCertificateDoctors();

        // Get the user data associated with this request document
        $user = User::find($requestDocument->user_id);

        // Load existing consultation medicines with medicine details
        $existingMedicines = $requestDocument->consultationMedicines()->with('medicine')->get();

        $latestStaffOverrideLog = ActivityLog::query()
            ->where('action', 'consultation_assessment_plan_override')
            ->where('subject_type', 'RequestDocuments')
            ->where('subject_id', $requestDocument->id)
            ->latest('updated_at')
            ->first();

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
            'existingMedicines',
            'latestStaffOverrideLog'
        ));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, DocumentIssuance $document_issuance)
    {
        $requestDocument = $document_issuance;

        $request->validate(array_merge([
            'dob' => 'nullable|date|before_or_equal:today',
            'date_of_birth' => 'nullable|date|before_or_equal:today',
            'nursing_incharged' => 'nullable|integer|exists:users,id',
        ], self::CONSULTATION_IMAGE_RULES), array_merge([
            'dob.before_or_equal' => 'Date of Birth cannot be in the future.',
            'date_of_birth.before_or_equal' => 'Date of Birth cannot be in the future.',
        ], self::CONSULTATION_IMAGE_MESSAGES));

        $data = $request->except(['_token', '_method', 'document_type', 'document_creator_id', 'consultation_images']);
        $data = $this->forceOwnNursingInCharge($data);

        // The document type is fixed at creation. Never take it from the request: the staff
        // module check (EnsureStaffModuleAccess) authorises against the stored type, so a
        // submitted type must not be able to route the update to a different handler.
        $documentType = $requestDocument->document_type;

        try {
            // Wrap document update + medicine restore/re-deduct in ONE transaction so a
            // mid-way failure rolls back everything instead of leaving partial stock
            // adjustments and half-updated records. DISP-2.
            DB::beginTransaction();

            if ($documentType === 'medical_certificate' || $documentType === 'excuse_slip') {
                $this->updateMedicalCertificate($requestDocument, $data);
            } elseif ($documentType === 'consultation_form') {
                $this->updateConsultationForm($requestDocument, $data);
            }

            DB::commit();

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
        } catch (\Throwable $e) {
            DB::rollBack();
            $this->discardImagesStoredThisRequest();
            Log::error('Error in update method: ' . $e->getMessage());
            return redirect()->back()
                ->withInput($request->except(['_token', '_method', 'consultation_images']))
                ->with('error', $this->userFacingErrorMessage($e, 'An error occurred while updating the request document.'));
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
                $data['date_of_birth'] = $this->normalizeNullableString(
                    $data['date_of_birth'] ?? ($user->dob ?? $requestDocument->date_of_birth)
                );

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
                $data['date_of_birth'] = $this->normalizeNullableString(
                    $data['date_of_birth'] ?? $requestDocument->date_of_birth
                );
            }
        } else {
            $data['campus'] = $requestDocument->campus;
            $data['college'] = $requestDocument->college;
            $data['course'] = $requestDocument->course;
            $data['year_level'] = $requestDocument->year_level;
            $data['date_of_birth'] = $this->normalizeNullableString(
                $data['date_of_birth'] ?? $requestDocument->date_of_birth
            );
        }

        $data['age'] = $this->resolveAgeFromDateOfBirth(
            $data['date_of_birth'] ?? null,
            (int) $requestDocument->age
        );

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
        $user = !empty($data['user_id']) ? User::with(['campus', 'college', 'course', 'yearLevel', 'patient.patientType'])->find($data['user_id']) : null;

        // Map related names for numeric fields using their IDs
        $data['campus'] = isset($data['campus_id']) ? (Campus::find($data['campus_id'])->campus_name ?? $requestDocument->campus) : $requestDocument->campus;
        $data['college'] = isset($data['college_id']) ? (College::find($data['college_id'])->college_name ?? $requestDocument->college) : $requestDocument->college;
        $data['course'] = isset($data['course_id']) ? (Course::find($data['course_id'])->course_name ?? $requestDocument->course) : $requestDocument->course;
        $data['year_level'] = isset($data['year_level_id']) ? (YearLevel::find($data['year_level_id'])->year_level_name ?? $requestDocument->year_level) : $requestDocument->year_level;
        $data['covid_vaccination'] = isset($data['vaccination_id']) ? (Vaccination::find($data['vaccination_id'])->vaccination_status ?? $requestDocument->covid_vaccination) : $requestDocument->covid_vaccination;

        $data['date_of_birth'] = $this->normalizeNullableString(
            $data['date_of_birth'] ?? ($user?->dob ?? $requestDocument->date_of_birth)
        );
        $data['age'] = $this->resolveAgeFromDateOfBirth(
            $data['date_of_birth'],
            (int) $requestDocument->age
        );
        $data['informant'] = $this->resolveConsultationInformantLabel(
            $data['informant'] ?? $requestDocument->informant,
            $this->normalizeNullableInt($data['year_level_id'] ?? null),
            $user
        );

        $canEditAssessmentPlanDirectly = $this->canCurrentUserEditAssessmentPlanDirectly();
        $staffOverrideActive = $this->shouldAllowStaffAssessmentPlanOverride($data);

        $originalAssessment = $requestDocument->assessment;
        $originalPlan = $requestDocument->plan;

        $incomingAssessment = array_key_exists('assessment', $data)
            ? $this->normalizeNullableString($data['assessment'])
            : $requestDocument->assessment;
        $incomingPlan = array_key_exists('plan', $data)
            ? $this->normalizeNullableString($data['plan'])
            : $requestDocument->plan;

        $resolvedAssessment = ($canEditAssessmentPlanDirectly || $staffOverrideActive)
            ? $incomingAssessment
            : $requestDocument->assessment;
        $resolvedPlan = ($canEditAssessmentPlanDirectly || $staffOverrideActive)
            ? $incomingPlan
            : $requestDocument->plan;

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
            'informant' => $data['informant'],
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
            'assessment' => $resolvedAssessment,
            'plan' => $resolvedPlan,
            'consult_mode' => $data['consult_mode'] ?? $requestDocument->consult_mode,
            'nursing_intervention' => $data['nursing_intervention'] ?? $requestDocument->nursing_intervention,
            'nursing_incharged_id' => $data['nursing_incharge_id'],
        ]);

        // Handle image updates
        $this->handleImageUpdates($requestDocument, $data);

        // Handle medicine updates (restore removed, deduct newly added)
        $this->handleMedicineUpdates($requestDocument, $data);

        $freshRequestDocument = $requestDocument->fresh();

        if ($staffOverrideActive) {
            $this->logStaffAssessmentPlanOverride(
                $freshRequestDocument,
                $originalAssessment,
                $resolvedAssessment,
                $originalPlan,
                $resolvedPlan
            );
        }

        // Log consultation form update (will update existing log instead of creating new)
        self::logConsultationCreation($freshRequestDocument);
    }

    private function canCurrentUserEditAssessmentPlanDirectly(): bool
    {
        return isRole('doctor') || isRole('clinic_admin');
    }

    private function canCurrentUserRequestAssessmentPlanOverride(): bool
    {
        return isRole('staff') || isRole('nurse');
    }

    private function shouldAllowStaffAssessmentPlanOverride(array $data): bool
    {
        if (! $this->canCurrentUserRequestAssessmentPlanOverride()) {
            return false;
        }

        return $this->normalizeBooleanInput($data['staff_doctor_override'] ?? null);
    }

    private function normalizeBooleanInput($value): bool
    {
        if (is_bool($value)) {
            return $value;
        }

        if ($value === null) {
            return false;
        }

        $normalized = strtolower(trim((string) $value));

        return in_array($normalized, ['1', 'true', 'yes', 'on'], true);
    }

    private function logStaffAssessmentPlanOverride(
        DocumentIssuance $requestDocument,
        $previousAssessment,
        $updatedAssessment,
        $previousPlan,
        $updatedPlan
    ): void {
        if (! $this->canCurrentUserRequestAssessmentPlanOverride()) {
            return;
        }

        $user = auth()->user();
        if (! $user) {
            return;
        }

        $normalize = function ($value): ?string {
            return $this->normalizeNullableString($value);
        };

        $changedFields = [];

        if ($normalize($previousAssessment) !== $normalize($updatedAssessment)) {
            $changedFields[] = 'assessment';
        }

        if ($normalize($previousPlan) !== $normalize($updatedPlan)) {
            $changedFields[] = 'plan';
        }

        if (empty($changedFields)) {
            return;
        }

        $now = now()->setTimezone('Asia/Manila');

        self::logActivity(
            'consultation_assessment_plan_override',
            'Staff override updated ' . implode(' and ', $changedFields) . " for consultation: {$requestDocument->name}",
            [
                'patient_name' => $requestDocument->name,
                'patient_age' => $requestDocument->age,
                'patient_gender' => $requestDocument->gender,
                'college' => $requestDocument->college,
                'address' => $requestDocument->address,
                'contact_number' => $requestDocument->patient_contact,
                'complaints' => $requestDocument->complaints,
                'diagnosis' => $requestDocument->assessment,
                'informant' => $requestDocument->informant,
                'consult_mode' => $requestDocument->consult_mode,
                'course' => $requestDocument->course,
                'year_level' => $requestDocument->year_level,
                'subject_type' => 'RequestDocuments',
                'subject_id' => $requestDocument->id,
                'date' => $requestDocument->requested_at ?? $now->toDateString(),
                'properties' => [
                    'staff_name' => $user->full_name ?? $user->name ?? 'Staff',
                    'changed_at' => $now->toDateTimeString(),
                    'changed_fields' => $changedFields,
                ],
            ]
        );
    }

    /**
     * Handle image uploads and removals for consultation form updates
     */
    private function handleImageUpdates(DocumentIssuance $requestDocument, array $data)
    {
        $existingImages = $requestDocument->consultationImageList();
        $removedImages = [];

        // Handle removed images (indices refer to the list rendered on the edit page)
        if (! empty($data['removed_images'])) {
            $removedIndices = json_decode((string) $data['removed_images'], true);

            if (is_array($removedIndices)) {
                foreach ($removedIndices as $index) {
                    if (is_numeric($index) && isset($existingImages[(int) $index])) {
                        $removedImages[] = $existingImages[(int) $index];
                        unset($existingImages[(int) $index]);
                    }
                }

                $existingImages = array_values($existingImages);
            }
        }

        foreach ($this->storeConsultationImages($requestDocument) as $uploadedImage) {
            $existingImages[] = $uploadedImage;
        }

        // Assign the array directly: the model's "array" cast does the JSON encoding.
        $requestDocument->consultation_images = ! empty($existingImages) ? $existingImages : null;
        $requestDocument->save();

        // Only delete removed files once the update is committed, so a rollback never leaves
        // the record pointing at files that no longer exist.
        if (! empty($removedImages)) {
            DB::afterCommit(function () use ($requestDocument, $removedImages) {
                foreach ($removedImages as $removedImage) {
                    $requestDocument->deleteConsultationImageFile($removedImage);
                }
            });
        }
    }

    /**
     * Store the uploaded consultation_images[] files (already validated as real pictures of
     * at most 5MB) on the private consultation_images disk under a random file name.
     *
     * @return array<int, array{disk: string, path: string, name: string, size: int, uploaded_at: string}>
     */
    private function storeConsultationImages(DocumentIssuance $requestDocument): array
    {
        $files = request()->file('consultation_images');
        if (empty($files)) {
            return [];
        }

        $files = is_array($files) ? $files : [$files];
        $storedImages = [];

        foreach ($files as $image) {
            if (! $image instanceof UploadedFile || ! $image->isValid()) {
                continue;
            }

            $fileSize = (int) $image->getSize();
            $storedPath = $image->store((string) $requestDocument->id, 'consultation_images');

            if ($storedPath === false) {
                throw new \RuntimeException('A consultation image could not be saved. Please try again.');
            }

            $this->imagesStoredThisRequest[] = $storedPath;

            $storedImages[] = [
                'disk' => 'consultation_images',
                'path' => $storedPath,
                'name' => $this->sanitizeImageDisplayName($image->getClientOriginalName()),
                'size' => $fileSize,
                'uploaded_at' => now()->toDateTimeString(),
            ];
        }

        return $storedImages;
    }

    /**
     * The original file name is only kept as a label; it is never used as a path.
     */
    private function sanitizeImageDisplayName(?string $clientName): string
    {
        $name = basename(str_replace('\\', '/', (string) $clientName));
        $name = preg_replace('/[\x00-\x1F\x7F]+/u', '', $name) ?? '';
        $name = trim($name);

        return $name === '' ? 'image' : mb_substr($name, 0, 150);
    }

    /**
     * Remove files stored during a request whose transaction was rolled back.
     */
    private function discardImagesStoredThisRequest(): void
    {
        foreach ($this->imagesStoredThisRequest as $storedPath) {
            Storage::disk('consultation_images')->delete($storedPath);
        }

        $this->imagesStoredThisRequest = [];
    }

    /**
     * Stream one consultation image. Images are clinical records, so they are only served
     * through this authenticated route (same role / staff-module checks as viewing the
     * consultation itself), never directly from the web root.
     */
    public function showImage(DocumentIssuance $document_issuance, int $index)
    {
        abort_unless($document_issuance->document_type === 'consultation_form', 404);

        $images = $document_issuance->consultationImageList();
        abort_unless(isset($images[$index]), 404);

        $absolutePath = $document_issuance->consultationImageAbsolutePath($images[$index]);
        abort_if($absolutePath === null, 404);

        $mimeType = (new \finfo(FILEINFO_MIME_TYPE))->file($absolutePath) ?: '';
        abort_unless(in_array($mimeType, ['image/jpeg', 'image/png', 'image/gif', 'image/webp'], true), 404);

        return response()->file($absolutePath, [
            'Content-Type' => $mimeType,
            'X-Content-Type-Options' => 'nosniff',
            'Cache-Control' => 'private, max-age=3600',
        ]);
    }

    /**
     * Handle medicine updates during consultation form edit
     * This method compares existing medicines with new submission and:
     * 1. Restores stock for removed medicines
     * 2. Deducts stock only for newly added medicines
     */
    private function handleMedicineUpdates(DocumentIssuance $requestDocument, array $data)
    {
        $canManagePlanMedicines = $this->canCurrentUserManagePlanMedicines();

        // Get existing medicines from database
        $existingMedicines = $requestDocument->consultationMedicines()->get();

        // Create a map of existing medicines for easy lookup
        // Key format: "medicineId_dosage_usedFor"
        $existingMedicinesMap = [];
        foreach ($existingMedicines as $existingMedicine) {
            if (! $canManagePlanMedicines && $existingMedicine->used_for === 'plan') {
                continue;
            }

            $key = "{$existingMedicine->medicine_id}_{$existingMedicine->dosage}_{$existingMedicine->used_for}";
            $existingMedicinesMap[$key] = $existingMedicine;
        }

        // Create a map of new medicines from form submission
        $newMedicinesMap = [];
        if (isset($data['medicines']) && is_array($data['medicines'])) {
            foreach ($data['medicines'] as $usedFor => $medicines) {
                if (! in_array($usedFor, ['plan', 'nursing'], true)) {
                    continue;
                }

                if ($usedFor === 'plan' && ! $canManagePlanMedicines) {
                    continue;
                }

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
                $this->restoreMedicineStock($existingMedicine, $requestDocument);

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
                            abs($quantityDiff),
                            $requestDocument
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
    private function restoreMedicineStock(\App\Models\ConsultationMedicine $consultationMedicine, ?DocumentIssuance $requestDocument = null)
    {
        $this->restoreMedicineStockAmount(
            (int) $consultationMedicine->medicine_id,
            $consultationMedicine->dosage,
            (int) $consultationMedicine->quantity,
            $requestDocument
        );
    }

    /**
     * Restore a specific amount of medicine stock
     */
    private function restoreMedicineStockAmount($medicineId, $dosage, $quantity, ?DocumentIssuance $requestDocument = null)
    {
        $quantity = (int) $quantity;
        if ($quantity <= 0) {
            return;
        }

        DB::transaction(function () use ($medicineId, $dosage, $quantity, $requestDocument) {
            $medicine = \App\Models\Medicine::find((int) $medicineId);
            if (! $medicine) {
                return;
            }

            $normalizedDosage = trim((string) ($dosage ?? ''));
            $resolvedDosage = $normalizedDosage !== ''
                ? $normalizedDosage
                : trim((string) ($medicine->dosage ?? ''));

            $batchQuery = \App\Models\MedicineBatch::query()
                ->where('medicine_id', (int) $medicineId);

            $batchQuery->where(function ($query) {
                $query->whereNull('expiration_date')
                    ->orWhereDate('expiration_date', '>=', now()->toDateString());
            });

            if ($resolvedDosage !== '') {
                if (strcasecmp($resolvedDosage, 'N/A') === 0) {
                    $batchQuery->where(function ($query) {
                        $query->whereNull('dosage')
                            ->orWhere('dosage', '')
                            ->orWhere('dosage', 'N/A');
                    });
                } else {
                    $batchQuery->where('dosage', $resolvedDosage);
                }
            }

            $targetBatch = $batchQuery
                ->orderByRaw('expiration_date IS NULL')
                ->orderBy('expiration_date')
                ->orderBy('id')
                ->lockForUpdate()
                ->first();

            if (! $targetBatch) {
                $targetBatch = \App\Models\MedicineBatch::create([
                    'medicine_id' => (int) $medicineId,
                    // Random suffix: a timestamp alone collided when two restores ran in the same
                    // second (unique-key failure rolled back the whole edit). M-03.
                    'batch_number' => 'RETURN-' . (int) $medicineId . '-' . now()->format('YmdHis') . '-' . strtoupper(\Illuminate\Support\Str::random(4)),
                    'dosage' => $resolvedDosage !== '' ? $resolvedDosage : null,
                    'quantity' => 0,
                    // Never-expires sentinel (original expiry unknown) so restored consultation
                    // stock stays available instead of vanishing the next day. Matches
                    // MedicineInventoryService::restoreStock. RE-1.
                    'expiration_date' => '2099-12-31',
                    'date_received' => now()->toDateString(),
                    'supplier_name' => 'Consultation stock restoration',
                ]);
            }

            $targetBatch->quantity = (int) $targetBatch->quantity + $quantity;
            $targetBatch->save();

            \App\Models\MedicineTransaction::create([
                'batch_id' => $targetBatch->id,
                'user_id' => auth()->id(),
                'transaction_type' => \App\Models\MedicineTransaction::TYPE_ADJUSTMENT,
                'quantity' => $quantity,
                'balance_after' => (int) $targetBatch->quantity,
                'reference_type' => $requestDocument ? get_class($requestDocument) : null,
                'reference_id' => $requestDocument ? $requestDocument->getKey() : null,
                'remarks' => 'Consultation medicine restoration',
            ]);

            $this->medicineInventoryService->syncMedicineTotals((int) $medicineId);
        });

        cache()->forget('medicine_' . (int) $medicineId);
        cache()->forget('medicines_list');
    }

    /**
     * Deduct medicine stock from inventory
     */
    private function deductMedicineStock($medicineId, $dosage, $quantity, DocumentIssuance $requestDocument)
    {
        $medicine = \App\Models\Medicine::find($medicineId);

        if (! $medicine) {
            throw new \Exception("Medicine not found (ID: {$medicineId})");
        }

        $normalizedDosage = trim((string) ($dosage ?? ''));
        $dosageFilter = $normalizedDosage !== '' ? $normalizedDosage : null;

        $this->medicineInventoryService->deductStockFefo(
            (int) $medicineId,
            (int) $quantity,
            auth()->id(),
            $requestDocument,
            'Consultation #' . $requestDocument->id . ' medicine deduction',
            \App\Models\MedicineTransaction::TYPE_DISPENSE,
            $dosageFilter
        );

        // Clear medicine cache
        cache()->forget('medicine_' . (int) $medicineId);
        cache()->forget('medicines_list');

        // Log medicine usage activity
        self::logMedicineUsage($medicine, $quantity, $requestDocument->name, $requestDocument);
    }

    /**
     * Handle medicine deduction from inventory
     */
    private function handleMedicineDeduction(DocumentIssuance $requestDocument, array $data)
    {
        $canManagePlanMedicines = $this->canCurrentUserManagePlanMedicines();

        if (!isset($data['medicines']) || !is_array($data['medicines'])) {
            return;
        }

        // Process medicines from both Plan and Nursing Intervention
        foreach ($data['medicines'] as $usedFor => $medicines) {
            if (! in_array($usedFor, ['plan', 'nursing'], true)) {
                continue;
            }

            if ($usedFor === 'plan' && ! $canManagePlanMedicines) {
                continue;
            }

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

                if ($quantity <= 0) {
                    continue;
                }

                // Deduct stock via the current FEFO batch inventory service.
                $this->deductMedicineStock($medicineId, $dosage, $quantity, $requestDocument);

                // Record the medicine usage in consultation_medicines table
                \App\Models\ConsultationMedicine::create([
                    'request_document_id' => $requestDocument->id,
                    'medicine_id' => $medicineId,
                    'quantity' => $quantity,
                    'dosage' => $dosage,
                    'used_for' => $usedFor, // 'plan' or 'nursing'
                    'dosage_instructions' => $dosageInstructions,
                ]);

                $medicine = \App\Models\Medicine::find($medicineId);

                // Log the medicine deduction
                Log::info('Medicine deducted from inventory', [
                    'consultation_id' => $requestDocument->id,
                    'medicine_id' => $medicineId,
                    'medicine_name' => $medicine?->name,
                    'dosage' => $dosage,
                    'quantity_used' => $quantity,
                    'remaining_stock' => $medicine?->available_quantity,
                    'used_for' => $usedFor,
                ]);
            }
        }
    }

    /**
     * Only doctors can add/edit medicines under Plan.
     */
    private function canCurrentUserManagePlanMedicines(): bool
    {
        return isRole('doctor');
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

            // Restore deducted medicine stock for consultation forms before deleting;
            // otherwise the cascade-deleted consultation_medicines lose their stock
            // permanently. Wrapped with the delete so a failure rolls back both. DISP-1.
            DB::transaction(function () use ($request_document) {
                if ($request_document->document_type === 'consultation_form') {
                    foreach ($request_document->consultationMedicines()->get() as $consultationMedicine) {
                        $this->restoreMedicineStock($consultationMedicine, $request_document);
                    }
                }

                $request_document->delete();
            });

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
                ->select('id', 'patient_unique_id', 'user_id', 'patient_type_id', 'allergies', 'comorbidities', 'admissions_surgeries', 'maintenance')
                ->addSelect([
                    'latest_consultation_status' => DocumentIssuance::query()
                        ->select('status')
                        ->whereColumn('user_id', 'patients.user_id')
                        ->where('document_type', 'consultation_form')
                        ->orderByDesc('requested_at')
                        ->orderByDesc('id')
                        ->limit(1),
                    'latest_consultation_religion' => DocumentIssuance::query()
                        ->select('religion')
                        ->whereColumn('user_id', 'patients.user_id')
                        ->where('document_type', 'consultation_form')
                        ->orderByDesc('requested_at')
                        ->orderByDesc('id')
                        ->limit(1),
                ])
                ->with([
                    'user' => function ($query) {
                        $query->select(
                            'id',
                            'first_name',
                            'middle_name',
                            'last_name',
                            'dob',
                            'gender',
                            'contact',
                            'campus_id',
                            'college_id',
                            'course_id',
                            'year_level_id',
                            'department_id',
                            'office_id',
                            'vaccination_id',
                            'emergency_contact_name',
                            'emergency_contact_no',
                            'emergency_relationship',
                            'university_id_number',
                            'employee_id'
                        )
                            ->with(['campus', 'college', 'course', 'yearLevel', 'department', 'office', 'vaccination:id,vaccination_status']);
                    },
                    'address' => function ($query) {
                        $query->select('id', 'owner_id', 'owner_type', 'address1', 'country_id', 'state_id', 'city_id', 'barangay_id', 'postal_code')
                            ->with(['barangay:id,name,city_id', 'city:id,name,state_id', 'state:id,name']);
                    },
                    'patientType:id,code,name',
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

    public function exportPdf(DocumentIssuance $document_issuance)
    {
        $id = $document_issuance->id;
        Log::info('exportPdf hit for ID: ' . $id . ' with action: ' . request('action'));
        try {
            // Set a higher time limit for PDF generation if needed
            set_time_limit(120);

            // The model is already fetched by Route Model Binding
            $requestDocument = $document_issuance;
            $medicalCertificateDoctorName = null;

            // Choose the PDF layout based on document type
            if ($requestDocument->document_type === 'medical_certificate') {
                $medicalCertificateDoctorName = $this->resolveMedicalCertificateDoctorName($requestDocument);

                // Direct HTML Print optimization for Medical Certificate
                if (request()->has('action') && request()->get('action') === 'print') {
                    return view('document_issuances.print_medical_certificate', compact('requestDocument', 'medicalCertificateDoctorName'));
                }

                // Optimization: Convert logos to base64 to avoid local HTTP requests or slow file lookups in DomPDF
                $norsuLogoPath = public_path('assets/image/norsu_logo.png');
                $clinicLogoPath = public_path('assets/image/norsu_clinic_logo.png');
                $norsuLogoBase64 = file_exists($norsuLogoPath) ? 'data:image/' . pathinfo($norsuLogoPath, PATHINFO_EXTENSION) . ';base64,' . base64_encode(file_get_contents($norsuLogoPath)) : '';
                $clinicLogoBase64 = file_exists($clinicLogoPath) ? 'data:image/' . pathinfo($clinicLogoPath, PATHINFO_EXTENSION) . ';base64,' . base64_encode(file_get_contents($clinicLogoPath)) : '';

                $view = 'document_issuances.pdf_medical_certificate';
                $pdf = Pdf::loadView($view, compact('requestDocument', 'medicalCertificateDoctorName', 'norsuLogoBase64', 'clinicLogoBase64'))
                    ->setPaper([0, 0, 612, 396], 'landscape') // 5.5"x8.5" in points
                    ->setWarnings(false);
            } elseif ($requestDocument->document_type === 'excuse_slip') {
                $medicalCertificateDoctorName = $this->resolveMedicalCertificateDoctorName($requestDocument);

                // Direct HTML Print optimization for Excuse Slip (No PDF generation, very fast)
                if (request()->has('action') && request()->get('action') === 'print') {
                    return view('document_issuances.print_excuse_slip', compact('requestDocument', 'medicalCertificateDoctorName'));
                }

                // Optimization: Convert logos to base64 for PDF
                $norsuLogoPath = public_path('assets/image/norsu_logo.png');
                $clinicLogoPath = public_path('assets/image/norsu_clinic_logo.png');
                $norsuLogoBase64 = file_exists($norsuLogoPath) ? 'data:image/' . pathinfo($norsuLogoPath, PATHINFO_EXTENSION) . ';base64,' . base64_encode(file_get_contents($norsuLogoPath)) : '';
                $clinicLogoBase64 = file_exists($clinicLogoPath) ? 'data:image/' . pathinfo($clinicLogoPath, PATHINFO_EXTENSION) . ';base64,' . base64_encode(file_get_contents($clinicLogoPath)) : '';

                $view = 'document_issuances.pdf_excuse_slip';
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

            $userContext = User::query()
                ->select('id', 'year_level_id')
                ->with('patient.patientType')
                ->find($userId);

            $historyFields = [
                'informant',
                'status',
                'religion',
                'comorbidities',
                'allergies',
                'admissions_surgeries',
                'maintenance',
                'pregnancy_status',
                'lmp_aog',
                'vital_signs_bp',
                'vital_signs_pr',
                'vital_signs_temp',
                'vital_signs_rr',
                'vital_signs_o2_sat',
                'vital_signs_weight',
                'vital_signs_height',
            ];

            // Get recent consultation history for this user and resolve each field from
            // the most recent non-empty value to avoid losing data when latest row has blanks.
            $recentConsultations = DocumentIssuance::where('user_id', $userId)
                ->where('document_type', 'consultation_form')
                ->orderBy('requested_at', 'desc')
                ->orderBy('id', 'desc')
                ->limit(50)
                ->get(array_merge(['id'], $historyFields));

            $lastConsultation = $recentConsultations->first();

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
                        'informant' => $this->resolveConsultationInformantLabel(
                            null,
                            $this->normalizeNullableInt($userContext?->year_level_id ?? null),
                            $userContext
                        ),
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

            $resolvedHistoryData = [];
            foreach ($historyFields as $field) {
                $resolvedHistoryData[$field] = $this->getMostRecentNonEmptyConsultationValue(
                    $recentConsultations,
                    $field
                );
            }

            // Return the relevant fields
            return response()->json([
                'success' => true,
                'data' => [
                    'informant' => $this->resolveConsultationInformantLabel(
                        $resolvedHistoryData['informant'] ?? null,
                        $this->normalizeNullableInt($userContext?->year_level_id ?? null),
                        $userContext
                    ),
                    'status' => $resolvedHistoryData['status'],
                    'religion' => $resolvedHistoryData['religion'],
                    'comorbidities' => $resolvedHistoryData['comorbidities'],
                    'allergies' => $resolvedHistoryData['allergies'],
                    'admissions_surgeries' => $resolvedHistoryData['admissions_surgeries'],
                    'maintenance' => $resolvedHistoryData['maintenance'],
                    'pregnancy_status' => $resolvedHistoryData['pregnancy_status'],
                    'lmp_aog' => $resolvedHistoryData['lmp_aog'],
                    'vital_signs_bp' => $resolvedHistoryData['vital_signs_bp'],
                    'vital_signs_pr' => $resolvedHistoryData['vital_signs_pr'],
                    'vital_signs_temp' => $resolvedHistoryData['vital_signs_temp'],
                    'vital_signs_rr' => $resolvedHistoryData['vital_signs_rr'],
                    'vital_signs_o2_sat' => $resolvedHistoryData['vital_signs_o2_sat'],
                    'vital_signs_weight' => $resolvedHistoryData['vital_signs_weight'],
                    'vital_signs_height' => $resolvedHistoryData['vital_signs_height'],
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Error in getLastConsultation: ' . $e->getMessage());
            return response()->json(['error' => 'An error occurred while fetching consultation data'], 500);
        }
    }

    private function getMostRecentNonEmptyConsultationValue($consultations, string $field)
    {
        foreach ($consultations as $consultation) {
            if (!isset($consultation->{$field})) {
                continue;
            }

            $value = $consultation->{$field};

            if ($this->hasMeaningfulConsultationValue($value)) {
                return $value;
            }
        }

        return null;
    }

    private function hasMeaningfulConsultationValue($value): bool
    {
        if ($value === null) {
            return false;
        }

        if (is_string($value)) {
            return trim($value) !== '';
        }

        return true;
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
