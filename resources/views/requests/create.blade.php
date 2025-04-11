@extends('layouts.app')
@section('title')
{{__('messages.request.create_request')}}
@endsection
@section('content')
<div class="p-4">
    <div class="flex justify-between items-center mb-4">
        <h1 class="text-lg font-bold">{{__('messages.request.create_request')}}</h1>
        <a href="{{ route('request-documents.index') }}" class="bg-blue-500 text-white px-4 py-2 rounded">Back</a>
    </div>

    @php
    $user = auth()->user();
    $patient = App\Models\Patient::where('user_id', $user->id)->first();
    @endphp

    <div class="form-group mb-5">
        <label for="document_type">Document Type</label>
        <select name="document_type" id="document_type" class="form-control" required>
            <option value="" disabled selected>Select Document Type</option>
            <option value="medical_certificate">Medical Certificate</option>
            <option value="consultation_form">Consultation Form</option>
        </select>
    </div>

    @if($user->type != 3)
    <div class="mb-10">
        <label class="block text-xs" for="user_search">Search User</label>
        <input type="text" id="user_search" class="w-full border-b border-black" placeholder="Search by name or email">
        <div id="user_search_results" class="absolute bg-white border border-gray-300 w-full hidden z-10"></div>
    </div>
    @endif

    <!-- this is consultation_form -->
    <div id="consultation_form" class="form-section hidden">
        <form action="{{ route('request-documents.store') }}" method="POST">
            @csrf

            <div class="form-group mb-5 d-none">
                <label for="document_type">Document Type</label>
                <select name="document_type" id="document_type" class="form-control" required>
                    <option value="consultation_form" selected>Consultation Form</option>
                </select>
            </div>

            <div class="grid grid-cols-4 gap-2 pb-2">
                <div class="col-span-1">
                    <label class="block text-xs" for="name">NAME<span class="text-red-500">*</span></label>
                    <input type="text" id="name" name="name" class="w-full border-b border-black" value="{{ $user->type == 3 ? $user->first_name . ' ' . $user->last_name : '' }}" {{ $user->type == 3 ? 'readonly' : '' }} required>
                </div>
                <div class="col-span-1">
                    <label class="block text-xs" for="age">AGE<span class="text-red-500">*</span></label>
                    <input type="text" id="age" name="age" class="w-full border-b border-black" value="{{ $user->type == 3 ? \Carbon\Carbon::parse($user->dob)->age : '' }}" {{ $user->type == 3 ? 'readonly' : '' }} required>
                </div>
                <div class="col-span-1">
                    <label class="block text-xs" for="gender">GENDER<span class="text-red-500">*</span></label>
                    <input type="text" id="gender" name="gender" class="w-full border-b border-black" value="{{ $user->type == 3 ? ($user->gender == 1 ? 'Male' : 'Female') : '' }}" {{ $user->type == 3 ? 'readonly' : '' }} required>
                </div>
                <div class="col-span-1">
                    <label class="block text-xs" for="status">STATUS<span class="text-red-500">*</span></label>
                    <input type="text" id="status" name="status" class="w-full border-b border-black" required>
                </div>
                <div class="col-span-1">
                    <label class="block text-xs" for="date_of_birth">DATE OF BIRTH<span class="text-red-500">*</span></label>
                    <input type="date" id="date_of_birth" name="date_of_birth" class="w-full border-b border-black" value="{{ $user->type == 3 ? $user->dob : '' }}" {{ $user->type == 3 ? 'readonly' : '' }} required>
                </div>
                <div class="col-span-1">
                    <label class="block text-xs" for="address">ADDRESS<span class="text-red-500">*</span></label>
                    <input type="text" id="address" name="address" class="w-full border-b border-black" value="{{ $user->type == 3 && $patient->address ? $patient->address->address1 : '' }}" {{ $user->type == 3 ? 'readonly' : '' }} required>
                </div>
                <div class="col-span-1">
                    <label class="block text-xs" for="religion">RELIGION<span class="text-red-500">*</span></label>
                    <input type="text" id="religion" name="religion" class="w-full border-b border-black" required>
                </div>
                <div class="col-span-1">
                    <label class="block text-xs" for="patient_contact">PATIENT'S CONTACT #<span class="text-red-500">*</span></label>
                    <input type="text" id="patient_contact" name="patient_contact" class="w-full border-b border-black" value="{{ $user->type == 3 ? $user->contact : '' }}" {{ $user->type == 3 ? 'readonly' : '' }} required>
                </div>
                <div class="col-span-1">
                    <label class="block text-xs" for="campus">CAMPUS<span class="text-red-500">*</span></label>
                    {{ Form::select('campus_id', $data['campuses'], $user->type == 3 ? $user->campus_id : null, ['id' => 'campus_id', 'class' => 'w-full border-b border-black', 'placeholder' => 'Select Campus', 'required']) }}
                </div>
                <div class="col-span-1">
                    <label class="block text-xs" for="college">COLLEGE<span class="text-red-500">*</span></label>
                    {{ Form::select('college_id', $data['colleges'], $user->type == 3 ? $user->college_id : null, ['id' => 'college_id', 'class' => 'w-full border-b border-black', 'placeholder' => 'Select College', 'required']) }}
                </div>
                <div class="col-span-1">
                    <label class="block text-xs" for="course_year">COURSE & YEAR<span class="text-red-500">*</span></label>
                    <div class="grid grid-cols-2 gap-2">
                        {{ Form::select('course_id', $data['courses'], $user->type == 3 ? $user->course_id : null, ['id' => 'course_id', 'class' => 'w-full border-b border-black', 'placeholder' => 'Select Course', 'required']) }}
                        {{ Form::select('year_level_id', $data['year_levels'], $user->type == 3 ? $user->year_level_id : null, ['id' => 'year_level_id', 'class' => 'w-full border-b border-black', 'placeholder' => 'Select Year Level', 'required']) }}
                    </div>
                </div>
                <div class="col-span-1">
                    <label class="block text-xs" for="informant">INFORMANT</label>
                    <input type="text" id="informant" name="informant" class="w-full border-b border-black" value="Student">
                </div>
                <div class="col-span-4">
                    <label class="block text-xs" for="emergency_contact">CONTACT PERSON & NUMBER IN EMERGENCY</label>
                    <input type="text" id="emergency_contact" name="emergency_contact" class="w-full border-b border-black" required>
                </div>
            </div>
            <div class="grid grid-cols-4 gap-2 py-2">
                <div class="col-span-1">
                    <label class="block text-xs" for="requested_at">REQUEST DATE<span class="text-red-500">*</span></label>
                    <input type="date" id="requested_at" name="requested_at" class="w-full border-b border-black" max="{{ date('Y-m-d') }}" required>
                </div>
                <div class="col-span-3">
                    <label class="block text-xs" for="complaints">Complaint/s:</label>
                    <textarea id="complaints" name="complaints" class="w-full border-b border-black" rows="5"></textarea>
                </div>
            </div>
            <div class="grid grid-cols-4 gap-2 py-2">
                <div class="col-span-1">
                    <label class="block text-red-500 font-bold">S</label>
                    <label class="block text-xs">(Subjective Complaints)</label>
                </div>
                <div class="col-span-3">
                    <div class="grid grid-cols-2 gap-2">
                        <div class="col-span-1">
                            <label class="block text-xs" for="covid_vaccination">COVID Vaccination<span class="text-red-500">*</span></label>
                            {{ Form::select('vaccination_id', $data['vaccination_data'], $user->type == 3 ? $user->vaccination_id : null, ['id' => 'vaccination_id', 'class' => 'w-full border-b border-black', 'placeholder' => 'Select Vaccination Status']) }}
                        </div>
                        <div class="col-span-1">
                            <label class="block text-xs" for="comorbidities">Comorbidities</label>
                            {{ Form::select('comorbidities_id', $data['comorbidities'], null, ['id' => 'comorbidities_id', 'class' => 'w-full border-b border-black', 'placeholder' => 'Select Comorbidities']) }}
                        </div>
                        <div class="col-span-1">
                            <label class="block text-xs" for="allergies">Allergies<span class="text-red-500">*</span></label>
                            <input type="text" id="allergies" name="allergies" class="w-full border-b border-black">
                        </div>
                        <div class="col-span-1">
                            <label class="block text-xs" for="admissions_surgeries">Pertinent Admissions or Surgeries</label>
                            <input type="text" id="admissions_surgeries" name="admissions_surgeries" class="w-full border-b border-black">
                        </div>
                        <div class="col-span-1">
                            <label class="block text-xs" for="maintenance">Maintenance<span class="text-red-500">*</span></label>
                            <input type="text" id="maintenance" name="maintenance" class="w-full border-b border-black">
                        </div>
                        <div class="col-span-1">
                            <label class="block text-xs" for="pregnancy_status">Pregnant or Not?<span class="text-red-500">*</span></label>
                            <input type="text" id="pregnancy_status" name="pregnancy_status" class="w-full border-b border-black">
                        </div>
                        <div class="col-span-1">
                            <label class="block text-xs" for="lmp_aog">If YES, LMP/AOG</label>
                            <input type="text" id="lmp_aog" name="lmp_aog" class="w-full border-b border-black">
                        </div>
                    </div>
                </div>
            </div>
            <div class="grid grid-cols-4 gap-2 py-2">
                <div class="col-span-1">
                    <label class="block text-red-500 font-bold">O</label>
                    <label class="block text-xs">(Objective Data)</label>
                </div>
                <div class="col-span-3">
                    <div class="grid grid-cols-6 gap-2">
                        <div class="col-span-1">
                            <label class="block text-xs" for="vital_signs_bp">BP<span class="text-red-500">*</span></label>
                            <input type="text" id="vital_signs_bp" name="vital_signs_bp" class="w-full border-b border-black" required>
                        </div>
                        <div class="col-span-1">
                            <label class="block text-xs" for="vital_signs_pr">PR<span class="text-red-500">*</span></label>
                            <input type="text" id="vital_signs_pr" name="vital_signs_pr" class="w-full border-b border-black" required>
                        </div>
                        <div class="col-span-1">
                            <label class="block text-xs" for="vital_signs_temp">Temp<span class="text-red-500">*</span></label>
                            <input type="text" id="vital_signs_temp" name="vital_signs_temp" class="w-full border-b border-black" required>
                        </div>
                        <div class="col-span-1">
                            <label class="block text-xs" for="vital_signs_rr">RR<span class="text-red-500">*</span></label>
                            <input type="text" id="vital_signs_rr" name="vital_signs_rr" class="w-full border-b border-black" required>
                        </div>
                        <div class="col-span-1">
                            <label class="block text-xs" for="vital_signs_o2_sat">O2 Sat<span class="text-red-500">*</span></label>
                            <input type="text" id="vital_signs_o2_sat" name="vital_signs_o2_sat" class="w-full border-b border-black" required>
                        </div>
                        <div class="col-span-1">
                            <label class="block text-xs" for="vital_signs_weight">Weight<span class="text-red-500">*</span></label>
                            <input type="text" id="vital_signs_weight" name="vital_signs_weight" class="w-full border-b border-black" required>
                        </div>
                        <div class="col-span-1">
                            <label class="block text-xs" for="vital_signs_height">Height<span class="text-red-500">*</span></label>
                            <input type="text" id="vital_signs_height" name="vital_signs_height" class="w-full border-b border-black" required>
                        </div>
                    </div>
                    <div class="col-span-5">
                        <label class="block text-xs" for="pertinent_exam">PERTINENT EXAM<span class="text-red-500">*</span></label>
                        <textarea id="pertinent_exam" name="pertinent_exam" class="w-full border-b border-black" rows="5" required></textarea>
                    </div>
                </div>
            </div>
            <div class="grid grid-cols-4 gap-2 py-2">
                <div class="col-span-1">
                    <label class="block text-red-500 font-bold">A</label>
                    <label class="block text-xs">(Assessment)<span class="text-red-500">*</span></label>
                </div>
                <div class="col-span-3">
                    <textarea id="assessment" name="assessment" class="w-full border-b border-black" rows="5" required></textarea>
                </div>
            </div>
            <div class="grid grid-cols-4 gap-2 py-2">
                <div class="col-span-1">
                    <label class="block text-red-500 font-bold">P</label>
                    <label class="block text-xs">(Plan)<span class="text-red-500">*</span></label>
                </div>
                <div class="col-span-3">
                    <textarea id="plan" name="plan" class="w-full border-b border-black" rows="5" required></textarea>
                </div>
            </div>

            <div class="grid grid-cols-4 gap-2 py-2">
                <div class="col-span-1">
                    <label class="block">Consult Mode<span class="text-red-500">*</span></label>
                </div>
                <div class="col-span-3">
                    {{ Form::select('consult_mode', ['physical' => 'Physical', 'virtual' => 'Virtual'], null, ['id' => 'consult_mode', 'class' => 'w-full border-b border-black', 'placeholder' => 'Select Consultation Mode', 'required']) }}
                </div>
            </div>

            <div class="grid grid-cols-4 gap-2 py-2">
                <div class="col-span-1">
                    <label class="block">Nursing Intervention<span class="text-red-500">*</span></label>
                </div>
                <div class="col-span-3">
                    <textarea id="nursing_intervention" name="nursing_intervention" class="w-full border-b border-black" rows="5" required></textarea>
                </div>
            </div>
            <div class="grid grid-cols-4 gap-2 py-2">
                <div class="col-span-1">
                    <label class="block">Nursing In-charged<span class="text-red-500">*</span></label>
                </div>
                <div class="col-span-3">
                    <!-- <select id="nursing_incharged" name="nursing_incharged" class="w-full border-b border-black" required>
                        <option value="" disabled selected>Select Nursing In-charged</option>
                        @foreach(\App\Models\User::where('type', \App\Models\User::STAFF)->get() as $staff)
                        <option value="{{ $staff->id }}">{{ $staff->first_name }} {{ $staff->last_name }}</option>
                        @endforeach
                    </select> -->

                    @if(auth()->user()->type == \App\Models\User::ADMIN)
                    <!-- Admin can select the nursing in-charged -->
                    <select id="nursing_incharged" name="nursing_incharged" class="w-full border-b border-black" required>
                        <option value="" disabled selected>Select Nursing In-charged</option>
                        @foreach(\App\Models\User::where('type', \App\Models\User::STAFF)->get() as $staff)
                        <option value="{{ $staff->id }}">{{ $staff->first_name }} {{ $staff->last_name }}</option>
                        @endforeach
                    </select>
                    @elseif(auth()->user()->type == \App\Models\User::STAFF)
                    <!-- Staff's account is pre-filled -->
                    <input type="text" id="nursing_incharged_display" class="w-full border-b border-black" value="{{ auth()->user()->first_name }} {{ auth()->user()->last_name }}" readonly>
                    <input type="hidden" id="nursing_incharged" name="nursing_incharged" value="{{ auth()->user()->id }}">
                    @endif
                </div>
            </div>


            <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded mt-4">
                Submit
            </button>
        </form>
    </div>

    <!-- medical_certificate -->
    <div id="medical_certificate" class="form-section hidden flex justify-center items-center">
        <div class="bg-white p-6 rounded-lg shadow-lg" style="width: 1065px;">
            <div class="flex items-center my-4">
                <!-- Left Logo -->
                <div>
                    <img src="{{ asset('assets/image/norsu_logo.png') }}" alt="Logo" class="w-22 h-22">
                </div>

                <!-- Text Content -->
                <div class="text-center flex-1">
                    <h1 class="text-xl font-bold">Negros Oriental State University</h1>
                    <h2 class="text-md">University Medical Clinic, CNPAHS Bldg., Kagawasan Ave., Dumaguete City</h2>
                    <p class="text-sm">Tel #: 225-9400, then Local # 188, 09263829484</p>
                </div>

                <!-- Right Logo -->
                <div class="ml-4">
                    <img src="{{ asset('assets/image/norsu_logo.png') }}" alt="Logo" class="w-22 h-22">
                </div>
            </div>

            <h3 class="text-lg text-center font-semibold mb-8">MEDICAL CERTIFICATE</h3>
            <form action="{{ route('request-documents.store') }}" method="POST">
                @csrf
                <div class="form-group mb-5 d-none">
                    <label for="document_type">Document Type</label>
                    <select name="document_type" id="document_type" class="form-control" required>
                        <option value="medical_certificate" selected>Medical Certificate</option>
                    </select>
                </div>

                <div class="flex row">
                    <p>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;This is to certify that Mr./Ms.
                        <input type="text" id="name_2" name="name" style="width: 400px; text-align: center;" class="border-b border-black" value="{{ $user->type == 3 ? $user->first_name . ' ' . $user->last_name : '' }}" readonly required>,
                        <input type="text" id="age_2" name="age" style="width: 70px; text-align: center;" class="border-b border-black" value="{{ $user->type == 3 ? \Carbon\Carbon::parse($user->dob)->age : '' }}" readonly required> yrs old,
                        <input type="text" id="gender_2" name="gender" style="width: 70px; text-align: center;" class="border-b border-black" value="{{ $user->type == 3 ? ($user->gender == 1 ? 'Male' : 'Female') : '' }}" readonly required> a resident of
                    </p>
                    <p>
                        <input type="text" id="address_2" name="address" style="width: 470px; text-align: center;" class="border-b border-black" value="{{ $user->type == 3 && $patient->address ? $patient->address->address1 : '' }}" {{ $user->type == 3 ? 'readonly' : '' }} required>
                        , was seen and examined at my clinic on <input type="date" id="examined_on" name="examined_on" style="width: 120px; text-align: center;" class="border-b border-black" value="{{ date('Y-m-d') }}" max="{{ date('Y-m-d') }}" required> with the following
                    <p class="font-semibold">complaints/diagnosis:</p>
                    <div class="border border-gray-300 p-2 h-28 mb-4">
                        <div class="col-span-3">
                            <textarea id="complaints_diagnosis" name="complaints_diagnosis" class="w-full border-b border-black" rows="5" required></textarea>
                        </div>
                    </div>
                    </p>
                </div>

                <div class="grid grid-cols-6 grid-rows-1 gap-7 mb-2">
                    <div>
                        <p class="font-semibold">BP<span class="text-red-500">*</span>: <input type="text" id="vital_signs_bp_2" name="vital_signs_bp_2" style="width: 30px; text-align: center;" class="border-b border-black" required> / <input type="text" id="vital_signs_bp_22" name="vital_signs_bp_22" style="width: 30px; text-align: center;" class="border-b border-black" required></p>
                    </div>
                    <div>
                        <p class="font-semibold">P<span class="text-red-500">*</span>: <input type="text" id="vital_signs_pr_2" name="vital_signs_pr_2" style="width: 50px; text-align: center;" class="border-b border-black" required></p>
                    </div>
                    <div>
                        <p class="font-semibold">R<span class="text-red-500">*</span>: <input type="text" id="vital_signs_rr_2" name="vital_signs_rr_2" style="width: 50px; text-align: center;" class="border-b border-black" required></p>
                    </div>
                    <div>
                        <p class="font-semibold">T<span class="text-red-500">*</span>: <input type="text" id="vital_signs_temp_2" name="vital_signs_temp_2" style="width: 50px; text-align: center;" class="border-b border-black" required></p>
                    </div>
                    <div>
                        <p class="font-semibold">Ht<span class="text-red-500">*</span>: <input type="text" id="vital_signs_height_2" name="vital_signs_height_2" style="width: 50px; text-align: center;" class="border-b border-black" required></p>
                    </div>
                    <div>
                        <p class="font-semibold">Wt<span class="text-red-500">*</span>: <input type="text" id="vital_signs_weight_2" name="vital_signs_weight_2" style="width: 50px; text-align: center;" class="border-b border-black" required></p>
                    </div>
                </div>

                <p class="font-semibold">Remark/s:</p>
                <div class="border border-gray-300 p-2 h-28 mb-4">
                    <div class="col-span-3">
                        <textarea id="medical_cert_remarks" name="medical_cert_remarks" class="w-full border-b border-black" rows="5" required></textarea>
                    </div>
                </div>

                <p class="text-sm text-black">Note: Please check the original copy of med cert before accepting the photocopied med cert. This medical certificate is <span class="font-bold underline">not to be used</span> outside school purposes or medico-legal purposes.</p>

                <p class="text-sm">This certificate is issued upon the request of _______________________ for your reference.</p>

                <div class="text-right mt-4 mr-5">
                    <p class="font-semibold">Dr. Mcfael S. Olivoros</p>
                    <p class="text-xs">Lic #: <input type="text" id="doc_lic_no" name="doc_lic_no" style="width: 110px; text-align: center;" class="border-b border-black" value="0113005" required></p>
                    <p class=" text-xs">PTR #: <input type="text" id="doc_prt_no" name="doc_prt_no" style="width: 105px; text-align: center;" class="border-b border-black" required></p>
                </div>

                <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded mt-4 text-center">
                    Submit
                </button>
            </form>
        </div>
    </div>

    <!-- medical_clearance -->
    <!-- <div id="medical_clearance" class="form-section hidden">
        <h1>medical_clearance</h1>
    </div> -->

