@extends('layouts.app')
@section('title')
{{__('messages.request.view_request')}}
@endsection
@section('content')
@php
$documentModule = request('module', $requestDocument->document_type === 'consultation_form' ? 'consultation' : 'certificate');
$indexRoute = isRole('clinic_admin') ? route('document-issuances.index') :
(isRole('staff') ? route('staff.document-issuances.index') :
(isRole('doctor') ? route('doctors.document-issuances.index') : route('document-issuances.index')));
$indexUrlWithModule = $indexRoute . '?module=' . $documentModule;

$routeDocument = request()->route('document_issuance');
$documentId = $requestDocument->id
?? (is_object($routeDocument) ? ($routeDocument->id ?? null) : $routeDocument)
?? request()->route('id');

$exportPdfUrl = $documentId
? (isRole('clinic_admin') ? route('document-issuances.export-pdf', ['document_issuance' => $documentId]) :
(isRole('staff') ? route('staff.document-issuances.export-pdf', ['document_issuance' => $documentId]) :
(isRole('doctor') ? route('doctors.document-issuances.export-pdf', ['document_issuance' => $documentId]) : route('document-issuances.export-pdf', ['document_issuance' => $documentId]))))
: null;
@endphp
<div class="p-4">
    <div class="flex justify-between items-center mb-4">
        <a href="{{ $indexUrlWithModule }}" class="bg-blue-500 text-white px-4 py-2 rounded">Back</a>

        <div class="flex gap-2">
            {{-- The doctor opens the nurse's form from the queue; this is the way on to the assessment and plan. --}}
            @if($requestDocument->document_type == 'consultation_form' && canUseModule('consultations'))
            <a href="{{ getRouteByRole('document-issuances.edit', ['document_issuance' => $requestDocument->id]) }}" class="bg-blue-600 text-white px-4 py-2 rounded shadow-sm hover:bg-blue-700 transition-colors flex items-center gap-2">
                <i class="fas fa-pen"></i> Edit / add assessment &amp; plan
            </a>
            @endif
            @if($exportPdfUrl)
            @if($requestDocument->document_type == 'excuse_slip' || $requestDocument->document_type == 'medical_certificate')
            <a href="{{ $exportPdfUrl }}{{ str_contains($exportPdfUrl, '?') ? '&' : '?' }}action=print" class="bg-blue-600 text-white px-4 py-2 rounded shadow-sm hover:bg-blue-700 transition-colors flex items-center gap-2" target="_blank">
                <i class="fas fa-print"></i> Direct Print (Fast)
            </a>
            @endif
            <a href="{{ $exportPdfUrl }}" class="bg-green-500 text-white px-4 py-2 rounded shadow-sm hover:bg-green-600 transition-colors flex items-center gap-2" target="_blank">
                <i class="fas fa-file-pdf"></i> Export via PDF
            </a>
            @endif
        </div>
    </div>

    @if ($requestDocument->document_type == 'consultation_form')
    @php
    $planMedicines = $requestDocument->consultationMedicines()->where('used_for', 'plan')->with('medicine')->get();
    $nursingMedicines = $requestDocument->consultationMedicines()->where('used_for', 'nursing')->with('medicine')->get();
    $consultationImages = $requestDocument->consultationImageList();

    $documentUser = $requestDocument->user_id
    ? \App\Models\User::with(['department:id,department_name', 'office:id,office_name'])->find($requestDocument->user_id)
    : null;

    $isMeaningfulValue = static function ($value): bool {
    if ($value === null) {
    return false;
    }

    $text = trim((string) $value);
    if ($text === '') {
    return false;
    }

    $normalized = strtolower($text);
    if ($normalized === 'unknown' || str_starts_with($normalized, 'unknown ')) {
    return false;
    }

    return !in_array($normalized, ['n/a', 'na', 'null'], true);
    };

    $campusDisplay = $isMeaningfulValue($requestDocument->campus) ? trim((string) $requestDocument->campus) : null;
    $collegeDisplay = $isMeaningfulValue($requestDocument->college) ? trim((string) $requestDocument->college) : null;
    $courseDisplay = $isMeaningfulValue($requestDocument->course) ? trim((string) $requestDocument->course) : null;
    $yearLevelDisplay = $isMeaningfulValue($requestDocument->year_level) ? trim((string) $requestDocument->year_level) : null;
    $informantDisplay = $isMeaningfulValue($requestDocument->informant) ? trim((string) $requestDocument->informant) : null;
    $departmentDisplay = $isMeaningfulValue($documentUser?->department?->department_name) ? trim((string) $documentUser->department->department_name) : null;
    $officeDisplay = $isMeaningfulValue($documentUser?->office?->office_name) ? trim((string) $documentUser->office->office_name) : null;

    $patientTypeSource = strtolower(trim((string) ($informantDisplay ?? $yearLevelDisplay ?? '')));
    $isFacultyType = $patientTypeSource === 'faculty';
    $isStaffType = $patientTypeSource === 'staff';
    $isGuestType = $patientTypeSource === 'guest';
    $isStudentType = ! $isFacultyType && ! $isStaffType && ! $isGuestType;

    $showCourseYearBlock = $isStudentType && ($courseDisplay !== null || $yearLevelDisplay !== null);
    @endphp
    <form>
        <div class="grid grid-cols-4 gap-2 pb-2">
            <div class="col-span-1">
                <label class="block text-xs" for="name">NAME</label>
                <input type="text" id="name" name="name" class="w-full border-b border-black" value="{{ $requestDocument->name }}" readonly>
            </div>
            <div class="col-span-1">
                <label class="block text-xs" for="age">AGE</label>
                <input type="text" id="age" name="age" class="w-full border-b border-black" value="{{ $requestDocument->age }}" readonly>
            </div>
            <div class="col-span-1">
                <label class="block text-xs" for="gender">GENDER</label>
                <input type="text" id="gender" name="gender" class="w-full border-b border-black" value="{{ $requestDocument->gender }}" readonly>
            </div>
            <div class="col-span-1">
                <label class="block text-xs" for="status">STATUS</label>
                <input type="text" id="status" name="status" class="w-full border-b border-black" value="{{ $requestDocument->status }}" readonly>
            </div>
            <div class="col-span-1">
                <label class="block text-xs" for="date_of_birth">DATE OF BIRTH</label>
                <input type="date" id="date_of_birth" name="date_of_birth" class="w-full border-b border-black" value="{{ $requestDocument->date_of_birth ? \Carbon\Carbon::parse($requestDocument->date_of_birth)->format('Y-m-d') : '' }}" readonly>
            </div>
            <div class="col-span-1">
                <label class="block text-xs" for="address">ADDRESS</label>
                <input type="text" id="address" name="address" class="w-full border-b border-black" value="{{ $requestDocument->address }}" readonly>
            </div>
            <div class="col-span-1">
                <label class="block text-xs" for="religion">RELIGION</label>
                <input type="text" id="religion" name="religion" class="w-full border-b border-black" value="{{ $requestDocument->religion }}" readonly>
            </div>
            <div class="col-span-1">
                <label class="block text-xs" for="patient_contact">PATIENT'S CONTACT #</label>
                <input type="text" id="patient_contact" name="patient_contact" class="w-full border-b border-black" value="{{ $requestDocument->patient_contact }}" readonly>
            </div>
            @if($isStudentType && $campusDisplay)
            <div class="col-span-1">
                <label class="block text-xs" for="campus">CAMPUS</label>
                <input type="text" id="campus_id" name="campus_id" class="w-full border-b border-black" value="{{ $campusDisplay }}" readonly>
            </div>
            @endif
            @if(($isStudentType || $isFacultyType) && $collegeDisplay)
            <div class="col-span-1">
                <label class="block text-xs" for="college">COLLEGE</label>
                <input type="text" id="college_id" name="college_id" class="w-full border-b border-black" value="{{ $collegeDisplay }}" readonly>
            </div>
            @endif
            @if($showCourseYearBlock)
            <div class="col-span-1">
                <label class="block text-xs" for="course_year">COURSE & YEAR</label>
                @if($courseDisplay && $yearLevelDisplay)
                <div class="grid grid-cols-2 gap-2">
                    <input type="text" id="course_id" name="course_id" class="w-full border-b border-black" value="{{ $courseDisplay }}" readonly>
                    <input type="text" id="year_level_id" name="year_level_id" class="w-full border-b border-black" value="{{ $yearLevelDisplay }}" readonly>
                </div>
                @elseif($courseDisplay)
                <input type="text" id="course_id" name="course_id" class="w-full border-b border-black" value="{{ $courseDisplay }}" readonly>
                @else
                <input type="text" id="year_level_id" name="year_level_id" class="w-full border-b border-black" value="{{ $yearLevelDisplay }}" readonly>
                @endif
            </div>
            @endif
            @if($isFacultyType && $departmentDisplay)
            <div class="col-span-1">
                <label class="block text-xs" for="department">DEPARTMENT</label>
                <input type="text" id="department" name="department" class="w-full border-b border-black" value="{{ $departmentDisplay }}" readonly>
            </div>
            @endif
            @if($isStaffType && $officeDisplay)
            <div class="col-span-1">
                <label class="block text-xs" for="office">OFFICE</label>
                <input type="text" id="office" name="office" class="w-full border-b border-black" value="{{ $officeDisplay }}" readonly>
            </div>
            @endif
            @if($informantDisplay)
            <div class="col-span-1">
                <label class="block text-xs" for="informant">INFORMANT</label>
                <input type="text" id="informant" name="informant" class="w-full border-b border-black" value="{{ $informantDisplay }}" readonly>
            </div>
            @endif
            <div class="col-span-4">
                <label class="block text-xs" for="emergency_contact">CONTACT PERSON & NUMBER IN EMERGENCY</label>
                <input type="text" id="emergency_contact" name="emergency_contact" class="w-full border-b border-black" value="{{ $requestDocument->emergency_contact }}" readonly>
            </div>
        </div>
        <div class="grid grid-cols-4 gap-2 py-2">
            <div class="col-span-1">
                <label class="block text-xs" for="requested_at">CONSULTATION DATE</label>
                <input type="date" id="requested_at" name="requested_at" class="w-full border-b border-black" value="{{ $requestDocument->requested_at?->format('Y-m-d') }}" readonly>
            </div>
            <div class="col-span-3">
                <label class="block text-xs" for="complaints">Complaint/s:</label>
                <textarea id="complaints" name="complaints" class="w-full border-b border-black view-textarea" rows="2" readonly>{{ $requestDocument->complaints }}</textarea>
            </div>
            <div class="col-span-1"></div>
            <div class="col-span-3">
                <textarea id="note" name="note" class="w-full border-b border-black view-textarea" rows="3" readonly>{{ $requestDocument->note }}</textarea>
            </div>
        </div>
        <!-- Subjective Data -->
        <div class="grid grid-cols-4 gap-2 py-2">
            <div class="col-span-1">
                <label class="block text-red-500 font-bold">S</label>
                <label class="block text-xs">(Subjective Data)</label>
            </div>
            <div class="col-span-3">
                <div class="grid grid-cols-2 gap-2">
                    <div class="col-span-1">
                        <label class="block text-xs" for="vaccination_id">COVID Vaccination</label>
                        <input type="text" id="vaccination_id" name="vaccination_id" class="w-full border-b border-black" value="{{ $requestDocument->covid_vaccination }}" readonly>
                    </div>
                    <div class="col-span-1">
                        <label class="block text-xs" for="comorbidities_id">Comorbidities</label>
                        <input type="text" id="comorbidities_id" name="comorbidities_id" class="w-full border-b border-black" value="{{ $requestDocument->comorbidities }}" readonly>
                    </div>
                    <div class="col-span-1">
                        <label class="block text-xs" for="allergies">Allergies</label>
                        <input type="text" id="allergies" name="allergies" class="w-full border-b border-black" value="{{ $requestDocument->allergies }}" readonly>
                    </div>
                    <div class="col-span-1">
                        <label class="block text-xs" for="admissions_surgeries">Pertinent Admissions or Surgeries</label>
                        <input type="text" id="admissions_surgeries" name="admissions_surgeries" class="w-full border-b border-black" value="{{ $requestDocument->admissions_surgeries }}" readonly>
                    </div>
                    <div class="col-span-1">
                        <label class="block text-xs" for="maintenance">Maintenance</label>
                        <input type="text" id="maintenance" name="maintenance" class="w-full border-b border-black" value="{{ $requestDocument->maintenance }}" readonly>
                    </div>
                    <div class="col-span-1">
                        <label class="block text-xs" for="pregnancy_status">Pregnant or Not?</label>
                        <input type="text" id="pregnancy_status" name="pregnancy_status" class="w-full border-b border-black" value="{{ $requestDocument->pregnancy_status }}" readonly>
                    </div>
                    <div class="col-span-1">
                        <label class="block text-xs" for="lmp_aog">If YES, LMP/AOG</label>
                        <input type="text" id="lmp_aog" name="lmp_aog" class="w-full border-b border-black" value="{{ $requestDocument->lmp_aog }}" readonly>
                    </div>
                </div>
            </div>
        </div>
        <!-- Objective Data -->
        <div class="grid grid-cols-4 gap-2 py-2">
            <div class="col-span-1">
                <label class="block text-red-500 font-bold">O</label>
                <label class="block text-xs">(Objective Data)</label>
            </div>
            <div class="col-span-3">
                <div class="grid grid-cols-6 gap-2">
                    <div class="col-span-1">
                        <label class="block text-xs" for="vital_signs_bp">BP</label>
                        <input type="text" id="vital_signs_bp" name="vital_signs_bp" class="w-full border-b border-black" value="{{ $requestDocument->vital_signs_bp }}" readonly>
                    </div>
                    <div class="col-span-1">
                        <label class="block text-xs" for="vital_signs_pr">PR</label>
                        <input type="text" id="vital_signs_pr" name="vital_signs_pr" class="w-full border-b border-black" value="{{ $requestDocument->vital_signs_pr }}" readonly>
                    </div>
                    <div class="col-span-1">
                        <label class="block text-xs" for="vital_signs_temp">Temp</label>
                        <input type="text" id="vital_signs_temp" name="vital_signs_temp" class="w-full border-b border-black" value="{{ $requestDocument->vital_signs_temp }}" readonly>
                    </div>
                    <div class="col-span-1">
                        <label class="block text-xs" for="vital_signs_rr">RR</label>
                        <input type="text" id="vital_signs_rr" name="vital_signs_rr" class="w-full border-b border-black" value="{{ $requestDocument->vital_signs_rr }}" readonly>
                    </div>
                    <div class="col-span-1">
                        <label class="block text-xs" for="vital_signs_o2_sat">O2 Sat</label>
                        <input type="text" id="vital_signs_o2_sat" name="vital_signs_o2_sat" class="w-full border-b border-black" value="{{ $requestDocument->vital_signs_o2_sat }}" readonly>
                    </div>
                    <div class="col-span-1">
                        <label class="block text-xs" for="vital_signs_weight">Weight (kg)</label>
                        <input type="text" id="vital_signs_weight" name="vital_signs_weight" class="w-full border-b border-black" value="{{ $requestDocument->vital_signs_weight }}" readonly>
                    </div>
                    <div class="col-span-1">
                        <label class="block text-xs" for="vital_signs_height">Height (cm)</label>
                        <input type="text" id="vital_signs_height" name="vital_signs_height" class="w-full border-b border-black" value="{{ $requestDocument->vital_signs_height }}" readonly>
                    </div>
                </div>
                <div class="col-span-5">
                    <label class="block text-xs" for="pertinent_exam">PERTINENT EXAM</label>
                    <textarea id="pertinent_exam" name="pertinent_exam" class="w-full border-b border-black view-textarea" rows="5" readonly>{{ $requestDocument->pertinent_exam }}</textarea>
                </div>
            </div>
        </div>
        <!-- Assessment -->
        <div class="grid grid-cols-4 gap-2 py-2">
            <div class="col-span-1">
                <label class="block text-red-500 font-bold">A</label>
                <label class="block text-xs">(Assessment)</label>
            </div>
            <div class="col-span-3">
                <textarea id="assessment" name="assessment" class="w-full border-b border-black view-textarea" rows="5" readonly>{{ $requestDocument->assessment }}</textarea>
            </div>
        </div>
        <!-- Plan -->
        <div class="grid grid-cols-4 gap-2 py-2">
            <div class="col-span-1">
                <label class="block text-red-500 font-bold">P</label>
                <label class="block text-xs">(Plan)</label>
            </div>
            <div class="col-span-3">
                <textarea id="plan" name="plan" class="w-full border-b border-black view-textarea" rows="5" readonly>{{ $requestDocument->plan }}</textarea>

                @if($planMedicines->count() > 0)
                <div class="mt-3">
                    <label class="block text-xs font-semibold mb-2">Medicines in Plan:</label>
                    <div class="space-y-2">
                        @foreach($planMedicines as $med)
                        <div class="medicine-view-row">
                            <span class="font-medium">{{ $med->medicine->brand_name ?? $med->medicine->generic_name ?? 'N/A' }}</span>
                            <span class="text-gray-600">{{ $med->dosage }}</span>
                            <span class="text-gray-600">Qty: {{ $med->quantity }}</span>
                            <span class="text-gray-500 text-xs">{{ $med->dosage_instructions }}</span>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif
            </div>
        </div>

        <!-- Illness / services (ACCOMPLISHMENT REPORT) -->
        <div class="grid grid-cols-4 gap-2 py-2">
            <div class="col-span-1">
                <label class="block">Illness / diagnosis</label>
            </div>
            <div class="col-span-3">
                @forelse($requestDocument->illnessLabels() as $illnessLabel)
                    <span class="inline-block border border-black rounded px-2 py-1 mr-1 mb-1 text-sm">{{ $illnessLabel }}</span>
                @empty
                    <span class="text-gray-500 text-sm">Not classified</span>
                @endforelse
            </div>
        </div>
        <div class="grid grid-cols-4 gap-2 py-2">
            <div class="col-span-1">
                <label class="block">Services rendered</label>
            </div>
            <div class="col-span-3">
                @forelse($requestDocument->serviceLabels() as $serviceLabel)
                    <span class="inline-block border border-black rounded px-2 py-1 mr-1 mb-1 text-sm">{{ $serviceLabel }}</span>
                @empty
                    <span class="text-gray-500 text-sm">None ticked</span>
                @endforelse
            </div>
        </div>

        <!-- Consult Mode -->
        <div class="grid grid-cols-4 gap-2 py-2">
            <div class="col-span-1">
                <label class="block">Consult Mode</label>
            </div>
            <div class="col-span-3">
                <input type="text" id="consult_mode" name="consult_mode" class="w-full border-b border-black" readonly
                    value="{{ $requestDocument->consult_mode === 'physical' ? 'Physical' : ($requestDocument->consult_mode === 'virtual' ? 'Virtual' : 'N/A') }}">
            </div>
        </div>

        <!-- Nursing Intervention -->
        <div class="grid grid-cols-4 gap-2 py-2">
            <div class="col-span-1">
                <label class="block">Nursing Intervention</label>
            </div>
            <div class="col-span-3">
                <textarea id="nursing_intervention" name="nursing_intervention" class="w-full border-b border-black view-textarea" rows="5" readonly>{{ $requestDocument->nursing_intervention }}</textarea>

                @if($nursingMedicines->count() > 0)
                <div class="mt-3">
                    <label class="block text-xs font-semibold mb-2">Medicines in Nursing Intervention:</label>
                    <div class="space-y-2">
                        @foreach($nursingMedicines as $med)
                        <div class="medicine-view-row">
                            <span class="font-medium">{{ $med->medicine->brand_name ?? $med->medicine->generic_name ?? 'N/A' }}</span>
                            <span class="text-gray-600">{{ $med->dosage }}</span>
                            <span class="text-gray-600">Qty: {{ $med->quantity }}</span>
                            <span class="text-gray-500 text-xs">{{ $med->dosage_instructions }}</span>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif
            </div>
        </div>

        <!-- Nursing In-charged -->
        <div class="grid grid-cols-4 gap-2 py-2">
            <div class="col-span-1">
                <label class="block">Nursing In-charged</label>
            </div>
            <div class="col-span-3">
                <input type="text" id="nursing_incharged" name="nursing_incharged" class="w-full border-b border-black" readonly
                    value="{{ \App\Models\User::find($requestDocument->nursing_incharged_id)?->first_name . ' ' . \App\Models\User::find($requestDocument->nursing_incharged_id)?->last_name }}">
            </div>
        </div>

        <!-- Consultation Images -->
        @if(is_array($consultationImages) && count($consultationImages) > 0)
        <div class="grid grid-cols-4 gap-2 py-2 mt-4">
            <div class="col-span-1">
                <label class="block font-semibold">Consultation Images</label>
            </div>
            <div class="col-span-3">
                <div class="grid grid-cols-3 gap-4">
                    @foreach($consultationImages as $imageIndex => $image)
                    <div>
                        <img src="{{ getRouteByRole('document-issuances.image', [$requestDocument->id, $imageIndex]) }}"
                            alt="{{ $image['name'] ?? 'Consultation image' }}"
                            class="w-full h-40 object-cover rounded-lg border-2 border-gray-200">
                        <small class="text-gray-600 block mt-1">
                            {{ $image['name'] ?? 'Image' }} ({{ number_format(((int) ($image['size'] ?? 0)) / 1024 / 1024, 2) }}MB)
                        </small>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
        @endif
    </form>
    @elseif ($requestDocument->document_type == 'medical_certificate')
    <div class="flex justify-center items-center">
        <div class="bg-white p-6 rounded-lg shadow-lg" style="width: 1065px;">
            <div class="flex items-center my-4">
                <!-- Left Logo -->
                <div>
                    <img src="{{ asset('assets/image/norsu_logo.png') }}" alt="Logo" class="w-28 h-28">
                </div>

                <!-- Text Content -->
                <div class="text-center flex-1">
                    <h1 class="text-xl font-bold">Negros Oriental State University</h1>
                    <h2 class="text-md">University Medical Clinic, CNPAHS Bldg., Kagawasan Ave., Dumaguete City</h2>
                    <p class="text-sm">Tel #: 225-9400, then Local # 188, 09263829484</p>
                </div>

                <!-- Right Logo -->
                <div class="ml-4">
                    <img src="{{ asset('assets/image/norsu_clinic_logo.png') }}" alt="Logo" class="w-28 h-28">
                </div>
            </div>

            <h3 class="text-lg text-center font-semibold mb-8">
                MEDICAL CERTIFICATE
            </h3>
            <form>
                <div class="flex row">
                    <p>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;This is to certify that Mr./Ms.
                        <input type="text" id="name_2" name="name" style="width: 400px; text-align: center;" class="border-b border-black" value="{{ $requestDocument->name }}" readonly>,
                        <input type="text" id="age_2" name="age" style="width: 70px; text-align: center;" class="border-b border-black" value="{{ $requestDocument->age }}" readonly> yrs old,
                        <input type="text" id="gender_2" name="gender" style="width: 70px; text-align: center;" class="border-b border-black" value="{{ $requestDocument->gender }}" readonly> a resident of
                    </p>
                    <p>
                        <input type="text" id="address_2" name="address" style="width: 470px; text-align: center;" class="border-b border-black" value="{{ $requestDocument->address }}" readonly>
                        , was seen and examined at my clinic on <input type="string" id="examined_on" name="examined_on" style="width: 300px; text-align: center;" class="border-b border-black" value="{{ $requestDocument->examined_on ? formatExaminedOnForPDF($requestDocument->examined_on) : '' }}" readonly> with the following
                    <p class="font-semibold">complaints/diagnosis:</p>
                    <div class="border border-gray-300 p-2 h-28 mb-4">
                        <div class="col-span-3">
                            <textarea id="complaints_diagnosis" name="complaints_diagnosis" class="w-full border-black view-textarea" rows="4" readonly>{{ trim($requestDocument->complaints_diagnosis) }}</textarea>
                        </div>
                    </div>
                    </p>
                </div>

                <div class="grid grid-cols-6 grid-rows-1 gap-7 mb-2">
                    <div>
                        <p class="font-semibold">BP: <input type="text" id="vital_signs_bp_2" name="vital_signs_bp_2" style="width: 50px; text-align: center;" class="border-b border-black" value="{{ $requestDocument->vital_signs_bp }}" readonly></p>
                    </div>
                    <div>
                        <p class="font-semibold">P: <input type="text" id="vital_signs_pr_2" name="vital_signs_pr_2" style="width: 50px; text-align: center;" class="border-b border-black" value="{{ $requestDocument->vital_signs_pr }}" readonly></p>
                    </div>
                    <div>
                        <p class="font-semibold">R: <input type="text" id="vital_signs_rr_2" name="vital_signs_rr_2" style="width: 50px; text-align: center;" class="border-b border-black" value="{{ $requestDocument->vital_signs_rr }}" readonly></p>
                    </div>
                    <div>
                        <p class="font-semibold">T: <input type="text" id="vital_signs_temp_2" name="vital_signs_temp_2" style="width: 50px; text-align: center;" class="border-b border-black" value="{{ $requestDocument->vital_signs_temp }}" readonly></p>
                    </div>
                    <div>
                        <p class="font-semibold">Ht: <input type="text" id="vital_signs_height_2" name="vital_signs_height_2" style="width: 50px; text-align: center;" class="border-b border-black" value="{{ $requestDocument->vital_signs_height }}" readonly></p>
                    </div>
                    <div>
                        <p class="font-semibold">Wt: <input type="text" id="vital_signs_weight_2" name="vital_signs_weight_2" style="width: 50px; text-align: center;" class="border-b border-black" value="{{ $requestDocument->vital_signs_weight }}" readonly></p>
                    </div>
                </div>

                <p class="font-semibold">Remark/s:</p>
                <div class="border border-gray-300 p-2 h-28 mb-4">
                    <div class="col-span-3">
                        <textarea id="medical_cert_remarks" name="medical_cert_remarks" class="w-full border-black view-textarea" rows="4" readonly>{{ trim($requestDocument->medical_cert_remarks) }}</textarea>
                    </div>
                </div>

                <p class="text-sm text-black">Note: Please check the original copy of med cert before accepting the photocopied one. This document is <span class="font-bold underline">not to be used</span> outside school purposes or medico-legal purposes.</p>
                <p class="text-sm">This certificate is issued upon the request of <input type="text" id="request_of" name="request_of" style="width: 350px; text-align: center;" class="border-b border-black" value="{{ $requestDocument->request_of }}" readonly> for your reference.</p>

                <div class="text-right mt-4 mr-5">
                    <p class="font-semibold">{{ $medicalCertificateDoctorName ? 'Dr. ' . $medicalCertificateDoctorName : 'Doctor' }}</p>
                    <p class="text-xs">Lic #: <input type="text" id="doc_lic_no" name="doc_lic_no" style="width: 110px; text-align: center;" class="border-b border-black" value="{{ $requestDocument->doc_lic_no }}" readonly></p>
                    <p class=" text-xs">PTR #: <input type="text" id="doc_prt_no" name="doc_prt_no" style="width: 105px; text-align: center;" class="border-b border-black" value="{{ $requestDocument->doc_prt_no }}" readonly></p>
                </div>
            </form>
        </div>
    </div>
    @elseif ($requestDocument->document_type == 'excuse_slip')
    <div class="flex justify-center items-center mb-10">
        <div class="bg-white shadow-2xl rounded-sm p-12 border border-gray-200" style="width: 10in; min-width: 10in; font-family: 'Arial', sans-serif; color: #000;">
            <!-- Header -->
            <table class="w-full mb-10" style="border-collapse: collapse; table-layout: auto;">
                <tr>
                    <td style="width: 100px; text-align: left; vertical-align: middle;">
                        <img src="{{ asset('assets/image/norsu_logo.png') }}" alt="Logo" style="width: 80px; height: 80px; object-fit: contain;">
                    </td>
                    <td style="text-align: center; vertical-align: middle;">
                        <h1 class="text-2xl font-bold uppercase tracking-wider leading-tight" style="margin: 0;">Negros Oriental State University</h1>
                        <h2 class="text-xs font-medium" style="margin: 2px 0;">University Medical Clinic, CNPAHS Bldg., Kagawasan Ave., Dumaguete City</h2>
                        <p class="text-[10px] text-gray-600" style="margin: 0;">Tel #: 225-9400, then Local # 188, 09263829484</p>
                    </td>
                    <td style="width: 100px; text-align: right; vertical-align: middle;">
                        <img src="{{ asset('assets/image/norsu_clinic_logo.png') }}" alt="Logo" style="width: 80px; height: 80px; object-fit: contain;">
                    </td>
                </tr>
            </table>

            <div class="text-center mb-10 border-t border-b border-black py-4">
                <h3 class="text-3xl font-black tracking-widest" style="margin: 0;">STUDENT EXCUSE SLIP</h3>
            </div>

            <table class="w-full mb-10" style="border-collapse: collapse; table-layout: auto;">
                <tr>
                    <!-- Left Side: Student Information -->
                    <td style="width: 70%; vertical-align: top; padding-right: 40px;">
                        <div class="space-y-8">
                            <div class="flex items-baseline border-b-2 border-black pb-1 gap-10">
                                <label class="font-bold text-[13px] whitespace-nowrap uppercase tracking-tighter">DATE FILED:</label>
                                <span class="text-base font-bold">{{ $requestDocument->created_at->format('M d, Y') }}</span>
                            </div>

                            <div class="flex items-baseline border-b-2 border-black pb-1 px-1 gap-10">
                                <label class="font-bold text-[13px] whitespace-nowrap uppercase tracking-tighter">STUDENT'S NAME:</label>
                                <span class="flex-1 text-base font-bold uppercase">{{ $requestDocument->name }}</span>
                            </div>

                            <div class="flex items-baseline border-b-2 border-black pb-1 px-1 gap-10">
                                <label class="font-bold text-[13px] whitespace-nowrap uppercase tracking-tighter">SECTION:</label>
                                <span class="flex-1 text-base font-bold">{{ $requestDocument->course }} {{ $requestDocument->year_level }}</span>
                            </div>

                            <div class="flex items-baseline border-b-2 border-black pb-1 px-1 gap-10">
                                <label class="font-bold text-[13px] whitespace-nowrap uppercase tracking-tighter">DATE/S ABSENT:</label>
                                <span class="flex-1 text-base font-bold">{{ $requestDocument->examined_on ? formatExaminedOnForPDF($requestDocument->examined_on) : '' }}</span>
                            </div>

                            <div class="pt-4">
                                <label class="font-bold text-[13px] block mb-3 uppercase tracking-tighter">REASON (COMPLAINTS/DIAGNOSIS):</label>
                                <textarea class="w-full border-2 border-black p-4 text-base leading-relaxed overflow-hidden view-textarea" readonly>{{ $requestDocument->complaints_diagnosis }}</textarea>
                            </div>
                        </div>
                    </td>

                    <!-- Right Side: Subject Table -->
                    <td style="width: 45%; vertical-align: top;">
                        <table class="w-full border-collapse border-2 border-black" style="font-size: 10px;">
                            <thead>
                                <tr class="bg-gray-100">
                                    <th class="border border-black p-2 text-center font-bold" style="width: 50%;">SUBJECT</th>
                                    <th class="border border-black p-2 text-center font-bold" style="width: 50%;">TEACHER</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                $subjects = isset($requestDocument) && isset($requestDocument->subjects) ? (is_array($requestDocument->subjects) ? $requestDocument->subjects : json_decode($requestDocument->subjects, true)) : array_fill(0, 10, ['subject' => '', 'teacher' => '']);
                                @endphp
                                @for($i=0; $i<10; $i++)
                                    <tr>
                                    <td class="border border-black h-9 px-2 font-medium text-[12px] align-middle">
                                        {{ $subjects[$i]['subject'] ?? '' }}
                                    </td>
                                    <td class="border border-black h-9 px-2 font-medium text-[12px] align-middle">
                                        {{ $subjects[$i]['teacher'] ?? '' }}
                                    </td>
                </tr>
                @endfor
                </tbody>
            </table>
            <p class="text-[9px] italic mt-2 text-gray-500 text-center uppercase tracking-widest">To be filled by Subject Teachers upon return</p>
            </td>
            </tr>
            </table>

            <!-- Parent Signature Area -->
            <div class="mt-12 bg-gray-50/50 p-6 rounded-xl border border-dashed border-gray-200">
                <div class="flex items-baseline border-b-2 border-black/10 pb-2">
                    <label class="font-bold text-[11px] mr-2 whitespace-nowrap text-gray-500 uppercase tracking-tighter">PARENT'S OR GUARDIAN'S SIGNATURE:</label>
                    <div class="flex-1"></div>
                </div>
            </div>

            <!-- Remarks -->
            <div class="mt-12 border-t border-black pt-4">
                <label class="font-bold text-sm block mb-2">CLINIC REMARKS / RECOMMENDATION:</label>
                <textarea class="w-full border-none p-0 text-sm leading-relaxed overflow-hidden view-textarea" readonly>{{ $requestDocument->medical_cert_remarks }}</textarea>
            </div>

            <!-- Vital Signs -->
            <div class="mt-8 pt-4 border-t border-gray-200">
                <table class="w-full" style="border-collapse: collapse; table-layout: fixed; font-size: 11px;">
                    <tr>
                        <td style="width: 16%;"><label class="font-bold">BP:</label> <span class="border-b border-black px-1">{{ $requestDocument->vital_signs_bp }}</span></td>
                        <td style="width: 16%;"><label class="font-bold">P:</label> <span class="border-b border-black px-1">{{ $requestDocument->vital_signs_pr }}</span></td>
                        <td style="width: 16%;"><label class="font-bold">R:</label> <span class="border-b border-black px-1">{{ $requestDocument->vital_signs_rr }}</span></td>
                        <td style="width: 16%;"><label class="font-bold">T:</label> <span class="border-b border-black px-1">{{ $requestDocument->vital_signs_temp }}</span></td>
                        <td style="width: 16%;"><label class="font-bold">Ht:</label> <span class="border-b border-black px-1">{{ $requestDocument->vital_signs_height }}</span></td>
                        <td style="width: 16%;"><label class="font-bold">Wt:</label> <span class="border-b border-black px-1">{{ $requestDocument->vital_signs_weight }}</span></td>
                    </tr>
                </table>
            </div>

            <!-- Approvals Section -->
            <table class="w-full mt-12" style="border-collapse: collapse; table-layout: fixed;">
                <tr>
                    <td class="text-center" style="width: 50%; vertical-align: top;">
                        <div style="border-top: 2px solid #e5e7eb; padding-top: 12px; margin: 0 40px;">
                            <div class="font-bold text-sm tracking-tighter text-gray-200">-----------------</div>
                            <div class="text-[10px] text-gray-400 font-bold uppercase mt-1 tracking-widest">PROGRAM CHAIR</div>
                        </div>
                    </td>
                    <td class="text-center" style="width: 50%; vertical-align: top;">
                        <div style="border-top: 2px solid #e5e7eb; padding-top: 12px; margin: 0 40px;">
                            <div class="font-bold text-sm tracking-tighter text-blue-900 uppercase">
                                DR. {{ $medicalCertificateDoctorName ?? 'UNIVERSITY PHYSICIAN' }}
                            </div>
                            <div class="text-[10px] text-gray-400 font-bold uppercase mt-1 tracking-widest">UNIVERSITY PHYSICIAN</div>
                            <div class="text-[9px] mt-2 space-y-1 text-gray-500">
                                <p>Lic #: {{ $requestDocument->doc_lic_no }}</p>
                                <p>PTR #: {{ $requestDocument->doc_prt_no }}</p>
                            </div>
                        </div>
                    </td>
                </tr>
            </table>

            <div class="text-right mt-4 text-[8px] text-gray-400">
                NORSU-CLINIC-FORM-02
            </div>
        </div>
    </div>
    @endif
</div>

<style>
    #complaints_diagnosis,
    #medical_cert_remarks,
    #complaints,
    #pertinent_exam,
    #assessment,
    #plan,
    #nursing_intervention,
    #note {
        resize: none;
    }

    .view-textarea {
        resize: none;
        overflow: hidden;
    }

    .medicine-view-row {
        display: grid;
        grid-template-columns: 2fr 1.5fr 1fr 2fr;
        gap: 0.5rem;
        padding: 0.5rem;
        background-color: #f9fafb;
        border-radius: 0.375rem;
        align-items: center;
        font-size: 0.875rem;
        border: 1px solid #e5e7eb;
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        document.querySelectorAll('.view-textarea').forEach(function(textarea) {
            textarea.style.height = 'auto';
            textarea.style.height = textarea.scrollHeight + 'px';
        });
    });
</script>
@endsection