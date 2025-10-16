@extends('layouts.app')
@section('title')
{{__('messages.request.edit_request')}}
@endsection
@section('content')
<div class="p-4">
    <div class="flex justify-between items-center mb-4">
        <a href="{{ 
            isRole('clinic_admin') ? route('request-documents.index') : 
            (isRole('staff') ? route('staff.request-documents.index') : 
            (isRole('doctor') ? route('doctors.request-documents.index') : route('request-documents.index')))
        }}" class="bg-blue-500 text-white px-4 py-2 rounded">Back</a>
    </div>

    @if ($requestDocument->document_type == 'consultation_form')
    <form action="{{ 
        isRole('clinic_admin') ? route('request-documents.update', $requestDocument) : 
        (isRole('staff') ? route('staff.request-documents.update', $requestDocument) : 
        (isRole('doctor') ? route('doctors.request-documents.update', $requestDocument) : route('request-documents.update', $requestDocument)))
    }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        <!-- Hidden field to carry patient_id from query parameter -->
        @if(request('patient_id'))
        <input type="hidden" name="redirect_patient_id" value="{{ request('patient_id') }}">
        @endif

        <div class="grid grid-cols-4 gap-2 pb-2">
            <div class="col-span-1">
                <label class="block text-xs" for="name">NAME</label>
                <input type="text" id="name" name="name" class="w-full border-b border-black" value="{{ old('name', $requestDocument->name) }}">
            </div>
            <div class="col-span-1">
                <label class="block text-xs" for="age">AGE</label>
                <input type="text" id="age" name="age" class="w-full border-b border-black" value="{{ old('age', $requestDocument->age) }}">
            </div>
            <div class="col-span-1">
                <label class="block text-xs" for="gender">GENDER</label>
                <input type="text" id="gender" name="gender" class="w-full border-b border-black" value="{{ old('gender', $requestDocument->gender) }}">
            </div>
            <div class="col-span-1">
                <label class="block text-xs" for="status">STATUS</label>
                <input type="text" id="status" name="status" class="w-full border-b border-black" value="{{ old('status', $requestDocument->status) }}">
            </div>
            <div class="col-span-1">
                <label class="block text-xs" for="date_of_birth">DATE OF BIRTH</label>
                <input type="date" id="date_of_birth" name="date_of_birth" class="w-full border-b border-black" value="{{ old('date_of_birth', $requestDocument->date_of_birth ? \Carbon\Carbon::parse($requestDocument->date_of_birth)->format('Y-m-d') : '') }}">
            </div>
            <div class="col-span-1">
                <label class="block text-xs" for="address">ADDRESS</label>
                <input type="text" id="address" name="address" class="w-full border-b border-black" value="{{ old('address', $requestDocument->address) }}">
            </div>
            <div class="col-span-1">
                <label class="block text-xs" for="religion">RELIGION</label>
                <input type="text" id="religion" name="religion" class="w-full border-b border-black" value="{{ old('religion', $requestDocument->religion) }}">
            </div>
            <div class="col-span-1">
                <label class="block text-xs" for="patient_contact">PATIENT'S CONTACT #</label>
                <input type="text" id="patient_contact" name="patient_contact" class="w-full border-b border-black" value="{{ old('patient_contact', $requestDocument->patient_contact) }}">
            </div>
            <div class="col-span-1">
                <label class="block text-xs" for="campus_id">CAMPUS</label>
                <select id="campus_id" name="campus_id" class="w-full border-b border-black">
                    @foreach($campuses as $campus)
                    <option value="{{ $campus->id }}" {{ old('campus_id', $requestDocument->campus_id ?? '') == $campus->id ? 'selected' : '' }}>
                        {{ $campus->campus_name }}
                    </option>
                    @endforeach
                </select>
            </div>
            <div class="col-span-1">
                <label class="block text-xs" for="college_id">COLLEGE</label>
                <select id="college_id" name="college_id" class="w-full border-b border-black">
                    @foreach($colleges as $college)
                    <option value="{{ $college->id }}" {{ old('college_id', $requestDocument->college_id ?? '') == $college->id ? 'selected' : '' }}>
                        {{ $college->college_name }}
                    </option>
                    @endforeach
                </select>
            </div>
            <div class="col-span-1">
                <label class="block text-xs" for="course_id">COURSE</label>
                <select id="course_id" name="course_id" class="w-full border-b border-black">
                    @foreach($courses as $course)
                    <option value="{{ $course->id }}" {{ old('course_id', $requestDocument->course_id ?? '') == $course->id ? 'selected' : '' }}>
                        {{ $course->course_name }}
                    </option>
                    @endforeach
                </select>
            </div>
            <div class="col-span-1">
                <label class="block text-xs" for="year_level_id">YEAR LEVEL</label>
                <select id="year_level_id" name="year_level_id" class="w-full border-b border-black">
                    @foreach($yearLevels as $level)
                    <option value="{{ $level->id }}" {{ old('year_level_id', $requestDocument->year_level_id ?? '') == $level->id ? 'selected' : '' }}>
                        {{ $level->year_level_name }}
                    </option>
                    @endforeach
                </select>
            </div>
            <div class="col-span-1">
                <label class="block text-xs" for="informant">INFORMANT</label>
                <input type="text" id="informant" name="informant" class="w-full border-b border-black" value="{{ old('informant', $requestDocument->informant) }}">
            </div>
            <div class="col-span-4">
                <label class="block text-xs" for="emergency_contact">CONTACT PERSON & NUMBER IN EMERGENCY</label>
                <input type="text" id="emergency_contact" name="emergency_contact" class="w-full border-b border-black" value="{{ old('emergency_contact', $requestDocument->emergency_contact) }}">
            </div>
        </div>
        <div class="grid grid-cols-4 gap-2 py-2">
            <div class="col-span-1">
                <label class="block text-xs" for="requested_at">REQUEST DATE</label>
                <input type="date" id="requested_at" name="requested_at" class="w-full border-b border-black" value="{{ old('requested_at', $requestDocument->requested_at->format('Y-m-d')) }}">
            </div>
            <div class="col-span-3">
                <label class="block text-xs" for="complaints">Complaint/s:</label>
                <textarea id="complaints" name="complaints" class="w-full border-b border-black" rows="5">{{ old('complaints', $requestDocument->complaints) }}</textarea>
            </div>
        </div>
        <!-- Subjective Complaints -->
        <div class="grid grid-cols-4 gap-2 py-2">
            <div class="col-span-1">
                <label class="block text-red-500 font-bold">S</label>
                <label class="block text-xs">(Subjective Complaints)</label>
            </div>
            <div class="col-span-3">
                <div class="grid grid-cols-2 gap-2">
                    <div class="col-span-1">
                        <label class="block text-xs" for="vaccination_id">COVID Vaccination</label>
                        <select id="vaccination_id" name="vaccination_id" class="w-full border-b border-black">
                            @foreach($vaccinations as $vaccination)
                            <option value="{{ $vaccination->id }}" {{ old('vaccination_id', $requestDocument->vaccination_id ?? '') == $vaccination->id ? 'selected' : '' }}>
                                {{ $vaccination->vaccination_status }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-span-1">
                        <label class="block text-xs" for="comorbidities_id">Comorbidities</label>
                        <select id="comorbidities_id" name="comorbidities_id" class="w-full border-b border-black">
                            @foreach($diagnoses as $diagnose)
                            <option value="{{ $diagnose->id }}" {{ old('comorbidities_id', $requestDocument->comorbidities_id ?? '') == $diagnose->id ? 'selected' : '' }}>
                                {{ $diagnose->diagnoses }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-span-1">
                        <label class="block text-xs" for="allergies">Allergies</label>
                        <input type="text" id="allergies" name="allergies" class="w-full border-b border-black" value="{{ old('allergies', $requestDocument->allergies) }}">
                    </div>
                    <div class="col-span-1">
                        <label class="block text-xs" for="admissions_surgeries">Pertinent Admissions or Surgeries</label>
                        <input type="text" id="admissions_surgeries" name="admissions_surgeries" class="w-full border-b border-black" value="{{ old('admissions_surgeries', $requestDocument->admissions_surgeries) }}">
                    </div>
                    <div class="col-span-1">
                        <label class="block text-xs" for="maintenance">Maintenance</label>
                        <input type="text" id="maintenance" name="maintenance" class="w-full border-b border-black" value="{{ old('maintenance', $requestDocument->maintenance) }}">
                    </div>
                    <div class="col-span-1">
                        <label class="block text-xs" for="pregnancy_status">Pregnant or Not?</label>
                        <input type="text" id="pregnancy_status" name="pregnancy_status" class="w-full border-b border-black" value="{{ old('pregnancy_status', $requestDocument->pregnancy_status) }}">
                    </div>
                    <div class="col-span-1">
                        <label class="block text-xs" for="lmp_aog">If YES, LMP/AOG</label>
                        <input type="text" id="lmp_aog" name="lmp_aog" class="w-full border-b border-black" value="{{ old('lmp_aog', $requestDocument->lmp_aog) }}">
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
                        <input type="text" id="vital_signs_bp" name="vital_signs_bp" class="w-full border-b border-black" value="{{ old('vital_signs_bp', $requestDocument->vital_signs_bp) }}">
                    </div>
                    <div class="col-span-1">
                        <label class="block text-xs" for="vital_signs_pr">PR</label>
                        <input type="text" id="vital_signs_pr" name="vital_signs_pr" class="w-full border-b border-black" value="{{ old('vital_signs_pr', $requestDocument->vital_signs_pr) }}">
                    </div>
                    <div class="col-span-1">
                        <label class="block text-xs" for="vital_signs_temp">Temp</label>
                        <input type="text" id="vital_signs_temp" name="vital_signs_temp" class="w-full border-b border-black" value="{{ old('vital_signs_temp', $requestDocument->vital_signs_temp) }}">
                    </div>
                    <div class="col-span-1">
                        <label class="block text-xs" for="vital_signs_rr">RR</label>
                        <input type="text" id="vital_signs_rr" name="vital_signs_rr" class="w-full border-b border-black" value="{{ old('vital_signs_rr', $requestDocument->vital_signs_rr) }}">
                    </div>
                    <div class="col-span-1">
                        <label class="block text-xs" for="vital_signs_o2_sat">O2 Sat</label>
                        <input type="text" id="vital_signs_o2_sat" name="vital_signs_o2_sat" class="w-full border-b border-black" value="{{ old('vital_signs_o2_sat', $requestDocument->vital_signs_o2_sat) }}">
                    </div>
                    <div class="col-span-1">
                        <label class="block text-xs" for="vital_signs_weight">Wt</label>
                        <input type="text" id="vital_signs_weight" name="vital_signs_weight" class="w-full border-b border-black" value="{{ old('vital_signs_weight', $requestDocument->vital_signs_weight) }}">
                    </div>
                    <div class="col-span-1">
                        <label class="block text-xs" for="vital_signs_height">Height</label>
                        <input type="text" id="vital_signs_height" name="vital_signs_height" class="w-full border-b border-black" value="{{ old('vital_signs_height', $requestDocument->vital_signs_height) }}">
                    </div>
                </div>
                <div class="col-span-5">
                    <label class="block text-xs" for="pertinent_exam">PERTINENT EXAM</label>
                    <textarea id="pertinent_exam" name="pertinent_exam" class="w-full border-b border-black" rows="5">{{ old('pertinent_exam', $requestDocument->pertinent_exam) }}</textarea>
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
                <textarea id="assessment" name="assessment" class="w-full border-b border-black" rows="5">{{ old('assessment', $requestDocument->assessment) }}</textarea>
            </div>
        </div>
        <!-- Plan -->
        <div class="grid grid-cols-4 gap-2 py-2">
            <div class="col-span-1">
                <label class="block text-red-500 font-bold">P</label>
                <label class="block text-xs">(Plan)</label>
            </div>
            <div class="col-span-3">
                <textarea id="plan" name="plan" class="w-full border-b border-black" rows="5">{{ old('plan', $requestDocument->plan) }}</textarea>
            </div>
        </div>

        <div class="grid grid-cols-4 gap-2 py-2">
            <div class="col-span-1">
                <label class="block">Consult Mode</label>
            </div>
            <div class="col-span-3">
                <select id="consult_mode" name="consult_mode" class="w-full border-b border-black">
                    <option value="physical" {{ old('consult_mode', $requestDocument->consult_mode) == 'physical' ? 'selected' : '' }}>Physical</option>
                    <option value="virtual" {{ old('consult_mode', $requestDocument->consult_mode) == 'virtual' ? 'selected' : '' }}>Virtual</option>
                </select>
            </div>
        </div>

        <div class="grid grid-cols-4 gap-2 py-2">
            <div class="col-span-1">
                <label class="block">Nursing Intervention</label>
            </div>
            <div class="col-span-3">
                <input type="text" id="nursing_intervention" name="nursing_intervention" class="w-full border-b border-black" value="{{ old('nursing_intervention', $requestDocument->nursing_intervention) }}">
            </div>
        </div>

        <div class="grid grid-cols-4 gap-2 py-2">
            <div class="col-span-1">
                <label class="block">Nursing In-charged</label>
            </div>
            <div class="col-span-3">
                @if(auth()->user()->type == \App\Models\User::ADMIN)
                <!-- Admin can select the nursing in-charged -->
                <select id="nursing_incharged" name="nursing_incharged" class="w-full border-b border-black" required>
                    <option value="" disabled {{ empty(old('nursing_incharged', $requestDocument->nursing_incharged_id ?? '')) ? 'selected' : '' }}>Select Nursing In-charged</option>
                    @foreach(\App\Models\User::where('type', \App\Models\User::STAFF)->get() as $staff)
                    <option value="{{ $staff->id }}"
                        {{ old('nursing_incharged', $requestDocument->nursing_incharged_id ?? '') == $staff->id ? 'selected' : '' }}>
                        {{ $staff->first_name }} {{ $staff->last_name }}
                    </option>
                    @endforeach
                </select>
                @elseif(auth()->user()->type == \App\Models\User::STAFF)
                <!-- Staff's account is pre-filled -->
                <input type="text" id="nursing_incharged_display" class="w-full border-b border-black" value="{{ auth()->user()->first_name }} {{ auth()->user()->last_name }}" readonly>
                <input type="hidden" id="nursing_incharged" name="nursing_incharged" value="{{ auth()->user()->id }}">
                @endif
            </div>
        </div>

        <!-- Image Upload Section -->
        <div class="grid grid-cols-4 gap-2 py-2 mt-4">
            <div class="col-span-1">
                <label class="block font-semibold">Consultation Images</label>
                <small class="text-gray-500">Max 5MB per image</small>
            </div>
            <div class="col-span-3">
                <!-- Display existing images -->
                @if($requestDocument->consultation_images)
                @php
                $existingImages = is_string($requestDocument->consultation_images)
                ? json_decode($requestDocument->consultation_images, true)
                : $requestDocument->consultation_images;
                @endphp

                @if(is_array($existingImages) && count($existingImages) > 0)
                <div class="mb-4">
                    <label class="block text-sm font-semibold mb-2">Current Images:</label>
                    <div class="grid grid-cols-3 gap-4" id="existing_images_container">
                        @foreach($existingImages as $index => $image)
                        <div class="image-preview-wrapper" data-image-index="{{ $index }}">
                            <img src="{{ asset('uploads/' . $image['path']) }}"
                                alt="{{ $image['name'] }}"
                                class="image-preview">
                            <button type="button" class="remove-existing-image-btn"
                                data-image-index="{{ $index }}"
                                onclick="removeExistingImage({{ $index }})">
                                ×
                            </button>
                            <small class="text-gray-600 block mt-1">
                                {{ $image['name'] }} ({{ number_format($image['size'] / 1024 / 1024, 2) }}MB)
                            </small>
                        </div>
                        @endforeach
                    </div>
                </div>

                <!-- Hidden field to track removed images -->
                <input type="hidden" name="removed_images" id="removed_images" value="">
                @endif
                @endif

                <!-- Upload new images -->
                <div class="mt-4">
                    <label class="block text-sm font-semibold mb-2">Add New Images:</label>
                    <input type="file" id="consultation_images" name="consultation_images[]"
                        class="w-full border border-gray-300 rounded p-2"
                        accept="image/jpeg,image/png,image/jpg,image/gif"
                        multiple>
                    <small class="text-gray-500">You can select multiple images (JPEG, PNG, JPG, GIF)</small>

                    <!-- Image Preview Container for new images -->
                    <div id="image_preview_container" class="mt-4 grid grid-cols-3 gap-4"></div>
                </div>
            </div>
        </div>


        <div class="flex justify-end mt-6">
            <button type="submit" class="bg-green-500 text-white px-6 py-2 rounded">Update</button>
        </div>
    </form>

    @elseif ($requestDocument->document_type == 'medical_certificate')
    <form action="{{ 
        isRole('clinic_admin') ? route('request-documents.update', $requestDocument) : 
        (isRole('staff') ? route('staff.request-documents.update', $requestDocument) : 
        (isRole('doctor') ? route('doctors.request-documents.update', $requestDocument) : route('request-documents.update', $requestDocument)))
    }}" method="POST">
        @csrf
        @method('PUT')

        <!-- Hidden field to carry patient_id from query parameter -->
        @if(request('patient_id'))
        <input type="hidden" name="redirect_patient_id" value="{{ request('patient_id') }}">
        @endif

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
            <div class="form-group mb-5 d-none">
                <label for="document_type">Document Type</label>
                <select name="document_type" id="document_type" class="form-control" readonly>
                    <option value="medical_certificate" selected>Medical Certificate</option>
                </select>
            </div>
            <div class="flex row">
                <p>
                    This is to certify that Mr./Ms.
                    <input type="text" id="name_2" name="name" style="width: 400px; text-align: center;" class="border-b border-black" value="{{ old('name', $requestDocument->name) }}">
                    <input type="text" id="age_2" name="age" style="width: 70px; text-align: center;" class="border-b border-black" value="{{ old('age', $requestDocument->age) }}"> yrs old,
                    <input type="text" id="gender_2" name="gender" style="width: 70px; text-align: center;" class="border-b border-black" value="{{ old('gender', $requestDocument->gender) }}"> a resident of
                </p>
                <p>
                    <input type="text" id="address_2" name="address" style="width: 470px; text-align: center;" class="border-b border-black" value="{{ old('address', $requestDocument->address) }}">
                    , was seen and examined at my clinic on <input type="date" id="examined_on" name="examined_on" style="width: 120px; text-align: center;" class="border-b border-black" value="{{ old('examined_on', $requestDocument->examined_on ? (strpos($requestDocument->examined_on, '|') !== false ? explode('|', $requestDocument->examined_on)[0] : (strpos($requestDocument->examined_on, ',') !== false ? explode(',', $requestDocument->examined_on)[0] : $requestDocument->examined_on)) : '') }}"> with the following
                <p class="font-semibold">complaints/diagnosis:</p>
                <div class="border border-gray-300 p-2 h-28 mb-4">
                    <div class="col-span-3">
                        <textarea id="complaints_diagnosis" name="complaints_diagnosis" class="w-full border-black" rows="5">{{ old('complaints_diagnosis', trim($requestDocument->complaints_diagnosis)) }}</textarea>
                    </div>
                </div>
                </p>
            </div>
            <div class="grid grid-cols-6 grid-rows-1 gap-7 mb-2">
                <div>
                    <p class="font-semibold">BP: <input type="text" id="vital_signs_bp_2" name="vital_signs_bp_2" style="width: 50px; text-align: center;" class="border-b border-black" value="{{ old('vital_signs_bp_2', $requestDocument->vital_signs_bp) }}"></p>
                </div>
                <div>
                    <p class="font-semibold">P: <input type="text" id="vital_signs_pr_2" name="vital_signs_pr_2" style="width: 50px; text-align: center;" class="border-b border-black" value="{{ old('vital_signs_pr_2', $requestDocument->vital_signs_pr) }}"></p>
                </div>
                <div>
                    <p class="font-semibold">R: <input type="text" id="vital_signs_rr_2" name="vital_signs_rr_2" style="width: 50px; text-align: center;" class="border-b border-black" value="{{ old('vital_signs_rr_2', $requestDocument->vital_signs_rr) }}"></p>
                </div>
                <div>
                    <p class="font-semibold">T: <input type="text" id="vital_signs_temp_2" name="vital_signs_temp_2" style="width: 50px; text-align: center;" class="border-b border-black" value="{{ old('vital_signs_temp_2', $requestDocument->vital_signs_temp) }}"></p>
                </div>
                <div>
                    <p class="font-semibold">Ht: <input type="text" id="vital_signs_height_2" name="vital_signs_height_2" style="width: 50px; text-align: center;" class="border-b border-black" value="{{ old('vital_signs_height_2', $requestDocument->vital_signs_height) }}"></p>
                </div>
                <div>
                    <p class="font-semibold">Wt: <input type="text" id="vital_signs_weight_2" name="vital_signs_weight_2" style="width: 50px; text-align: center;" class="border-b border-black" value="{{ old('vital_signs_weight_2', $requestDocument->vital_signs_weight) }}"></p>
                </div>
            </div>
            <p class="font-semibold">Remark/s:</p>
            <div class="border border-gray-300 p-2 h-28 mb-4">
                <div class="col-span-3">
                    <textarea id="medical_cert_remarks" name="medical_cert_remarks" class="w-full border-black" rows="5">{{ old('medical_cert_remarks', trim($requestDocument->medical_cert_remarks)) }}</textarea>
                </div>
            </div>
            <p class="text-sm text-black">Note: Please check the original copy of med cert before accepting the photocopied med cert. This medical certificate is <span class="font-bold underline">not to be used</span> outside school purposes or medico-legal purposes.</p>
            <p class="text-sm">This certificate is issued upon the request of <input type="text" id="request_of" name="request_of" style="width: 350px; text-align: center;" class="border-b border-black" value="{{ old('request_of', $requestDocument->request_of) }}"> for your reference.</p>
            <div class="text-right mt-4 mr-5">
                <p class="font-semibold">Dr. Michael S. Oliveros</p>
                <p class="text-xs">Lic #: <input type="text" id="doc_lic_no" name="doc_lic_no" style="width: 110px; text-align: center;" class="border-b border-black" value="{{ old('doc_lic_no', $requestDocument->doc_lic_no) }}"></p>
                <p class=" text-xs">PTR #: <input type="text" id="doc_prt_no" name="doc_prt_no" style="width: 105px; text-align: center;" class="border-b border-black" value="{{ old('doc_prt_no', $requestDocument->doc_prt_no) }}"></p>
            </div>
            <div class="flex justify-end mt-6">
                <button type="submit" class="bg-green-500 text-white px-6 py-2 rounded">Update</button>
            </div>
        </div>
    </form>
    @endif
