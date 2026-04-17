@extends('layouts.app')
@section('title')
{{__('messages.request.edit_request')}}
@endsection
@section('content')
@php
$documentModule = request('module', $requestDocument->document_type === 'consultation_form' ? 'consultation' : 'certificate');
$indexRoute = isRole('clinic_admin') ? route('document-issuances.index') :
(isRole('staff') ? route('staff.document-issuances.index') :
(isRole('doctor') ? route('doctors.document-issuances.index') : route('document-issuances.index')));
$indexUrlWithModule = $indexRoute . '?module=' . $documentModule;
@endphp
<div class="p-4">
    <div class="flex justify-between items-center mb-4">
        <h1 class="text-lg font-bold">
            @if($requestDocument->document_type === 'consultation_form')
            Update Consultation Form
            @elseif($requestDocument->document_type === 'medical_certificate')
            Update Medical Certificate
            @else
            {{ __('messages.request.edit_request') }}
            @endif
        </h1>
        <a href="{{ 
            request('patient_id') ? 
                (isRole('clinic_admin') ? route('patients.showMyHistory', ['patient' => request('patient_id')]) : 
                (isRole('staff') ? route('staff.patients.showMyHistory', ['patient' => request('patient_id')]) : 
                (isRole('doctor') ? route('doctors.patients.showMyHistory', ['patient' => request('patient_id')]) : 
                route('patients.showMyHistory', ['patient' => request('patient_id')])))) :
                $indexUrlWithModule
        }}" class="bg-blue-500 text-white px-4 py-2 rounded">Back</a>
    </div>

    @if ($requestDocument->document_type == 'consultation_form')
    @php
    $effectiveYearLevelId = (int) old('year_level_id', $user->year_level_id ?? 0);
    $isStudentType = $effectiveYearLevelId >= 1 && $effectiveYearLevelId <= 6;
        $isFacultyType=$effectiveYearLevelId===7;
        $isStaffType=$effectiveYearLevelId===8;
        @endphp
        <form action="{{ 
        isRole('clinic_admin') ? route('document-issuances.update', $requestDocument) : 
        (isRole('staff') ? route('staff.document-issuances.update', $requestDocument) : 
        (isRole('doctor') ? route('doctors.document-issuances.update', $requestDocument) : route('document-issuances.update', $requestDocument)))
    }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        <!-- Hidden field to carry patient_id from query parameter -->
        @if(request('patient_id'))
        <input type="hidden" name="redirect_patient_id" value="{{ request('patient_id') }}">
        @endif
        <input type="hidden" name="redirect_module" value="{{ $documentModule }}">

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


            <!-- Campus Field (for Students only) -->
            <div class="col-span-1" id="campus_field" style="display: {{ $isStudentType ? 'block' : 'none' }};">
                <label class="block text-xs" for="campus_id">CAMPUS</label>
                <select id="campus_id" name="campus_id" class="w-full border-b border-black">
                    <option value="">Select Campus</option>
                    @foreach($campuses as $campus)
                    <option value="{{ $campus->id }}" {{ old('campus_id', $requestDocument->campus_id ?? $user->campus_id ?? '') == $campus->id ? 'selected' : '' }}>
                        {{ $campus->campus_name }}
                    </option>
                    @endforeach
                </select>
            </div>

            <!-- College Field (for Students and Faculty) -->
            <div class="col-span-1" id="college_field" style="display: {{ ($isStudentType || $isFacultyType) ? 'block' : 'none' }};">
                <label class="block text-xs" for="college_id">COLLEGE</label>
                <select id="college_id" name="college_id" class="w-full border-b border-black">
                    <option value="">Select College</option>
                    @foreach($colleges as $college)
                    <option value="{{ $college->id }}" {{ old('college_id', $requestDocument->college_id ?? $user->college_id ?? '') == $college->id ? 'selected' : '' }}>
                        {{ $college->college_name }}
                    </option>
                    @endforeach
                </select>
            </div>

            <!-- Course & Year Field (for Students only) -->
            <div class="col-span-1" id="course_year_field" style="display: {{ $isStudentType ? 'block' : 'none' }};">
                <label class="block text-xs" for="course_year">COURSE & YEAR</label>
                <div class="grid grid-cols-2 gap-2">
                    <select id="course_id" name="course_id" class="w-full border-b border-black">
                        <option value="">Select Course</option>
                        @foreach($courses as $course)
                        <option value="{{ $course->id }}" {{ old('course_id', $requestDocument->course_id ?? $user->course_id ?? '') == $course->id ? 'selected' : '' }}>
                            {{ $course->course_name }}
                        </option>
                        @endforeach
                    </select>
                    <select id="year_level_id" name="year_level_id" class="w-full border-b border-black">
                        <option value="">Select Year Level</option>
                        @foreach($yearLevels as $level)
                        <option value="{{ $level->id }}" {{ old('year_level_id', $requestDocument->year_level_id ?? $user->year_level_id ?? '') == $level->id ? 'selected' : '' }}>
                            {{ $level->year_level_name }}
                        </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <!-- Department Field (for Faculty only) -->
            <div class="col-span-1" id="department_field" style="display: {{ $isFacultyType ? 'block' : 'none' }};">
                <label class="block text-xs" for="department_id">DEPARTMENT</label>
                <select id="department_id" name="department_id" class="w-full border-b border-black">
                    <option value="">Select Department</option>
                    @foreach($departments as $department)
                    <option value="{{ $department->id }}" {{ old('department_id', $requestDocument->department_id ?? $user->department_id ?? '') == $department->id ? 'selected' : '' }}>
                        {{ $department->department_name }}
                    </option>
                    @endforeach
                </select>
            </div>

            <!-- Office Field (for Staff only) -->
            <div class="col-span-1" id="office_field" style="display: {{ $isStaffType ? 'block' : 'none' }};">
                <label class="block text-xs" for="office_id">OFFICE</label>
                <select id="office_id" name="office_id" class="w-full border-b border-black">
                    <option value="">Select Office</option>
                    @foreach($offices as $office)
                    <option value="{{ $office->id }}" {{ old('office_id', $requestDocument->office_id ?? $user->office_id ?? '') == $office->id ? 'selected' : '' }}>
                        {{ $office->office_name }}
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
                <label class="block text-xs" for="requested_at">CONSULTATION DATE<span class="text-red-500">*</span></label>
                <input type="date" id="requested_at" name="requested_at" class="w-full border-b border-black" max="{{ date('Y-m-d') }}" value="{{ old('requested_at', $requestDocument->requested_at->format('Y-m-d')) }}" required>
            </div>
            <div class="col-span-3">
                <label class="block text-xs" for="complaints">Complaint/s:</label>
                <textarea id="complaints" name="complaints" class="w-full border-b border-black auto-resize-textarea" rows="2">{{ old('complaints', $requestDocument->complaints) }}</textarea>
            </div>
            <div class="col-span-1"></div>
            <div class="col-span-3">
                <textarea id="note" name="note" class="w-full border-b border-black auto-resize-textarea" rows="3">{{ old('note', $requestDocument->note) }}</textarea>
            </div>
        </div>
        <!-- Subjective Complaints -->
        <div class="grid grid-cols-4 gap-2 py-2">
            <div class="col-span-1">
                <label class="block text-red-500 font-bold">S</label>
                <label class="block text-xs">(Subjective Data)</label>
            </div>
            <div class="col-span-3">
                <div class="grid grid-cols-2 gap-2">
                    <div class="col-span-1">
                        <label class="block text-xs" for="vaccination_id">COVID Vaccination<span class="text-red-500">*</span></label>
                        <select id="vaccination_id" name="vaccination_id" class="w-full border-b border-black" required>
                            <option value="">Select Vaccination Status</option>
                            @foreach($vaccinations as $vaccination)
                            <option value="{{ $vaccination->id }}" {{ old('vaccination_id', $requestDocument->covid_vaccination ?? '') == $vaccination->vaccination_status ? 'selected' : '' }}>
                                {{ $vaccination->vaccination_status }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-span-1">
                        <label class="block text-xs" for="comorbidities_input">Comorbidities</label>
                        <div class="relative">
                            <input type="hidden"
                                name="comorbidities_custom"
                                id="comorbidities_custom"
                                value="{{ old('comorbidities_custom', $requestDocument->comorbidities) }}">
                            <div id="comorbidities_selected_list" class="comorbidities-selected-list"></div>
                            <div class="flex gap-2 mt-1">
                                <input type="text"
                                    id="comorbidities_input"
                                    list="comorbidities_list"
                                    class="w-full border-b border-black"
                                    placeholder="Type and press Enter"
                                    autocomplete="off">
                                <button type="button" id="add_comorbidity_btn" class="px-3 py-1 bg-blue-500 text-white text-xs rounded hover:bg-blue-600">Add</button>
                            </div>
                            <datalist id="comorbidities_list">
                                <option value="None">
                                    @foreach($diagnoses as $diagnose)
                                <option value="{{ $diagnose->diagnoses }}">
                                    @endforeach
                            </datalist>
                        </div>
                    </div>
                    <div class="col-span-1">
                        <label class="block text-xs" for="allergies">Allergies<span class="text-red-500">*</span></label>
                        <input type="text" id="allergies" name="allergies" class="w-full border-b border-black" value="{{ old('allergies', $requestDocument->allergies) }}" required>
                    </div>
                    <div class="col-span-1">
                        <label class="block text-xs" for="admissions_surgeries">Pertinent Admissions or Surgeries</label>
                        <input type="text" id="admissions_surgeries" name="admissions_surgeries" class="w-full border-b border-black" value="{{ old('admissions_surgeries', $requestDocument->admissions_surgeries) }}">
                    </div>
                    <div class="col-span-1">
                        <label class="block text-xs" for="maintenance">Maintenance<span class="text-red-500">*</span></label>
                        <input type="text" id="maintenance" name="maintenance" class="w-full border-b border-black" value="{{ old('maintenance', $requestDocument->maintenance) }}" required>
                    </div>
                    <div class="col-span-1">
                        <label class="block text-xs" for="pregnancy_status">Pregnant or Not?<span class="text-red-500">*</span></label>
                        <input type="text" id="pregnancy_status" name="pregnancy_status" class="w-full border-b border-black" value="{{ old('pregnancy_status', $requestDocument->pregnancy_status) }}" required>
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
                        <input type="text" id="vital_signs_bp" name="vital_signs_bp" class="w-full border-b border-black" placeholder="mmHg" value="{{ old('vital_signs_bp', $requestDocument->vital_signs_bp) }}">
                    </div>
                    <div class="col-span-1">
                        <label class="block text-xs" for="vital_signs_pr">PR</label>
                        <input type="text" id="vital_signs_pr" name="vital_signs_pr" class="w-full border-b border-black" placeholder="bpm" value="{{ old('vital_signs_pr', $requestDocument->vital_signs_pr) }}">
                    </div>
                    <div class="col-span-1">
                        <label class="block text-xs" for="vital_signs_temp">Temp</label>
                        <input type="text" id="vital_signs_temp" name="vital_signs_temp" class="w-full border-b border-black" placeholder="°C" value="{{ old('vital_signs_temp', $requestDocument->vital_signs_temp) }}">
                    </div>
                    <div class="col-span-1">
                        <label class="block text-xs" for="vital_signs_rr">RR</label>
                        <input type="text" id="vital_signs_rr" name="vital_signs_rr" class="w-full border-b border-black" placeholder="cycles/min" value="{{ old('vital_signs_rr', $requestDocument->vital_signs_rr) }}">
                    </div>
                    <div class="col-span-1">
                        <label class="block text-xs" for="vital_signs_o2_sat">O2 Sat</label>
                        <input type="text" id="vital_signs_o2_sat" name="vital_signs_o2_sat" class="w-full border-b border-black" placeholder="%" value="{{ old('vital_signs_o2_sat', $requestDocument->vital_signs_o2_sat) }}">
                    </div>
                    <div class="col-span-1">
                        <label class="block text-xs" for="vital_signs_weight">Weight (kg)</label>
                        <input type="text" id="vital_signs_weight" name="vital_signs_weight" class="w-full border-b border-black" placeholder="kg" value="{{ old('vital_signs_weight', $requestDocument->vital_signs_weight) }}">
                    </div>
                    <div class="col-span-1">
                        <label class="block text-xs" for="vital_signs_height">Height (cm)</label>
                        <input type="text" id="vital_signs_height" name="vital_signs_height" class="w-full border-b border-black" placeholder="cm" value="{{ old('vital_signs_height', $requestDocument->vital_signs_height) }}">
                    </div>
                </div>
                <div class="col-span-5">
                    <label class="block text-xs" for="pertinent_exam">PERTINENT EXAM</label>
                    <textarea id="pertinent_exam" name="pertinent_exam" class="w-full border-b border-black auto-resize-textarea" rows="5">{{ old('pertinent_exam', $requestDocument->pertinent_exam) }}</textarea>
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
                <textarea id="assessment" name="assessment" class="w-full border-b border-black auto-resize-textarea" rows="5">{{ old('assessment', $requestDocument->assessment) }}</textarea>
            </div>
        </div>
        <!-- Plan -->
        <div class="grid grid-cols-4 gap-2 py-2">
            <div class="col-span-1">
                <label class="block text-red-500 font-bold">P</label>
                <label class="block text-xs">(Plan)</label>
            </div>
            <div class="col-span-3">
                <textarea id="plan" name="plan" class="w-full border-b border-black auto-resize-textarea" rows="5">{{ old('plan', $requestDocument->plan) }}</textarea>

                <!-- Medicine Selection for Plan -->
                <div class="mt-3">
                    <label class="block text-xs font-semibold mb-2">Add Medicines to Plan:</label>
                    <button type="button" id="add_plan_medicine_btn" class="bg-green-500 text-white px-3 py-1 rounded text-sm">
                        <i class="fas fa-plus"></i> Add Medicine
                    </button>
                    <div id="plan_medicines_container" class="mt-2 space-y-2"></div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-4 gap-2 py-2">
            <div class="col-span-1">
                <label class="block">Consult Mode<span class="text-red-500">*</span></label>
            </div>
            <div class="col-span-3">
                <select id="consult_mode" name="consult_mode" class="w-full border-b border-black" required>
                    <option value="">Select Consultation Mode</option>
                    <option value="physical" {{ old('consult_mode', $requestDocument->consult_mode) == 'physical' ? 'selected' : '' }}>Physical</option>
                    <option value="virtual" {{ old('consult_mode', $requestDocument->consult_mode) == 'virtual' ? 'selected' : '' }}>Virtual</option>
                </select>
            </div>
        </div>

        <div class="grid grid-cols-4 gap-2 py-2">
            <div class="col-span-1">
                <label class="block">Nursing Intervention<span class="text-red-500">*</span></label>
            </div>
            <div class="col-span-3">
                <textarea id="nursing_intervention" name="nursing_intervention" class="w-full border-b border-black auto-resize-textarea" rows="5" required>{{ old('nursing_intervention', $requestDocument->nursing_intervention) }}</textarea>

                <!-- Medicine Selection for Nursing Intervention -->
                <div class="mt-3">
                    <label class="block text-xs font-semibold mb-2">Add Medicines to Nursing Intervention:</label>
                    <button type="button" id="add_nursing_medicine_btn" class="bg-green-500 text-white px-3 py-1 rounded text-sm">
                        <i class="fas fa-plus"></i> Add Medicine
                    </button>
                    <div id="nursing_medicines_container" class="mt-2 space-y-2"></div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-4 gap-2 py-2">
            <div class="col-span-1">
                <label class="block">Nursing In-charged</label>
            </div>
            <div class="col-span-3">
                @if(auth()->user()->type == \App\Models\User::ADMIN || auth()->user()->type == \App\Models\User::DOCTOR)
                <!-- Admin and Doctor can select the nursing in-charged -->
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

                    <!-- Drag & Drop Zone -->
                    <div id="drop_zone" class="border-2 border-dashed border-gray-300 rounded-lg p-6 text-center mb-4 transition-all hover:border-blue-400 hover:bg-blue-50">
                        <i class="fas fa-cloud-upload-alt text-4xl text-gray-400 mb-2"></i>
                        <p class="text-gray-600 font-semibold mb-1">Drag & Drop Images Here</p>
                        <p class="text-gray-500 text-sm mb-3">or</p>
                        <label for="consultation_images" class="bg-blue-500 text-white px-4 py-2 rounded cursor-pointer hover:bg-blue-600 inline-block">
                            <i class="fas fa-folder-open"></i> Browse Files
                        </label>
                        <input type="file" id="consultation_images" name="consultation_images[]"
                            class="hidden"
                            accept="image/jpeg,image/png,image/jpg,image/gif"
                            multiple>
                        <p class="text-gray-500 text-xs mt-3">Supported: JPEG, PNG, JPG, GIF (Max 5MB each)</p>
                    </div>

                    <!-- Image URL Input -->
                    <div class="mb-4">
                        <label class="block text-sm font-semibold mb-2">
                            <i class="fas fa-link"></i> Or paste image URL to auto-download:
                        </label>
                        <div class="flex gap-2">
                            <input type="text" id="image_url_input"
                                class="flex-1 border border-gray-300 rounded px-3 py-2"
                                placeholder="https://example.com/image.jpg">
                            <button type="button" id="download_from_url_btn"
                                class="bg-green-500 text-white px-4 py-2 rounded hover:bg-green-600">
                                <i class="fas fa-download"></i> Download
                            </button>
                        </div>
                        <small class="text-gray-500">Paste an image URL and click Download to add it to your uploads</small>
                    </div>

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
        @php
        $doctorOptions = collect($availableDoctors ?? [])->filter(function ($doctor) {
        return $doctor->doctor !== null;
        })->map(function ($doctor) {
        return [
        'id' => $doctor->id,
        'name' => trim(($doctor->first_name ?? '') . ' ' . ($doctor->last_name ?? '')),
        'lic_no' => (string) ($doctor->doctor->prc_license_number ?? ''),
        'ptr_no' => (string) ($doctor->doctor->ptr_number ?? ''),
        ];
        })->values();

        $currentLicNo = (string) old('doc_lic_no', $requestDocument->doc_lic_no ?? '');
        $currentPtrNo = (string) old('doc_prt_no', $requestDocument->doc_prt_no ?? '');

        $matchedDoctor = $doctorOptions->first(function ($doctorOption) use ($currentLicNo, $currentPtrNo) {
        return ($currentLicNo !== '' && (string) $doctorOption['lic_no'] === $currentLicNo)
        || ($currentPtrNo !== '' && (string) $doctorOption['ptr_no'] === $currentPtrNo);
        });

        $selectedDoctorId = old('doctor_user_id', $matchedDoctor['id'] ?? '');
        @endphp
        <form action="{{ 
        isRole('clinic_admin') ? route('document-issuances.update', $requestDocument) : 
        (isRole('staff') ? route('staff.document-issuances.update', $requestDocument) : 
        (isRole('doctor') ? route('doctors.document-issuances.update', $requestDocument) : route('document-issuances.update', $requestDocument)))
    }}" method="POST">
            @csrf
            @method('PUT')

            <!-- Hidden field to carry patient_id from query parameter -->
            @if(request('patient_id'))
            <input type="hidden" name="redirect_patient_id" value="{{ request('patient_id') }}">
            @endif
            <input type="hidden" name="redirect_module" value="{{ $documentModule }}">

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
                        @php
                        $examinedOnRaw = old('examined_on', $requestDocument->examined_on ?? '');
                        if ($examinedOnRaw) {
                        if (str_ends_with($examinedOnRaw, '|range')) {
                        $parts = explode('|', $examinedOnRaw);
                        $examinedOnDisplay = \Carbon\Carbon::parse($parts[0])->format('m/d/Y') . ' - ' . \Carbon\Carbon::parse($parts[1])->format('m/d/Y');
                        } elseif (str_ends_with($examinedOnRaw, '|multiple')) {
                        $datesStr = explode('|', $examinedOnRaw)[0];
                        $examinedOnDisplay = implode(', ', array_map(fn($d) => \Carbon\Carbon::parse(trim($d))->format('m/d/Y'), explode(',', $datesStr)));
                        } elseif (str_contains($examinedOnRaw, ',')) {
                        $examinedOnDisplay = implode(', ', array_map(fn($d) => \Carbon\Carbon::parse(trim($d))->format('m/d/Y'), explode(',', $examinedOnRaw)));
                        } else {
                        $examinedOnDisplay = \Carbon\Carbon::parse($examinedOnRaw)->format('m/d/Y');
                        }
                        } else {
                        $examinedOnDisplay = '';
                        }
                        @endphp
                        , was seen and examined at my clinic on
                        <input type="text" id="examined_on_display" name="examined_on_display" style="width: 300px; text-align: center;" class="border-b border-black" placeholder="Click to select date(s)" value="{{ $examinedOnDisplay }}" readonly required>
                        <input type="hidden" id="examined_on" name="examined_on" value="{{ $examinedOnRaw }}">
                        <button type="button" id="open_date_selector" class="btn btn-sm btn-primary ml-2" style="padding: 2px 8px; font-size: 12px;">
                            <i class="fas fa-calendar-alt"></i> Select Dates
                        </button>
                        with the following
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
                    @if($doctorOptions->count() > 1)
                    <div class="inline-block text-left mb-2" style="min-width: 260px;">
                        <label for="medical_cert_doctor_id" class="block text-xs font-semibold">Attending Doctor<span class="text-red-500">*</span></label>
                        <select id="medical_cert_doctor_id" name="doctor_user_id" class="w-full border-b border-black" required>
                            <option value="" disabled {{ $selectedDoctorId ? '' : 'selected' }}>Select Doctor</option>
                            @foreach($doctorOptions as $doctorOption)
                            <option
                                value="{{ $doctorOption['id'] }}"
                                data-name="{{ $doctorOption['name'] }}"
                                data-lic="{{ $doctorOption['lic_no'] }}"
                                data-ptr="{{ $doctorOption['ptr_no'] }}"
                                {{ (string) $selectedDoctorId === (string) $doctorOption['id'] ? 'selected' : '' }}>
                                {{ $doctorOption['name'] }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                    <p class="font-semibold" id="doctor_display_name"></p>
                    @else
                    @php
                    $singleDoctor = $doctorOptions->count() === 1 ? $doctorOptions->first() : null;
                    @endphp
                    <input
                        type="hidden"
                        id="medical_cert_doctor_id"
                        name="doctor_user_id"
                        value="{{ $singleDoctor['id'] ?? '' }}"
                        data-name="{{ $singleDoctor['name'] ?? '' }}"
                        data-lic="{{ $singleDoctor['lic_no'] ?? '' }}"
                        data-ptr="{{ $singleDoctor['ptr_no'] ?? '' }}">
                    <p class="font-semibold" id="doctor_display_name">{{ $singleDoctor ? 'Dr. ' . $singleDoctor['name'] : 'Doctor' }}</p>
                    @endif
                    <p class="text-xs">Lic #: <input type="text" id="doc_lic_no" name="doc_lic_no" style="width: 110px; text-align: center;" class="border-b border-black" value="{{ old('doc_lic_no', $requestDocument->doc_lic_no) }}"></p>
                    <p class=" text-xs">PTR #: <input type="text" id="doc_prt_no" name="doc_prt_no" style="width: 105px; text-align: center;" class="border-b border-black" value="{{ old('doc_prt_no', $requestDocument->doc_prt_no) }}"></p>
                </div>
                <div class="flex justify-end mt-6">
                    <button type="submit" class="bg-green-500 text-white px-6 py-2 rounded">Update</button>
                </div>
            </div>
        </form>

        <!-- Date Selector Modal (Medical Certificate) -->
        <div id="date_selector_modal" class="modal fade" tabindex="-1" aria-labelledby="dateSelectorModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="dateSelectorModalLabel">Select Examination Date(s)</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Select Date Type:</label>
                            <div class="btn-group w-100" role="group">
                                <input type="radio" class="btn-check" name="date_type" id="single_date_radio" value="single" checked>
                                <label class="btn btn-outline-primary" for="single_date_radio">Single Date</label>
                                <input type="radio" class="btn-check" name="date_type" id="date_range_radio" value="range">
                                <label class="btn btn-outline-primary" for="date_range_radio">Date Range</label>
                                <input type="radio" class="btn-check" name="date_type" id="multiple_dates_radio" value="multiple">
                                <label class="btn btn-outline-primary" for="multiple_dates_radio">Multiple Dates</label>
                            </div>
                        </div>
                        <!-- Single Date -->
                        <div id="single_date_section" class="date-section">
                            <label for="single_date_input" class="form-label">Select Date:</label>
                            <input type="date" id="single_date_input" class="form-control" max="{{ date('Y-m-d') }}" value="{{ date('Y-m-d') }}">
                        </div>
                        <!-- Date Range -->
                        <div id="date_range_section" class="date-section" style="display: none;">
                            <div class="row">
                                <div class="col-md-6">
                                    <label for="start_date_input" class="form-label">Start Date:</label>
                                    <input type="date" id="start_date_input" class="form-control" max="{{ date('Y-m-d') }}">
                                </div>
                                <div class="col-md-6">
                                    <label for="end_date_input" class="form-label">End Date:</label>
                                    <input type="date" id="end_date_input" class="form-control" max="{{ date('Y-m-d') }}">
                                </div>
                            </div>
                        </div>
                        <!-- Multiple Dates -->
                        <div id="multiple_dates_section" class="date-section" style="display: none;">
                            <label for="add_date_input" class="form-label">Add Date:</label>
                            <div class="input-group mb-3">
                                <input type="date" id="add_date_input" class="form-control" max="{{ date('Y-m-d') }}">
                                <button type="button" id="add_date_btn" class="btn btn-success">
                                    <i class="fas fa-plus"></i> Add
                                </button>
                            </div>
                            <div id="selected_dates_list" class="border rounded p-3" style="min-height: 100px; max-height: 200px; overflow-y: auto;">
                                <p class="text-muted text-center mb-0">No dates selected</p>
                            </div>
                        </div>
                        <!-- Preview -->
                        <div class="mt-4 p-3 bg-light rounded">
                            <label class="form-label fw-bold">Preview:</label>
                            <p id="date_preview" class="mb-0 text-primary">No date selected</p>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="button" id="apply_dates_btn" class="btn btn-primary">Apply Dates</button>
                    </div>
                </div>
            </div>
        </div>
        @endif
</div>

<style>
    .auto-resize-textarea {
        resize: none;
        overflow: hidden;
    }

    #complaints_diagnosis,
    #medical_cert_remarks {
        resize: none;
    }

    .selected-date-item {
        display: inline-block;
        background: #e7f3ff;
        border: 1px solid #2196F3;
        border-radius: 4px;
        padding: 5px 10px;
        margin: 3px;
        font-size: 14px;
    }

    .selected-date-item .remove-date {
        margin-left: 8px;
        color: #d32f2f;
        cursor: pointer;
        font-weight: bold;
    }

    .selected-date-item .remove-date:hover {
        color: #b71c1c;
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

    /* Medicine selection styles */
    .medicine-row {
        display: grid;
        grid-template-columns: 2fr 1.5fr 1fr 2fr auto auto;
        gap: 0.5rem;
        padding: 0.5rem;
        background-color: #f9fafb;
        border-radius: 0.375rem;
        align-items: center;
    }

    .existing-medicine-row {
        background-color: #e0f2fe;
        border: 1px solid #0ea5e9;
    }

    .existing-medicine-badge {
        display: inline-flex;
        align-items: center;
        gap: 0.25rem;
        padding: 0.25rem 0.5rem;
        background-color: #10b981;
        color: white;
        border-radius: 0.25rem;
        font-size: 0.75rem;
        font-weight: 600;
        white-space: nowrap;
    }

    .medicine-row select,
    .medicine-row input {
        padding: 0.375rem 0.5rem;
        border: 1px solid #d1d5db;
        border-radius: 0.25rem;
        font-size: 0.875rem;
    }

    .medicine-row select optgroup {
        font-weight: bold;
        font-style: normal;
        background-color: #e5e7eb;
    }

    .medicine-row select option {
        padding: 0.25rem;
    }

    .remove-medicine-btn {
        background-color: #ef4444;
        color: white;
        border: none;
        border-radius: 0.25rem;
        padding: 0.375rem 0.75rem;
        cursor: pointer;
        font-size: 0.875rem;
    }

    .remove-medicine-btn:hover {
        background-color: #dc2626;
    }

    .medicine-stock-info {
        font-size: 0.75rem;
        color: #6b7280;
        margin-top: 0.125rem;
    }

    .medicine-stock-warning {
        color: #ef4444;
        font-weight: 600;
    }

    .medicine-stock-error {
        color: #dc2626;
        font-weight: 700;
        background-color: #fee2e2;
        padding: 0.5rem;
        border-radius: 0.25rem;
        border-left: 4px solid #dc2626;
        grid-column: 1 / -1;
    }

    .comorbidities-selected-list {
        display: flex;
        flex-wrap: wrap;
        gap: 0.35rem;
        min-height: 1.75rem;
    }

    .comorbidity-chip {
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        background: #e0f2fe;
        border: 1px solid #bae6fd;
        color: #0c4a6e;
        padding: 0.2rem 0.55rem;
        border-radius: 999px;
        font-size: 0.75rem;
        line-height: 1.2;
    }

    .comorbidity-chip-remove {
        background: transparent;
        border: none;
        color: #0369a1;
        cursor: pointer;
        font-size: 0.75rem;
        padding: 0;
        line-height: 1;
    }

    /* Drag and Drop Zone Styles */
    #drop_zone {
        cursor: pointer;
    }

    #drop_zone.drag-over {
        border-color: #3b82f6;
        background-color: #dbeafe;
        transform: scale(1.02);
    }

    #drop_zone.drag-over i {
        color: #3b82f6;
        transform: scale(1.1);
    }

    .image-loading {
        position: relative;
        opacity: 0.6;
    }

    .image-loading::after {
        content: '';
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        width: 30px;
        height: 30px;
        border: 3px solid #f3f3f3;
        border-top: 3px solid #3b82f6;
        border-radius: 50%;
        animation: spin 1s linear infinite;
    }

    @keyframes spin {
        0% {
            transform: translate(-50%, -50%) rotate(0deg);
        }

        100% {
            transform: translate(-50%, -50%) rotate(360deg);
        }
    }

    .url-downloading {
        position: relative;
    }

    .url-downloading::after {
        content: 'Downloading...';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(59, 130, 246, 0.9);
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 12px;
        font-weight: bold;
        border-radius: 0.375rem;
    }