</div>


<script>
    document.addEventListener('DOMContentLoaded', function() {
        const userSearchInput = document.getElementById('user_search');
        const userSearchResults = document.getElementById('user_search_results');

        // Check if the userSearchInput exists before adding the event listener
        if (userSearchInput) {
            userSearchInput.addEventListener('input', function() {
                const query = userSearchInput.value;

                if (query.length > 1) {
                    fetch(`{{ route('search-users') }}?query=${query}`)
                        .then(response => response.json())
                        .then(data => {
                            userSearchResults.innerHTML = '';
                            userSearchResults.classList.remove('hidden');

                            if (data.length === 0) {
                                const noResults = document.createElement('div');
                                noResults.classList.add('p-2', 'text-gray-500');
                                noResults.textContent = 'No patients found.';
                                userSearchResults.appendChild(noResults);
                                return;
                            }

                            data.forEach(patient => {
                                const option = document.createElement('div');
                                option.classList.add('p-2', 'cursor-pointer', 'hover:bg-gray-200');
                                option.textContent = `${patient.user.first_name} ${patient.user.last_name}`;
                                option.dataset.patient = JSON.stringify(patient);

                                option.addEventListener('click', function() {
                                    const patientData = JSON.parse(this.dataset.patient);

                                    document.getElementById('name').value = `${patientData.user.first_name} ${patientData.user.last_name}`;
                                    document.getElementById('name_2').value = `${patientData.user.first_name} ${patientData.user.last_name}`;
                                    document.getElementById('age').value = calculateAge(patientData.user.dob);
                                    document.getElementById('age_2').value = calculateAge(patientData.user.dob);
                                    document.getElementById('gender').value = patientData.user.gender === 1 ? 'Male' : 'Female';
                                    document.getElementById('gender_2').value = patientData.user.gender === 1 ? 'Male' : 'Female';
                                    document.getElementById('date_of_birth').value = patientData.user.dob || '';
                                    document.getElementById('patient_contact').value = patientData.user.contact;
                                    document.getElementById('emergency_contact').value = `${patientData.user.emergency_contact_name}/${patientData.user.emergency_contact_no}`;
                                    document.getElementById('campus_id').value = patientData.user.campus_id;
                                    document.getElementById('college_id').value = patientData.user.college_id;
                                    document.getElementById('course_id').value = patientData.user.course_id;
                                    document.getElementById('year_level_id').value = patientData.user.year_level_id;
                                    document.getElementById('vaccination_id').value = patientData.user.vaccination_id;

                                    if (patientData.address) {
                                        document.getElementById('address').value = `${patientData.address.address1}`;
                                        document.getElementById('address_2').value = `${patientData.address.address1}`;
                                    }

                                    userSearchResults.classList.add('hidden');
                                });

                                userSearchResults.appendChild(option);
                            });
                        })
                        .catch(error => {
                            console.error('Error fetching patients:', error);
                        });
                } else {
                    userSearchResults.classList.add('hidden');
                }
            });

            document.addEventListener('click', function(e) {
                if (!userSearchResults.contains(e.target) && e.target !== userSearchInput) {
                    userSearchResults.classList.add('hidden');
                }
            });
        } else {
            console.warn('Element with id "user_search" not found. Skipping event listener.');
        }

        function calculateAge(dob) {
            if (!dob) return '';
            const birthDate = new Date(dob);
            if (isNaN(birthDate)) return '';
            const today = new Date();
            let age = today.getFullYear() - birthDate.getFullYear();
            const monthDiff = today.getMonth() - birthDate.getMonth();
            if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < birthDate.getDate())) {
                age--;
            }
            return age;
        }
    });
</script>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const documentTypeSelect = document.getElementById('document_type');
        const formSections = document.querySelectorAll('.form-section');

        // Check if the element exists before adding the event listener
        if (documentTypeSelect) {
            documentTypeSelect.addEventListener('change', function() {
                const selectedType = this.value;

                // Hide all form sections
                formSections.forEach(section => section.classList.add('hidden'));

                // Show the selected form section
                const selectedForm = document.getElementById(selectedType);
                if (selectedForm) {
                    selectedForm.classList.remove('hidden');
                }
            });
        } else {
            console.error('Element with id "document_type" not found.');
        }
    });
</script>

<style>
    .hidden {
        display: none;
    }
</style>

@endsection