</div>

<style>
    #complaints_diagnosis,
    #medical_cert_remarks,
    #complaints,
    #pertinent_exam,
    #assessment,
    #plan,
    #nursing_intervention {
        resize: none;
    }

    .image-preview-wrapper {
        position: relative;
        display: inline-block;
    }

    .image-preview {
        width: 100%;
        height: 150px;
        object-fit: cover;
        border-radius: 8px;
        border: 2px solid #e5e7eb;
    }

    .remove-image-btn,
    .remove-existing-image-btn {
        position: absolute;
        top: 5px;
        right: 5px;
        background-color: #ef4444;
        color: white;
        border: none;
        border-radius: 50%;
        width: 25px;
        height: 25px;
        cursor: pointer;
        font-size: 14px;
        line-height: 1;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .remove-image-btn:hover,
    .remove-existing-image-btn:hover {
        background-color: #dc2626;
    }

    .image-size-error {
        color: #ef4444;
        font-size: 12px;
        margin-top: 4px;
    }
</style>

<script>
    // Track removed existing images
    let removedImages = [];

    function removeExistingImage(index) {
        if (confirm('Are you sure you want to remove this image?')) {
            // Add to removed list
            removedImages.push(index);
            document.getElementById('removed_images').value = JSON.stringify(removedImages);

            // Remove from DOM
            const imageWrapper = document.querySelector(`[data-image-index="${index}"]`);
            if (imageWrapper) {
                imageWrapper.remove();
            }
        }
    }

    document.addEventListener('DOMContentLoaded', function() {
        // Image Upload Preview and Validation for new images
        const imageInput = document.getElementById('consultation_images');
        const previewContainer = document.getElementById('image_preview_container');
        const maxFileSize = 5 * 1024 * 1024; // 5MB in bytes
        let selectedFiles = [];

        if (imageInput) {
            imageInput.addEventListener('change', function(e) {
                const files = Array.from(e.target.files);
                previewContainer.innerHTML = ''; // Clear previous previews
                selectedFiles = []; // Reset selected files

                // Create a new FileList to store valid files
                const dataTransfer = new DataTransfer();

                files.forEach((file, index) => {
                    // Validate file size
                    if (file.size > maxFileSize) {
                        const errorDiv = document.createElement('div');
                        errorDiv.className = 'col-span-3 image-size-error';
                        errorDiv.textContent = `Error: ${file.name} exceeds 5MB limit (${(file.size / 1024 / 1024).toFixed(2)}MB)`;
                        previewContainer.appendChild(errorDiv);
                        return; // Skip this file
                    }

                    // Add valid file to the list
                    selectedFiles.push(file);
                    dataTransfer.items.add(file);

                    // Create preview
                    const reader = new FileReader();
                    reader.onload = function(event) {
                        const wrapper = document.createElement('div');
                        wrapper.className = 'image-preview-wrapper';

                        const img = document.createElement('img');
                        img.src = event.target.result;
                        img.className = 'image-preview';
                        img.alt = file.name;

                        const removeBtn = document.createElement('button');
                        removeBtn.className = 'remove-image-btn';
                        removeBtn.innerHTML = '×';
                        removeBtn.type = 'button';
                        removeBtn.onclick = function() {
                            // Remove from selectedFiles array
                            const fileIndex = selectedFiles.indexOf(file);
                            if (fileIndex > -1) {
                                selectedFiles.splice(fileIndex, 1);
                            }

                            // Update the file input
                            const newDataTransfer = new DataTransfer();
                            selectedFiles.forEach(f => newDataTransfer.items.add(f));
                            imageInput.files = newDataTransfer.files;

                            // Remove preview
                            wrapper.remove();

                            // Show message if no images
                            if (selectedFiles.length === 0) {
                                previewContainer.innerHTML = '<p class="text-gray-500 col-span-3">No new images selected</p>';
                            }
                        };

                        const fileInfo = document.createElement('small');
                        fileInfo.className = 'text-gray-600 block mt-1';
                        fileInfo.textContent = `${file.name} (${(file.size / 1024 / 1024).toFixed(2)}MB)`;

                        wrapper.appendChild(img);
                        wrapper.appendChild(removeBtn);
                        wrapper.appendChild(fileInfo);
                        previewContainer.appendChild(wrapper);
                    };

                    reader.readAsDataURL(file);
                });

                // Update the file input with only valid files
                imageInput.files = dataTransfer.files;

                // Show message if no valid files
                if (selectedFiles.length === 0 && files.length > 0) {
                    const noValidFiles = document.createElement('p');
                    noValidFiles.className = 'text-red-500 col-span-3';
                    noValidFiles.textContent = 'No valid images selected. All files exceeded 5MB limit.';
                    previewContainer.appendChild(noValidFiles);
                }
            });
        }
    });
</script>
@endsection