</style>

<script>
    // Track removed existing images
    let removedImages = [];
    const initialYearLevelId = @json((int) old('year_level_id', $user - > year_level_id ?? 0));

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

    // Function to update field visibility based on year_level_id
    function updateFieldsVisibility() {
        const selectedYearLevelId = document.getElementById('year_level_id')?.value;
        const yearLevelId = parseInt(selectedYearLevelId || initialYearLevelId || 0, 10);

        const campusField = document.getElementById('campus_field');
        const collegeField = document.getElementById('college_field');
        const courseYearField = document.getElementById('course_year_field');
        const departmentField = document.getElementById('department_field');
        const officeField = document.getElementById('office_field');

        // Hide all fields first
        if (campusField) campusField.style.display = 'none';
        if (collegeField) collegeField.style.display = 'none';
        if (courseYearField) courseYearField.style.display = 'none';
        if (departmentField) departmentField.style.display = 'none';
        if (officeField) officeField.style.display = 'none';

        if (yearLevelId === 7) {
            // Faculty: Show College and Department
            if (collegeField) collegeField.style.display = 'block';
            if (departmentField) departmentField.style.display = 'block';
        } else if (yearLevelId === 8) {
            // Staff: Show Office only
            if (officeField) officeField.style.display = 'block';
        } else if (yearLevelId === 9) {
            // Guest: Hide all additional fields
            // All fields are already hidden
        } else if (yearLevelId >= 1 && yearLevelId <= 6) {
            // Students: Show Campus, College, Course & Year
            if (campusField) campusField.style.display = 'block';
            if (collegeField) collegeField.style.display = 'block';
            if (courseYearField) courseYearField.style.display = 'block';
        }
    }

    document.addEventListener('DOMContentLoaded', function() {
        // Add event listener for year_level_id changes
        const yearLevelSelect = document.getElementById('year_level_id');
        if (yearLevelSelect) {
            if (!yearLevelSelect.value && initialYearLevelId > 0) {
                yearLevelSelect.value = String(initialYearLevelId);
            }
            yearLevelSelect.addEventListener('change', updateFieldsVisibility);
        }

        updateFieldsVisibility();

        // ==================== COMORBIDITIES MULTI-SELECT ====================
        const comorbiditiesInput = document.getElementById('comorbidities_input');
        const comorbiditiesHiddenInput = document.getElementById('comorbidities_custom');
        const comorbiditiesSelectedList = document.getElementById('comorbidities_selected_list');
        const addComorbidityBtn = document.getElementById('add_comorbidity_btn');
        let selectedComorbidities = [];

        function normalizeComorbidity(value) {
            return String(value || '').replace(/\s+/g, ' ').trim();
        }

        function updateComorbiditiesHiddenInput() {
            if (comorbiditiesHiddenInput) {
                comorbiditiesHiddenInput.value = selectedComorbidities.join(', ');
            }
        }

        function renderComorbidityChips() {
            if (!comorbiditiesSelectedList) {
                return;
            }

            comorbiditiesSelectedList.innerHTML = '';

            selectedComorbidities.forEach((item, index) => {
                const chip = document.createElement('span');
                chip.className = 'comorbidity-chip';
                chip.textContent = item;

                const removeBtn = document.createElement('button');
                removeBtn.type = 'button';
                removeBtn.className = 'comorbidity-chip-remove';
                removeBtn.setAttribute('aria-label', `Remove ${item}`);
                removeBtn.textContent = 'x';
                removeBtn.addEventListener('click', function() {
                    selectedComorbidities.splice(index, 1);
                    renderComorbidityChips();
                    updateComorbiditiesHiddenInput();
                });

                chip.appendChild(removeBtn);
                comorbiditiesSelectedList.appendChild(chip);
            });
        }

        function addComorbidity(value) {
            const normalized = normalizeComorbidity(value);

            if (!normalized) {
                return;
            }

            const duplicate = selectedComorbidities.some((item) => item.toLowerCase() === normalized.toLowerCase());
            if (duplicate) {
                if (comorbiditiesInput) {
                    comorbiditiesInput.value = '';
                }
                return;
            }

            selectedComorbidities.push(normalized);
            renderComorbidityChips();
            updateComorbiditiesHiddenInput();

            if (comorbiditiesInput) {
                comorbiditiesInput.value = '';
            }
        }

        function setComorbidities(value) {
            selectedComorbidities = [];

            const values = String(value || '')
                .split(',')
                .map((item) => normalizeComorbidity(item))
                .filter((item) => item !== '');

            values.forEach((item) => {
                const duplicate = selectedComorbidities.some((existingItem) => existingItem.toLowerCase() === item.toLowerCase());
                if (!duplicate) {
                    selectedComorbidities.push(item);
                }
            });

            renderComorbidityChips();
            updateComorbiditiesHiddenInput();
        }

        if (addComorbidityBtn) {
            addComorbidityBtn.addEventListener('click', function() {
                addComorbidity(comorbiditiesInput ? comorbiditiesInput.value : '');
            });
        }

        if (comorbiditiesInput) {
            comorbiditiesInput.addEventListener('keydown', function(e) {
                if (e.key === 'Enter' || e.key === ',') {
                    e.preventDefault();
                    addComorbidity(this.value);
                }
            });

            comorbiditiesInput.addEventListener('blur', function() {
                const pendingValue = normalizeComorbidity(this.value);
                if (pendingValue) {
                    addComorbidity(pendingValue);
                }
            });
        }

        setComorbidities(comorbiditiesHiddenInput ? comorbiditiesHiddenInput.value : '');


        // ==================== IMAGE UPLOAD WITH DRAG & DROP AND URL DOWNLOAD ====================
        const imageInput = document.getElementById('consultation_images');
        const previewContainer = document.getElementById('image_preview_container');
        const dropZone = document.getElementById('drop_zone');
        const imageUrlInput = document.getElementById('image_url_input');
        const downloadUrlBtn = document.getElementById('download_from_url_btn');
        const maxFileSize = 5 * 1024 * 1024; // 5MB in bytes
        let selectedFiles = [];

        // Function to validate and add files
        function processFiles(files) {
            const filesArray = Array.from(files);
            const dataTransfer = new DataTransfer();

            // Keep existing files
            selectedFiles.forEach(f => dataTransfer.items.add(f));

            filesArray.forEach((file) => {
                // Validate file type
                if (!file.type.match('image.*')) {
                    showError(`${file.name} is not a valid image file.`);
                    return;
                }

                // Validate file size
                if (file.size > maxFileSize) {
                    showError(`${file.name} exceeds 5MB limit (${(file.size / 1024 / 1024).toFixed(2)}MB)`);
                    return;
                }

                // Add valid file to the list
                selectedFiles.push(file);
                dataTransfer.items.add(file);

                // Create preview
                createImagePreview(file);
            });

            // Update the file input with all files
            imageInput.files = dataTransfer.files;
        }

        // Function to create image preview
        function createImagePreview(file) {
            const reader = new FileReader();
            reader.onload = function(event) {
                const wrapper = document.createElement('div');
                wrapper.className = 'image-preview-wrapper';
                wrapper.dataset.filename = file.name;

                const img = document.createElement('img');
                img.src = event.target.result;
                img.className = 'image-preview';
                img.alt = file.name;

                const removeBtn = document.createElement('button');
                removeBtn.className = 'remove-image-btn';
                removeBtn.innerHTML = '×';
                removeBtn.type = 'button';
                removeBtn.onclick = function() {
                    removeImage(file, wrapper);
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
        }

        // Function to remove image
        function removeImage(file, wrapper) {
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
        }

        // Function to show error
        function showError(message) {
            const errorDiv = document.createElement('div');
            errorDiv.className = 'col-span-3 image-size-error';
            errorDiv.textContent = `⚠️ ${message}`;
            previewContainer.appendChild(errorDiv);

            // Auto-remove error after 5 seconds
            setTimeout(() => {
                errorDiv.remove();
            }, 5000);
        }

        // File input change event
        if (imageInput) {
            imageInput.addEventListener('change', function(e) {
                processFiles(e.target.files);
            });
        }

        // ==================== DRAG & DROP FUNCTIONALITY ====================
        if (dropZone) {
            // Prevent default drag behaviors
            ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
                dropZone.addEventListener(eventName, preventDefaults, false);
                document.body.addEventListener(eventName, preventDefaults, false);
            });

            function preventDefaults(e) {
                e.preventDefault();
                e.stopPropagation();
            }

            // Highlight drop zone when item is dragged over it
            ['dragenter', 'dragover'].forEach(eventName => {
                dropZone.addEventListener(eventName, highlight, false);
            });

            ['dragleave', 'drop'].forEach(eventName => {
                dropZone.addEventListener(eventName, unhighlight, false);
            });

            function highlight(e) {
                dropZone.classList.add('drag-over');
            }

            function unhighlight(e) {
                dropZone.classList.remove('drag-over');
            }

            // Handle dropped files
            dropZone.addEventListener('drop', handleDrop, false);

            function handleDrop(e) {
                const dt = e.dataTransfer;
                const files = dt.files;

                processFiles(files);
            }

            // Click to browse
            dropZone.addEventListener('click', function(e) {
                if (e.target.id !== 'consultation_images' && !e.target.closest('label')) {
                    imageInput.click();
                }
            });
        }

        // ==================== IMAGE URL DOWNLOAD FUNCTIONALITY ====================
        if (downloadUrlBtn && imageUrlInput) {
            downloadUrlBtn.addEventListener('click', async function() {
                const imageUrl = imageUrlInput.value.trim();

                if (!imageUrl) {
                    alert('Please enter an image URL');
                    return;
                }

                // Validate URL format
                try {
                    new URL(imageUrl);
                } catch (e) {
                    alert('Please enter a valid URL');
                    return;
                }

                // Show loading state
                downloadUrlBtn.disabled = true;
                downloadUrlBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Downloading...';
                downloadUrlBtn.classList.add('url-downloading');

                try {
                    // Fetch the image
                    const response = await fetch(imageUrl);

                    if (!response.ok) {
                        throw new Error(`Failed to download image: ${response.statusText}`);
                    }

                    const blob = await response.blob();

                    // Validate if it's an image
                    if (!blob.type.match('image.*')) {
                        throw new Error('The URL does not point to a valid image file');
                    }

                    // Validate file size
                    if (blob.size > maxFileSize) {
                        throw new Error(`Image exceeds 5MB limit (${(blob.size / 1024 / 1024).toFixed(2)}MB)`);
                    }

                    // Extract filename from URL or generate one
                    let filename = imageUrl.split('/').pop().split('?')[0];
                    if (!filename || !filename.match(/\\.(jpg|jpeg|png|gif)$/i)) {
                        const ext = blob.type.split('/')[1];
                        filename = `downloaded_image_${Date.now()}.${ext}`;
                    }

                    // Create File object from blob
                    const file = new File([blob], filename, {
                        type: blob.type
                    });

                    // Add to selected files
                    selectedFiles.push(file);

                    // Update file input
                    const dataTransfer = new DataTransfer();
                    selectedFiles.forEach(f => dataTransfer.items.add(f));
                    imageInput.files = dataTransfer.files;

                    // Create preview
                    createImagePreview(file);

                    // Clear input
                    imageUrlInput.value = '';

                    // Show success message
                    const successDiv = document.createElement('div');
                    successDiv.className = 'col-span-3 text-green-600 font-semibold';
                    successDiv.innerHTML = `✓ Image downloaded successfully: ${filename}`;
                    previewContainer.appendChild(successDiv);

                    setTimeout(() => {
                        successDiv.remove();
                    }, 3000);

                } catch (error) {
                    console.error('Error downloading image:', error);
                    alert(`Error downloading image: ${error.message}`);
                } finally {
                    // Reset button state
                    downloadUrlBtn.disabled = false;
                    downloadUrlBtn.innerHTML = '<i class="fas fa-download"></i> Download';
                    downloadUrlBtn.classList.remove('url-downloading');
                }
            });

            // Allow Enter key to trigger download
            imageUrlInput.addEventListener('keypress', function(e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    downloadUrlBtn.click();
                }
            });
        }

        // ==================== AUTO-RESIZE TEXTAREA FUNCTIONALITY ====================

        function autoResizeTextarea(textarea) {
            textarea.style.height = 'auto';
            textarea.style.height = textarea.scrollHeight + 'px';
        }

        const autoResizeTextareas = document.querySelectorAll('.auto-resize-textarea');
        autoResizeTextareas.forEach(function(textarea) {
            textarea.addEventListener('input', function() {
                autoResizeTextarea(this);
            });
            autoResizeTextarea(textarea);
        });

        // ==================== MEDICAL CERTIFICATE DOCTOR FIELD SYNC ====================

        const doctorSelector = document.getElementById('medical_cert_doctor_id');
        const doctorDisplayName = document.getElementById('doctor_display_name');
        const doctorLicenseInput = document.getElementById('doc_lic_no');
        const doctorPtrInput = document.getElementById('doc_prt_no');

        const syncMedicalCertificateDoctorDetails = () => {
            if (!doctorSelector) {
                return;
            }

            let selectedDoctorName = '';
            let selectedDoctorLic = '';
            let selectedDoctorPtr = '';

            if (doctorSelector.tagName === 'SELECT') {
                const selectedOption = doctorSelector.options[doctorSelector.selectedIndex];

                if (!selectedOption || !selectedOption.value) {
                    if (doctorDisplayName) {
                        doctorDisplayName.textContent = '';
                    }
                    return;
                }

                selectedDoctorName = selectedOption.dataset.name || selectedOption.textContent.trim();
                selectedDoctorLic = selectedOption.dataset.lic || '';
                selectedDoctorPtr = selectedOption.dataset.ptr || '';
            } else {
                selectedDoctorName = doctorSelector.dataset.name || '';
                selectedDoctorLic = doctorSelector.dataset.lic || '';
                selectedDoctorPtr = doctorSelector.dataset.ptr || '';
            }

            if (doctorDisplayName) {
                doctorDisplayName.textContent = selectedDoctorName ? `Dr. ${selectedDoctorName}` : 'Doctor';
            }

            if (doctorLicenseInput && selectedDoctorLic !== '') {
                doctorLicenseInput.value = selectedDoctorLic;
            }

            if (doctorPtrInput && selectedDoctorPtr !== '') {
                doctorPtrInput.value = selectedDoctorPtr;
            }
        };

        if (doctorSelector) {
            if (doctorSelector.tagName === 'SELECT') {
                doctorSelector.addEventListener('change', syncMedicalCertificateDoctorDetails);
            }
            syncMedicalCertificateDoctorDetails();
        }

        // ==================== MEDICINE SELECTION FUNCTIONALITY ====================

        let medicinesData = [];

        async function fetchMedicines() {
            try {
                const response = await fetch('{{ route("medicines.by.category") }}');
                const result = await response.json();

                if (result.success) {
                    medicinesData = result.data;

                    // Also fetch ALL medicines (including out of stock) for existing medicine rows
                    await fetchAllMedicinesForExisting();

                    // Load existing medicines after medicine data is fetched
                    loadExistingMedicines();
                } else {
                    console.error('Error fetching medicines:', result.message);
                }
            } catch (error) {
                console.error('Error fetching medicines:', error);
            }
        }

        // Fetch all medicines including out of stock ones (for existing medicines only)
        let allMedicinesData = [];
        async function fetchAllMedicinesForExisting() {
            try {
                // We'll use the existing medicines data but also need to ensure
                // out-of-stock medicines that are currently used can be shown
                allMedicinesData = medicinesData; // Use same data structure
            } catch (error) {
                console.error('Error fetching all medicines:', error);
            }
        }

        fetchMedicines();

        // Load existing medicines from the database
        function loadExistingMedicines() {
            const existingMedicines = @json($existingMedicines ?? []);

            if (existingMedicines.length === 0) {
                return;
            }

            existingMedicines.forEach(medicine => {
                const type = medicine.used_for; // 'plan' or 'nursing'
                const counter = type === 'plan' ? planMedicineCounter++ : nursingMedicineCounter++;

                // Add medicine row with existing data
                addMedicineRow(type, counter, {
                    medicineId: medicine.medicine_id,
                    dosage: medicine.dosage,
                    quantity: medicine.quantity,
                    dosageInstructions: medicine.dosage_instructions,
                    isExisting: true, // Mark as existing medicine
                    existingQuantity: medicine.quantity // Store the original quantity used
                });
            });
        }

        let planMedicineCounter = 0;
        let nursingMedicineCounter = 0;

        const addPlanMedicineBtn = document.getElementById('add_plan_medicine_btn');
        const addNursingMedicineBtn = document.getElementById('add_nursing_medicine_btn');

        if (addPlanMedicineBtn) {
            addPlanMedicineBtn.addEventListener('click', function() {
                addMedicineRow('plan', planMedicineCounter++);
            });
        }

        if (addNursingMedicineBtn) {
            addNursingMedicineBtn.addEventListener('click', function() {
                addMedicineRow('nursing', nursingMedicineCounter++);
            });
        }

        // ==================== DATE SELECTOR FUNCTIONALITY (Medical Certificate) ====================
        const dateSelectorModalEl = document.getElementById('date_selector_modal');
        if (dateSelectorModalEl) {
            let selectedDatesArray = [];
            let currentDateType = 'single';

            const dateSelectorModal = new bootstrap.Modal(dateSelectorModalEl);
            const openDateSelectorBtn = document.getElementById('open_date_selector');
            const applyDatesBtn = document.getElementById('apply_dates_btn');
            const dateTypeRadios = document.querySelectorAll('input[name="date_type"]');

            // Pre-populate from existing examined_on value
            const existingExaminedOn = document.getElementById('examined_on').value;
            if (existingExaminedOn) {
                if (existingExaminedOn.endsWith('|range')) {
                    const parts = existingExaminedOn.split('|');
                    document.getElementById('start_date_input').value = parts[0];
                    document.getElementById('end_date_input').value = parts[1];
                    document.getElementById('date_range_radio').checked = true;
                    document.getElementById('single_date_section').style.display = 'none';
                    document.getElementById('date_range_section').style.display = 'block';
                    currentDateType = 'range';
                } else if (existingExaminedOn.endsWith('|multiple')) {
                    const datesStr = existingExaminedOn.split('|')[0];
                    selectedDatesArray = datesStr.split(',').map(d => d.trim()).filter(d => d);
                    document.getElementById('multiple_dates_radio').checked = true;
                    document.getElementById('single_date_section').style.display = 'none';
                    document.getElementById('multiple_dates_section').style.display = 'block';
                    currentDateType = 'multiple';
                    renderSelectedDates();
                } else if (existingExaminedOn.includes(',')) {
                    // Old comma-separated format without |multiple
                    selectedDatesArray = existingExaminedOn.split(',').map(d => d.trim()).filter(d => d);
                    document.getElementById('multiple_dates_radio').checked = true;
                    document.getElementById('single_date_section').style.display = 'none';
                    document.getElementById('multiple_dates_section').style.display = 'block';
                    currentDateType = 'multiple';
                    renderSelectedDates();
                } else {
                    document.getElementById('single_date_input').value = existingExaminedOn;
                    currentDateType = 'single';
                }
            }

            openDateSelectorBtn.addEventListener('click', function() {
                dateSelectorModal.show();
                updatePreview();
            });

            dateTypeRadios.forEach(radio => {
                radio.addEventListener('change', function() {
                    currentDateType = this.value;
                    document.getElementById('single_date_section').style.display = 'none';
                    document.getElementById('date_range_section').style.display = 'none';
                    document.getElementById('multiple_dates_section').style.display = 'none';
                    if (currentDateType === 'single') {
                        document.getElementById('single_date_section').style.display = 'block';
                    } else if (currentDateType === 'range') {
                        document.getElementById('date_range_section').style.display = 'block';
                    } else if (currentDateType === 'multiple') {
                        document.getElementById('multiple_dates_section').style.display = 'block';
                    }
                    updatePreview();
                });
            });

            document.getElementById('single_date_input').addEventListener('change', updatePreview);

            document.getElementById('start_date_input').addEventListener('change', function() {
                document.getElementById('end_date_input').min = this.value;
                updatePreview();
            });
            document.getElementById('end_date_input').addEventListener('change', updatePreview);

            document.getElementById('add_date_btn').addEventListener('click', function() {
                const dateInput = document.getElementById('add_date_input');
                const dateValue = dateInput.value;
                if (!dateValue) {
                    alert('Please select a date');
                    return;
                }
                if (selectedDatesArray.includes(dateValue)) {
                    alert('This date is already added');
                    return;
                }
                selectedDatesArray.push(dateValue);
                selectedDatesArray.sort();
                renderSelectedDates();
                updatePreview();
                dateInput.value = '';
            });

            function renderSelectedDates() {
                const listContainer = document.getElementById('selected_dates_list');
                if (selectedDatesArray.length === 0) {
                    listContainer.innerHTML = '<p class="text-muted text-center mb-0">No dates selected</p>';
                    return;
                }
                listContainer.innerHTML = '';
                selectedDatesArray.forEach((date, index) => {
                    const dateItem = document.createElement('span');
                    dateItem.className = 'selected-date-item';
                    dateItem.innerHTML = `${formatDateDisplay(date)}<span class="remove-date" data-index="${index}">&times;</span>`;
                    listContainer.appendChild(dateItem);
                });
                document.querySelectorAll('.remove-date').forEach(btn => {
                    btn.addEventListener('click', function() {
                        selectedDatesArray.splice(parseInt(this.dataset.index), 1);
                        renderSelectedDates();
                        updatePreview();
                    });
                });
            }

            function updatePreview() {
                const preview = document.getElementById('date_preview');
                let previewText = '';
                if (currentDateType === 'single') {
                    const singleDate = document.getElementById('single_date_input').value;
                    previewText = singleDate ? formatDateDisplay(singleDate) : 'No date selected';
                } else if (currentDateType === 'range') {
                    const startDate = document.getElementById('start_date_input').value;
                    const endDate = document.getElementById('end_date_input').value;
                    if (startDate && endDate) {
                        previewText = `${formatDateDisplay(startDate)} - ${formatDateDisplay(endDate)}`;
                    } else if (startDate) {
                        previewText = `${formatDateDisplay(startDate)} - (End date not selected)`;
                    } else {
                        previewText = 'No date range selected';
                    }
                } else if (currentDateType === 'multiple') {
                    previewText = selectedDatesArray.length === 0 ? 'No dates selected' : selectedDatesArray.map(d => formatDateDisplay(d)).join(', ');
                }
                preview.textContent = previewText;
            }

            function formatDateDisplay(dateString) {
                if (!dateString) return '';
                const date = new Date(dateString + 'T00:00:00');
                return date.toLocaleDateString('en-US', {
                    month: '2-digit',
                    day: '2-digit',
                    year: 'numeric'
                });
            }

            applyDatesBtn.addEventListener('click', function() {
                let displayValue = '';
                let storageValue = '';
                if (currentDateType === 'single') {
                    const singleDate = document.getElementById('single_date_input').value;
                    if (!singleDate) {
                        alert('Please select a date');
                        return;
                    }
                    displayValue = formatDateDisplay(singleDate);
                    storageValue = singleDate;
                } else if (currentDateType === 'range') {
                    const startDate = document.getElementById('start_date_input').value;
                    const endDate = document.getElementById('end_date_input').value;
                    if (!startDate || !endDate) {
                        alert('Please select both start and end dates');
                        return;
                    }
                    if (new Date(endDate) < new Date(startDate)) {
                        alert('End date cannot be before start date');
                        return;
                    }
                    displayValue = `${formatDateDisplay(startDate)} - ${formatDateDisplay(endDate)}`;
                    storageValue = `${startDate}|${endDate}|range`;
                } else if (currentDateType === 'multiple') {
                    if (selectedDatesArray.length === 0) {
                        alert('Please add at least one date');
                        return;
                    }
                    displayValue = selectedDatesArray.map(d => formatDateDisplay(d)).join(', ');
                    storageValue = selectedDatesArray.join(',') + '|multiple';
                }
                document.getElementById('examined_on_display').value = displayValue;
                document.getElementById('examined_on').value = storageValue;
                dateSelectorModal.hide();
            });
        }

        function addMedicineRow(type, index, existingData = null) {
            const container = type === 'plan' ?
                document.getElementById('plan_medicines_container') :
                document.getElementById('nursing_medicines_container');

            const row = document.createElement('div');
            row.className = 'medicine-row';
            row.dataset.type = type;
            row.dataset.index = index;

            // Add visual indicator for existing medicines
            if (existingData && existingData.isExisting) {
                row.classList.add('existing-medicine-row');
            }

            const medicineSelect = document.createElement('select');
            medicineSelect.name = `medicines[${type}][${index}][medicine_id]`;
            medicineSelect.className = 'medicine-select';
            medicineSelect.required = true;

            const defaultOption = document.createElement('option');
            defaultOption.value = '';
            defaultOption.textContent = 'Select Medicine';
            medicineSelect.appendChild(defaultOption);

            // For existing medicines, we need to ensure the medicine appears even if out of stock
            if (existingData && existingData.isExisting) {
                // Add a special entry for the existing medicine even if it's not in medicinesData
                let medicineFound = false;

                medicinesData.forEach(category => {
                    category.medicines.forEach(medicine => {
                        if (medicine.id === existingData.medicineId) {
                            medicineFound = true;
                        }
                    });
                });

                // If medicine not found in available list, add it manually
                if (!medicineFound) {
                    const existingMedicine = @json($existingMedicines ?? []).find(m => m.medicine_id === existingData.medicineId);
                    if (existingMedicine && existingMedicine.medicine) {
                        const option = document.createElement('option');
                        option.value = existingMedicine.medicine.id;
                        option.textContent = `${existingMedicine.medicine.name} (Out of Stock)`;
                        option.dataset.medicineId = existingMedicine.medicine.id;
                        option.dataset.medicineName = existingMedicine.medicine.name;
                        option.dataset.dosages = JSON.stringify([{
                            dosage: existingData.dosage,
                            available_quantity: 0
                        }]);
                        option.dataset.totalStock = 0;
                        option.dataset.isOutOfStock = 'true';
                        option.selected = true;
                        medicineSelect.appendChild(option);
                    }
                }
            }

            medicinesData.forEach(category => {
                const optgroup = document.createElement('optgroup');
                optgroup.label = category.name;
                let hasValidMedicines = false;

                category.medicines.forEach(medicine => {
                    // Check if medicine has available stock OR is the currently selected existing medicine
                    const isCurrentlyUsed = existingData && medicine.id === existingData.medicineId;
                    const hasStock = medicine.available_quantity > 0 ||
                        (medicine.dosages && medicine.dosages.length > 0);

                    // Only show if it has stock OR is already being used in this consultation
                    if (hasStock || isCurrentlyUsed) {
                        const option = document.createElement('option');
                        option.value = medicine.id;
                        option.textContent = `${medicine.name}`;
                        option.dataset.medicineId = medicine.id;
                        option.dataset.medicineName = medicine.name;
                        option.dataset.dosages = JSON.stringify(medicine.dosages);
                        option.dataset.totalStock = medicine.available_quantity;

                        // Pre-select if this is existing data
                        if (existingData && medicine.id === existingData.medicineId) {
                            option.selected = true;
                        }

                        optgroup.appendChild(option);
                        hasValidMedicines = true;
                    }
                });

                // Only add optgroup if it has valid medicines
                if (hasValidMedicines) {
                    medicineSelect.appendChild(optgroup);
                }
            });

            const dosageSelect = document.createElement('select');
            dosageSelect.name = `medicines[${type}][${index}][dosage]`;
            dosageSelect.className = 'dosage-select';
            dosageSelect.required = true;
            dosageSelect.disabled = true;

            const dosageDefaultOption = document.createElement('option');
            dosageDefaultOption.value = '';
            dosageDefaultOption.textContent = 'Select Dosage';
            dosageSelect.appendChild(dosageDefaultOption);

            const quantityInput = document.createElement('input');
            quantityInput.type = 'number';
            quantityInput.name = `medicines[${type}][${index}][quantity]`;
            quantityInput.placeholder = 'Qty';
            quantityInput.min = '1';
            quantityInput.value = existingData ? existingData.quantity : '1';
            quantityInput.required = true;
            quantityInput.disabled = true;

            const dosageInstructions = document.createElement('input');
            dosageInstructions.type = 'text';
            dosageInstructions.name = `medicines[${type}][${index}][dosage_instructions]`;
            dosageInstructions.placeholder = 'Instructions (e.g., 1 tablet 3x a day)';
            dosageInstructions.value = existingData ? existingData.dosageInstructions || '' : '';

            const removeBtn = document.createElement('button');
            removeBtn.type = 'button';
            removeBtn.className = 'remove-medicine-btn';
            removeBtn.innerHTML = '<i class="fas fa-trash"></i>';
            removeBtn.addEventListener('click', function() {
                const isExisting = existingData && existingData.isExisting;
                const medicineName = medicineSelect.options[medicineSelect.selectedIndex]?.textContent || 'this medicine';
                const quantity = quantityInput.value || '0';
                const dosageText = dosageSelect.options[dosageSelect.selectedIndex]?.textContent || 'N/A';

                let confirmMessage = `Remove ${medicineName}?\nDosage: ${dosageText}\nQuantity: ${quantity}`;

                if (isExisting) {
                    confirmMessage += '\n\n⚠️ This will restore the stock to inventory.';
                }

                if (confirm(confirmMessage)) {
                    row.remove();
                }
            });

            medicineSelect.addEventListener('change', function() {
                const selectedOption = this.options[this.selectedIndex];

                dosageSelect.innerHTML = '';
                dosageSelect.appendChild(dosageDefaultOption.cloneNode(true));
                dosageSelect.disabled = true;
                quantityInput.disabled = true;
                if (!existingData) {
                    quantityInput.value = '1';
                }

                const existingWarning = row.querySelector('.medicine-stock-info');
                if (existingWarning) {
                    existingWarning.remove();
                }

                if (!selectedOption.value) return;

                const dosages = JSON.parse(selectedOption.dataset.dosages || '[]');

                if (dosages.length === 0) {
                    const warning = document.createElement('div');
                    warning.className = 'medicine-stock-info medicine-stock-warning';
                    warning.textContent = `⚠️ No dosages available for this medicine!`;
                    row.appendChild(warning);
                    return;
                }

                // Filter and populate dosages
                let hasAnyValidDosage = false;
                const isOutOfStock = selectedOption.dataset.isOutOfStock === 'true';

                dosages.forEach(dosageItem => {
                    // Calculate display quantity (add existing if this is the current dosage)
                    let displayQuantity = dosageItem.available_quantity;
                    let isAdjusted = false;
                    if (existingData && existingData.isExisting && dosageItem.dosage === existingData.dosage) {
                        displayQuantity = dosageItem.available_quantity + existingData.existingQuantity;
                        isAdjusted = true;
                    }

                    // Only show dosages with stock > 0 OR the currently used dosage (even if stock is 0)
                    if (displayQuantity > 0 || (existingData && dosageItem.dosage === existingData.dosage)) {
                        const option = document.createElement('option');
                        option.value = dosageItem.dosage;

                        // Special message for out-of-stock existing medicines
                        let displayText;
                        if (isOutOfStock && existingData && dosageItem.dosage === existingData.dosage) {
                            displayText = `${dosageItem.dosage} (Stock: 0 - You used ${existingData.existingQuantity})`;
                        } else if (isAdjusted) {
                            displayText = `${dosageItem.dosage} (Stock: ${dosageItem.available_quantity} + Your ${existingData.existingQuantity} used = ${displayQuantity} available)`;
                        } else {
                            displayText = `${dosageItem.dosage} (Available: ${displayQuantity})`;
                        }

                        option.textContent = displayText;
                        option.dataset.availableQty = displayQuantity; // Use adjusted quantity for validation
                        option.dataset.originalAvailableQty = dosageItem.available_quantity; // Store original for reference
                        option.dataset.isAdjusted = isAdjusted;
                        option.dataset.isOutOfStock = isOutOfStock;

                        // Pre-select dosage if this is existing data
                        if (existingData && dosageItem.dosage === existingData.dosage) {
                            option.selected = true;
                        }

                        dosageSelect.appendChild(option);
                        hasAnyValidDosage = true;
                    }
                });

                if (!hasAnyValidDosage) {
                    const warning = document.createElement('div');
                    warning.className = 'medicine-stock-info medicine-stock-warning';
                    warning.textContent = `⚠️ No dosages available with sufficient stock!`;
                    row.appendChild(warning);
                    return;
                }

                dosageSelect.disabled = false;

                // Trigger dosage change event if loading existing data
                if (existingData && existingData.dosage) {
                    dosageSelect.dispatchEvent(new Event('change'));
                }
            });

            dosageSelect.addEventListener('change', function() {
                const selectedOption = this.options[this.selectedIndex];

                const existingWarning = row.querySelector('.medicine-stock-info');
                if (existingWarning) {
                    existingWarning.remove();
                }

                if (!selectedOption.value) {
                    quantityInput.disabled = true;
                    quantityInput.value = '1';
                    return;
                }

                const availableQty = parseInt(selectedOption.dataset.availableQty || 0);
                const isOutOfStock = selectedOption.dataset.isOutOfStock === 'true';
                const isAdjusted = selectedOption.dataset.isAdjusted === 'true';

                quantityInput.max = availableQty;
                quantityInput.disabled = false;

                // Special handling for out-of-stock existing medicines
                if (isOutOfStock && existingData && existingData.isExisting) {
                    const warning = document.createElement('div');
                    warning.className = 'medicine-stock-info medicine-stock-error';
                    warning.innerHTML = `<i class="fa fa-exclamation-triangle"></i> This medicine is completely out of stock. You can only reduce the quantity or remove it to restore ${existingData.existingQuantity} units to inventory.`;
                    row.appendChild(warning);
                    quantityInput.max = existingData.existingQuantity; // Can't increase, only decrease
                } else if (availableQty <= 0) {
                    const warning = document.createElement('div');
                    warning.className = 'medicine-stock-info medicine-stock-warning';
                    warning.textContent = `⚠️ This dosage is out of stock!`;
                    row.appendChild(warning);
                    dosageSelect.value = '';
                    quantityInput.disabled = true;
                } else if (isAdjusted && availableQty < 10) {
                    const warning = document.createElement('div');
                    warning.className = 'medicine-stock-info medicine-stock-info';
                    warning.innerHTML = `<i class="fa fa-info-circle"></i> Actual Stock: ${selectedOption.dataset.originalAvailableQty} + Your ${existingData.existingQuantity} used = ${availableQty} total you can use`;
                    row.appendChild(warning);
                } else if (isAdjusted) {
                    const warning = document.createElement('div');
                    warning.className = 'medicine-stock-info medicine-stock-info';
                    warning.innerHTML = `<i class="fa fa-info-circle"></i> Current Stock: ${selectedOption.dataset.originalAvailableQty} units in inventory + Your ${existingData.existingQuantity} already used`;
                    row.appendChild(warning);
                } else if (availableQty < 10) {
                    const warning = document.createElement('div');
                    warning.className = 'medicine-stock-info medicine-stock-warning';
                    warning.textContent = `⚠️ Low stock: Only ${availableQty} units available`;
                    row.appendChild(warning);
                }
            });

            quantityInput.addEventListener('input', function() {
                const selectedDosageOption = dosageSelect.options[dosageSelect.selectedIndex];
                const availableQty = parseInt(selectedDosageOption.dataset.availableQty || 0);
                const isOutOfStock = selectedDosageOption.dataset.isOutOfStock === 'true';
                const quantity = parseInt(this.value || 0);

                // For out-of-stock existing medicines, only allow reducing quantity
                if (isOutOfStock && existingData && existingData.isExisting) {
                    if (quantity > existingData.existingQuantity) {
                        this.value = existingData.existingQuantity;
                        alert(`This medicine is out of stock. You can only reduce from ${existingData.existingQuantity} to restore stock for other patients.`);
                    }
                } else if (quantity > availableQty) {
                    this.value = availableQty;
                    alert(`Only ${availableQty} units available for this dosage.`);
                }
            });

            row.appendChild(medicineSelect);
            row.appendChild(dosageSelect);
            row.appendChild(quantityInput);
            row.appendChild(dosageInstructions);

            // Add existing medicine badge if applicable
            if (existingData && existingData.isExisting) {
                const badge = document.createElement('span');
                badge.className = 'existing-medicine-badge';
                badge.innerHTML = '<i class="fas fa-check-circle"></i> Saved';
                badge.title = 'This medicine was already saved. The available quantity shown includes the amount you already used (' + existingData.existingQuantity + ' units). Removing it will restore the stock.';
                row.appendChild(badge);
            }

            row.appendChild(removeBtn);

            container.appendChild(row);

            // If loading existing data, trigger medicine select change to populate dosages
            if (existingData && existingData.medicineId) {
                medicineSelect.dispatchEvent(new Event('change'));
            }
        }
    });
</script>
@endsection