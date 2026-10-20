<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Campus;
use App\Models\College;
use App\Models\Course;
use App\Models\Department;
use App\Models\Diagnose;
use App\Models\Doctor;
use App\Models\DocumentIssuance;
use App\Models\Illness;
use App\Models\Office;
use App\Models\Patient;
use App\Models\ServiceType;
use App\Models\Staff;
use App\Models\User;
use App\Models\Vaccination;
use App\Models\YearLevel;
use App\Repositories\PatientRepository;
use App\Services\ConsultationImageService;
use App\Services\ConsultationMedicineService;
use App\Services\ConsultationPatientService;
use App\Services\DocumentIssuanceNavigationService;
use App\Support\ConsultationInput;
use App\Support\OtherName;
use App\Support\SearchTerm;
use App\Traits\LogsActivity;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

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

    /** The illness / services picks of the ACCOMPLISHMENT REPORT (ids from the clinic's own lists). */
    /**
     * Every free-text box of the consultation form fits the column it is saved in (document_issuances). A longer text
     * used to fail the whole save in strict mode with only "An error occurred while creating the request document".
     * 191 is what a varchar column holds here; the long boxes are `text` columns (a limit well inside 64 KB, since a
     * character can take four bytes).
     */
    private const CONSULTATION_TEXT_RULES = [
        'name' => 'nullable|string|max:191', 'gender' => 'nullable|string|max:191', 'status' => 'nullable|string|max:191',
        'religion' => 'nullable|string|max:191', 'patient_contact' => 'nullable|string|max:191', 'campus' => 'nullable|string|max:191',
        'college' => 'nullable|string|max:191', 'course' => 'nullable|string|max:191', 'year_level' => 'nullable|string|max:191',
        'informant' => 'nullable|string|max:191', 'covid_vaccination' => 'nullable|string|max:191', 'request_of' => 'nullable|string|max:191',
        'doc_lic_no' => 'nullable|string|max:191', 'doc_prt_no' => 'nullable|string|max:191', 'examined_on' => 'nullable|string|max:255',
        'vital_signs_bp' => 'nullable|string|max:191', 'vital_signs_pr' => 'nullable|string|max:191', 'vital_signs_temp' => 'nullable|string|max:191',
        'vital_signs_rr' => 'nullable|string|max:191', 'vital_signs_o2_sat' => 'nullable|string|max:191', 'vital_signs_height' => 'nullable|string|max:191',
        'vital_signs_weight' => 'nullable|string|max:191',
        'address' => 'nullable|max:10000', 'emergency_contact' => 'nullable|max:10000', 'complaints' => 'nullable|max:10000', 'note' => 'nullable|max:10000',
        'allergies' => 'nullable|max:10000', 'admissions_surgeries' => 'nullable|max:10000', 'maintenance' => 'nullable|max:10000',
        'pregnancy_status' => 'nullable|max:10000', 'lmp_aog' => 'nullable|max:10000', 'pertinent_exam' => 'nullable|max:10000',
        'assessment' => 'nullable|max:10000', 'plan' => 'nullable|max:10000', 'nursing_intervention' => 'nullable|max:10000',
        'complaints_diagnosis' => 'nullable|max:10000', 'medical_cert_remarks' => 'nullable|max:10000',
    ];

    /**
     * Values of the consultation / certificate forms that the database would not take, or that would point at nothing:
     * a visit date that exists and is not absurd, one of the two consult modes, and ids that belong to their list.
     * (The age is worked out from the date of birth, whatever is posted.)
     */
    private const CONSULTATION_VALUE_RULES = [
        'requested_at' => 'nullable|date_format:Y-m-d|after:1899-12-31|before_or_equal:+1 year',
        'consult_mode' => 'nullable|in:physical,virtual',
        'campus_id' => 'nullable|integer|exists:campuses,id',
        'college_id' => 'nullable|integer|exists:colleges,id',
        'course_id' => 'nullable|integer|exists:courses,id',
        'year_level_id' => 'nullable|integer|exists:year_levels,id',
        'department_id' => 'nullable|integer|exists:departments,id',
        'office_id' => 'nullable|integer|exists:offices,id',
    ];

    /**
     * The medical certificate and the excuse slip name their boxes differently from the consultation form (the vital
     * signs are `vital_signs_*_2`, the blood pressure is two boxes that are joined with a slash, the excuse slip has a
     * list of subjects with their teachers). Those boxes were saved unchecked: a long text failed the save with a
     * general error, the patient could be a number that points at no account, and the joined pressure could pass the
     * 191 characters of its column.
     */
    private const CERTIFICATE_RULES = [
        'user_id' => 'nullable|integer|exists:users,id',
        'vital_signs_bp_2' => 'nullable|string|max:90', 'vital_signs_bp_22' => 'nullable|string|max:90',
        'vital_signs_pr_2' => 'nullable|string|max:191', 'vital_signs_rr_2' => 'nullable|string|max:191',
        'vital_signs_temp_2' => 'nullable|string|max:191', 'vital_signs_height_2' => 'nullable|string|max:191',
        'vital_signs_weight_2' => 'nullable|string|max:191',
        'subjects' => 'nullable|array|max:30',
        'subjects.*' => 'nullable|array',
        'subjects.*.subject' => 'nullable|string|max:191',
        'subjects.*.teacher' => 'nullable|string|max:191',
    ];

    private const CLASSIFICATION_RULES = [
        'illness_ids' => 'nullable|array|max:60',
        'illness_ids.*' => 'integer|exists:illnesses,id',
        'illness_other' => 'nullable|array',
        'illness_other.*' => 'nullable|string|max:150',
        'service_ids' => 'nullable|array|max:60',
        'service_ids.*' => 'integer|exists:service_types,id',
        'service_other' => 'nullable|array',
        'service_other.*' => 'nullable|string|max:150',
    ];

    private ConsultationPatientService $consultationPatients;

    private ConsultationMedicineService $consultationMedicines;

    private ConsultationImageService $consultationImages;

    private DocumentIssuanceNavigationService $navigation;

    public function __construct(
        ConsultationPatientService $consultationPatients,
        ConsultationMedicineService $consultationMedicines,
        ConsultationImageService $consultationImages,
        DocumentIssuanceNavigationService $navigation
    ) {
        $this->consultationPatients = $consultationPatients;
        $this->consultationMedicines = $consultationMedicines;
        $this->consultationImages = $consultationImages;
        $this->navigation = $navigation;
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
            'dob' => 'nullable|date|before_or_equal:today',
            'date_of_birth' => 'nullable|date|before_or_equal:today',
            'nursing_incharged' => 'nullable|integer|exists:users,id',
        ], self::CONSULTATION_IMAGE_RULES, self::CLASSIFICATION_RULES, self::CONSULTATION_TEXT_RULES, self::CONSULTATION_VALUE_RULES, self::CERTIFICATE_RULES), array_merge([
            'document_type.required' => 'Please choose the type of document to create.',
            'document_type.in' => 'Unknown document type.',
            'dob.before_or_equal' => 'Date of Birth cannot be in the future.',
            'date_of_birth.before_or_equal' => 'Date of Birth cannot be in the future.',
        ], self::CONSULTATION_IMAGE_MESSAGES));

        if ($request->input('document_type') === 'consultation_form') {
            $this->requireOtherNames($request->all());
        }

        // document_creator_id is always the signed-in user; never trust it from the form.
        $data = $request->except(['_token', 'document_creator_id', 'consultation_images']);
        $data = $this->forceOwnNursingInCharge($data);
        $data = $this->consultationPatients->normalizeContact($data);
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
                $this->consultationPatients->prepare($data);
                $this->storeConsultationForm($data);
            }

            DB::commit();

            return $this->navigation->created($data, $request);
        } catch (\Throwable $e) {
            DB::rollBack();
            $this->consultationImages->discardStoredFiles();

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

    /** The two pressure boxes joined as "120/80"; both blank is no pressure at all (it used to be saved as "/"). */
    private function joinBloodPressure(?string $systolic, ?string $diastolic): ?string
    {
        $systolic = trim((string) $systolic);
        $diastolic = trim((string) $diastolic);

        return ($systolic === '' && $diastolic === '') ? null : $systolic . '/' . $diastolic;
    }

    /**
     * Store a medical certificate document.
     */
    private function storeMedicalCertificate(array $data)
    {
        // Prepare the data for insertion
        $data['vital_signs_bp'] = $this->joinBloodPressure($data['vital_signs_bp_2'] ?? null, $data['vital_signs_bp_22'] ?? null);
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

        $data['dob'] = ConsultationInput::nullableString(
            $data['date_of_birth'] ?? ($data['dob'] ?? ($user?->dob ?? null))
        );
        $data['age'] = $this->consultationPatients->ageFromDateOfBirth($data['dob']);

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

        $data['date_of_birth'] = ConsultationInput::nullableString(
            $data['date_of_birth'] ?? ($user?->dob ?? null)
        );
        $data['age'] = $this->consultationPatients->ageFromDateOfBirth($data['date_of_birth']);

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

        $data['informant'] = $this->consultationPatients->informantLabel(
            $data['informant'] ?? null,
            ConsultationInput::nullableInt($data['year_level_id'] ?? null),
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
            ...$this->snapshotIds($data, $user),
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

        // Illness and services picked from the clinic list (ACCOMPLISHMENT REPORT).
        $this->syncClassification($requestDocument, $data);

        // Consultation photos go to the private consultation_images disk (see ConsultationImageService::store()).
        $uploadedImages = $this->consultationImages->store($requestDocument);
        if (! empty($uploadedImages)) {
            $requestDocument->consultation_images = $uploadedImages;
            $requestDocument->save();
        }

        // Handle medicine deduction
        $this->consultationMedicines->store($requestDocument, $data);

        // A patient already waiting in the queue gets this form attached, so the doctor can open it
        // from the queue screen even when the form was filled in after the patient was queued.
        \App\Models\PatientQueue::attachConsultation($requestDocument);

        // Log consultation form creation activity
        self::logConsultationCreation($requestDocument);

        return $requestDocument;
    }

    /**
     * The patient's campus / college / course / year level / department / office / type as they were on the day of
     * the visit, saved by id so the ACCOMPLISHMENT REPORT can group and filter them. A value the form did not send
     * keeps what the consultation already had, and otherwise comes from the patient's record.
     *
     * @return array<string, int|null>
     */
    private function snapshotIds(array $data, ?User $user, ?DocumentIssuance $existing = null): array
    {
        $ids = [];

        foreach (['campus_id', 'college_id', 'course_id', 'year_level_id', 'department_id', 'office_id'] as $column) {
            $ids[$column] = ConsultationInput::nullableInt($data[$column] ?? null)
                ?? ConsultationInput::nullableInt($existing?->{$column})
                ?? ConsultationInput::nullableInt($user?->{$column});
        }

        $ids['patient_type_id'] = $this->consultationPatients->patientTypeId($ids['year_level_id'], $data['informant'] ?? null, true)
            ?? ConsultationInput::nullableInt($existing?->patient_type_id);

        return $ids;
    }

    /**
     * Saves the illnesses and services picked on the consultation form. Only done when the form sent its picks
     * (a marker field, or the lists themselves), so an old form without them cannot wipe what was saved. The free
     * text of an "Others" line is kept only for lines that take free text.
     */
    private function syncClassification(DocumentIssuance $document, array $data): void
    {
        if (! array_key_exists('classification_submitted', $data)
            && ! array_key_exists('illness_ids', $data)
            && ! array_key_exists('service_ids', $data)) {
            return;
        }

        $illnessIds = collect($data['illness_ids'] ?? [])->map(fn ($id) => (int) $id)->unique()->values();
        $otherLines = Illness::whereIn('id', $illnessIds)->where('is_other', true)->pluck('id')->all();
        $texts = (array) ($data['illness_other'] ?? []);

        $picks = [];
        foreach ($illnessIds as $id) {
            $picks[$id] = ['other_text' => in_array($id, $otherLines, true) ? OtherName::clean($texts[$id] ?? null) : null];
        }

        $serviceIds = collect($data['service_ids'] ?? [])->map(fn ($id) => (int) $id)->unique()->values();
        $otherServices = ServiceType::whereIn('id', $serviceIds)->where('is_other', true)->pluck('id')->all();
        $serviceTexts = (array) ($data['service_other'] ?? []);

        $servicePicks = [];
        foreach ($serviceIds as $id) {
            $servicePicks[$id] = ['other_text' => in_array($id, $otherServices, true) ? OtherName::clean($serviceTexts[$id] ?? null) : null];
        }

        $document->illnesses()->sync($picks);
        $document->services()->sync($servicePicks);
    }

    /**
     * A ticked "Others" line (illness or service) must say what it is: the ACCOMPLISHMENT REPORT lists the typed names
     * under "Others". Only checked when the form carried its picks, like syncClassification().
     *
     * @throws ValidationException
     */
    private function requireOtherNames(array $data): void
    {
        if (! array_key_exists('classification_submitted', $data)
            && ! array_key_exists('illness_ids', $data)
            && ! array_key_exists('service_ids', $data)) {
            return;
        }

        $illnessTexts = (array) ($data['illness_other'] ?? []);
        $serviceTexts = (array) ($data['service_other'] ?? []);
        $errors = [];

        $illnessIds = collect($data['illness_ids'] ?? [])->map(fn ($id) => (int) $id)->unique()->values();
        foreach (Illness::with('system')->whereIn('id', $illnessIds)->where('is_other', true)->get() as $illness) {
            if (OtherName::clean(is_string($illnessTexts[$illness->id] ?? null) ? $illnessTexts[$illness->id] : null) === null) {
                $errors["illness_other.{$illness->id}"] = 'Please type what the "Others" illness under ' . ($illness->system?->name ?? 'the body system') . ' is, or untick it.';
            }
        }

        $serviceIds = collect($data['service_ids'] ?? [])->map(fn ($id) => (int) $id)->unique()->values();
        foreach (ServiceType::whereIn('id', $serviceIds)->where('is_other', true)->get() as $service) {
            if (OtherName::clean(is_string($serviceTexts[$service->id] ?? null) ? $serviceTexts[$service->id] : null) === null) {
                $errors["service_other.{$service->id}"] = 'Please type what the "Others" service under ' . (ServiceType::CATEGORY_LABELS[$service->category] ?? 'Services rendered') . ' is, or untick it.';
            }
        }

        if ($errors !== []) {
            throw ValidationException::withMessages($errors);
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
        ], self::CONSULTATION_IMAGE_RULES, self::CLASSIFICATION_RULES, self::CONSULTATION_TEXT_RULES, self::CONSULTATION_VALUE_RULES, self::CERTIFICATE_RULES), array_merge([
            'dob.before_or_equal' => 'Date of Birth cannot be in the future.',
            'date_of_birth.before_or_equal' => 'Date of Birth cannot be in the future.',
        ], self::CONSULTATION_IMAGE_MESSAGES));

        if ($requestDocument->document_type === 'consultation_form') {
            $this->requireOtherNames($request->all());
        }

        $data = $request->except(['_token', '_method', 'document_type', 'document_creator_id', 'consultation_images']);
        $data = $this->forceOwnNursingInCharge($data);
        $data = $this->consultationPatients->normalizeContact($data);

        // The document type is fixed at creation. Never take it from the request: a submitted
        // type must not be able to route the update to a different handler.
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

            return $this->navigation->changed($requestDocument, $request, 'Request document updated successfully.');
        } catch (\Throwable $e) {
            DB::rollBack();
            $this->consultationImages->discardStoredFiles();
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
        $data['vital_signs_bp'] = (array_key_exists('vital_signs_bp_2', $data) && array_key_exists('vital_signs_bp_22', $data))
            ? $this->joinBloodPressure($data['vital_signs_bp_2'], $data['vital_signs_bp_22'])
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
                $data['date_of_birth'] = ConsultationInput::nullableString(
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
                $data['date_of_birth'] = ConsultationInput::nullableString(
                    $data['date_of_birth'] ?? $requestDocument->date_of_birth
                );
            }
        } else {
            $data['campus'] = $requestDocument->campus;
            $data['college'] = $requestDocument->college;
            $data['course'] = $requestDocument->course;
            $data['year_level'] = $requestDocument->year_level;
            $data['date_of_birth'] = ConsultationInput::nullableString(
                $data['date_of_birth'] ?? $requestDocument->date_of_birth
            );
        }

        $data['age'] = $this->consultationPatients->ageFromDateOfBirth(
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

        $data['date_of_birth'] = ConsultationInput::nullableString(
            $data['date_of_birth'] ?? ($user?->dob ?? $requestDocument->date_of_birth)
        );
        $data['age'] = $this->consultationPatients->ageFromDateOfBirth(
            $data['date_of_birth'],
            (int) $requestDocument->age
        );
        $data['informant'] = $this->consultationPatients->informantLabel(
            $data['informant'] ?? $requestDocument->informant,
            ConsultationInput::nullableInt($data['year_level_id'] ?? null),
            $user
        );

        $canEditAssessmentPlanDirectly = $this->canCurrentUserEditAssessmentPlanDirectly();
        $staffOverrideActive = $this->shouldAllowStaffAssessmentPlanOverride($data);

        $originalAssessment = $requestDocument->assessment;
        $originalPlan = $requestDocument->plan;

        $incomingAssessment = array_key_exists('assessment', $data)
            ? ConsultationInput::nullableString($data['assessment'])
            : $requestDocument->assessment;
        $incomingPlan = array_key_exists('plan', $data)
            ? ConsultationInput::nullableString($data['plan'])
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
            ...$this->snapshotIds($data, $user, $requestDocument),
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

        // Illness and services picked from the clinic list (ACCOMPLISHMENT REPORT).
        $this->syncClassification($requestDocument, $data);

        // Handle image updates
        $this->consultationImages->update($requestDocument, $data);

        // Handle medicine updates (restore removed, deduct newly added)
        $this->consultationMedicines->update($requestDocument, $data);

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
            return ConsultationInput::nullableString($value);
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
     * Stream one consultation image. Images are clinical records, so they are only served
     * through this authenticated route (same role / permission checks as viewing the
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
     * Remove the specified resource from storage.
     */
    public function destroy(DocumentIssuance $document_issuance)
    {
        $request_document = $document_issuance;

        // A consultation can be deleted only by the clinic admin or the person who recorded it. This check
        // runs before the try block below, which would otherwise turn the 403 into a generic error message.
        abort_unless(
            $request_document->canBeDeletedBy(auth()->user()),
            403,
            'Only the clinic admin or the person who recorded this consultation can delete it.'
        );

        $deletedLabel = match ($request_document->document_type) {
            'consultation_form' => 'Consultation',
            'excuse_slip' => 'Excuse slip',
            'medical_certificate' => 'Medical certificate',
            default => 'Document',
        };

        try {
            // Give the deducted medicine stock back to the batches it came from before deleting;
            // otherwise the cascade-deleted consultation_medicines lose their stock permanently. The audit
            // row and the delete are in the same transaction, so a failure rolls back everything. DISP-1.
            DB::transaction(function () use ($request_document) {
                if ($request_document->document_type === 'consultation_form') {
                    foreach ($request_document->consultationMedicines()->get() as $consultationMedicine) {
                        $this->consultationMedicines->restore($consultationMedicine, $request_document);
                    }
                }

                // Snapshot first: once the record is gone nothing shows what was removed. R3-M5.
                self::logDocumentDeletion($request_document);

                $request_document->delete();

                // The doctor's queue screen must not keep pointing at a form that is gone.
                if ($request_document->document_type === 'consultation_form' && $request_document->user_id) {
                    \App\Models\PatientQueue::syncAttachment((int) $request_document->user_id);
                }
            });

            Log::info('Document deleted successfully', ['document_id' => $request_document->id]);

            return $this->navigation->changed($request_document, request(), $deletedLabel . ' deleted successfully.');
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

            // A one-letter search returned every patient with a dozen eager loads each. Require two
            // characters and cap the result list. M-11.
            if (mb_strlen(trim((string) $search)) < 2) {
                return response()->json([]);
            }

            // Search patients by any part of the name ("Ana Reyes", "Reyes, Ana", "ana*"), e-mail or ID number:
            // every typed word must be found in the person's name / ID columns.
            $patients = Patient::whereHas('user', function ($query) {
                $query->where('type', User::PATIENT);
            })
                ->whereHas('user', function ($query) use ($search) {
                    SearchTerm::whereAllWords($query, $search, SearchTerm::PERSON_COLUMNS);
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
                ->orderBy('patients.id')
                ->limit(25)
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
                // Height and weight change slowly and are carried over. BP, pulse, temperature, breathing rate and O2
                // saturation are measured at every visit: they are never copied from an earlier one (an old reading
                // that is not overwritten would be saved, and counted in the reports, as today's). Owner decision 2026-10-16.
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
                        'informant' => $this->consultationPatients->informantLabel(
                            null,
                            ConsultationInput::nullableInt($userContext?->year_level_id ?? null),
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
                    'informant' => $this->consultationPatients->informantLabel(
                        $resolvedHistoryData['informant'] ?? null,
                        ConsultationInput::nullableInt($userContext?->year_level_id ?? null),
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
                    'vital_signs_bp' => null,
                    'vital_signs_pr' => null,
                    'vital_signs_temp' => null,
                    'vital_signs_rr' => null,
                    'vital_signs_o2_sat' => null,
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
