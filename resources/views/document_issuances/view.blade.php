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
? (isRole('clinic_admin') ? route('document-issuances.export-pdf', $documentId) :
(isRole('staff') ? route('staff.document-issuances.export-pdf', $documentId) :
(isRole('doctor') ? route('doctors.document-issuances.export-pdf', $documentId) : route('document-issuances.export-pdf', $documentId))))
: null;
@endphp
<div class="p-4">
    <div class="flex justify-between items-center mb-4">
        <a href="{{ $indexUrlWithModule }}" class="bg-blue-500 text-white px-4 py-2 rounded">Back</a>

        @if($exportPdfUrl)
        <a href="{{ $exportPdfUrl }}" class="bg-green-500 text-white px-4 py-2 rounded" target="_blank">Export via PDF</a>
        @endif
    </div>

    @if ($requestDocument->document_type == 'consultation_form')
    @php
    $planMedicines = $requestDocument->consultationMedicines()->where('used_for', 'plan')->with('medicine')->get();
    $nursingMedicines = $requestDocument->consultationMedicines()->where('used_for', 'nursing')->with('medicine')->get();
    $consultationImages = $requestDocument->consultation_images
    ? (is_string($requestDocument->consultation_images) ? json_decode($requestDocument->consultation_images, true) : $requestDocument->consultation_images)
    : [];

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

    $yearOrRoleDisplay = $yearLevelDisplay ?? $informantDisplay;
    $showCourseYearBlock = $courseDisplay !== null || $yearOrRoleDisplay !== null;
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
            @if($campusDisplay)
            <div class="col-span-1">
                <label class="block text-xs" for="campus">CAMPUS</label>
                <input type="text" id="campus_id" name="campus_id" class="w-full border-b border-black" value="{{ $campusDisplay }}" readonly>
            </div>
            @endif
            @if($collegeDisplay)
            <div class="col-span-1">
                <label class="block text-xs" for="college">COLLEGE</label>
                <input type="text" id="college_id" name="college_id" class="w-full border-b border-black" value="{{ $collegeDisplay }}" readonly>
            </div>
            @endif
            @if($showCourseYearBlock)
            <div class="col-span-1">
                <label class="block text-xs" for="course_year">COURSE & YEAR</label>
                @if($courseDisplay && $yearOrRoleDisplay)
                <div class="grid grid-cols-2 gap-2">
                    <input type="text" id="course_id" name="course_id" class="w-full border-b border-black" value="{{ $courseDisplay }}" readonly>
                    <input type="text" id="year_level_id" name="year_level_id" class="w-full border-b border-black" value="{{ $yearOrRoleDisplay }}" readonly>
                </div>
                @elseif($courseDisplay)
                <input type="text" id="course_id" name="course_id" class="w-full border-b border-black" value="{{ $courseDisplay }}" readonly>
                @else
                <input type="text" id="year_level_id" name="year_level_id" class="w-full border-b border-black" value="{{ $yearOrRoleDisplay }}" readonly>
                @endif
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
                <input type="date" id="requested_at" name="requested_at" class="w-full border-b border-black" value="{{ $requestDocument->requested_at->format('Y-m-d') }}" readonly>
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
                    @foreach($consultationImages as $image)
                    <div>
                        <img src="{{ asset('uploads/' . $image['path']) }}"
                            alt="{{ $image['name'] }}"
                            class="w-full h-40 object-cover rounded-lg border-2 border-gray-200">
                        <small class="text-gray-600 block mt-1">
                            {{ $image['name'] }} ({{ number_format($image['size'] / 1024 / 1024, 2) }}MB)
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

            <h3 class="text-lg text-center font-semibold mb-8">MEDICAL CERTIFICATE</h3>
            <form action="{{ 
                isRole('clinic_admin') ? route('document-issuances.store') : 
                (isRole('staff') ? route('staff.document-issuances.store') : 
                (isRole('doctor') ? route('doctors.document-issuances.store') : route('document-issuances.store')))
            }}" method="POST">
                @csrf
                <div class="form-group mb-5 d-none">
                    <label for="document_type">Document Type</label>
                    <select name="document_type" id="document_type" class="form-control" readonly>
                        <option value="medical_certificate" selected>Medical Certificate</option>
                    </select>
                </div>

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
                            <textarea id="complaints_diagnosis" name="complaints_diagnosis" class="w-full border-black" rows="5" readonly>
                            {{ trim($requestDocument->complaints_diagnosis) }}
                            </textarea>
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
                        <textarea id="medical_cert_remarks" name="medical_cert_remarks" class="w-full border-black" rows="5" readonly>
                        {{ trim($requestDocument->medical_cert_remarks) }}
                        </textarea>
                    </div>
                </div>

                <p class="text-sm text-black">Note: Please check the original copy of med cert before accepting the photocopied med cert. This medical certificate is <span class="font-bold underline">not to be used</span> outside school purposes or medico-legal purposes.</p>
                <p class="text-sm">This certificate is issued upon the request of <input type="text" id="request_of" name="request_of" style="width: 350px; text-align: center;" class="border-b border-black" value="{{ $requestDocument->request_of }}" readonly> for your reference.</p>

                <div class="text-right mt-4 mr-5">
                    <p class="font-semibold">{{ $medicalCertificateDoctorName ? 'Dr. ' . $medicalCertificateDoctorName : 'Dr. Michael S. Oliveros' }}</p>
                    <p class="text-xs">Lic #: <input type="text" id="doc_lic_no" name="doc_lic_no" style="width: 110px; text-align: center;" class="border-b border-black" value="{{ $requestDocument->doc_lic_no }}" readonly></p>
                    <p class=" text-xs">PTR #: <input type="text" id="doc_prt_no" name="doc_prt_no" style="width: 105px; text-align: center;" class="border-b border-black" value="{{ $requestDocument->doc_prt_no }}" readonly></p>
                </div>
            </form>
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