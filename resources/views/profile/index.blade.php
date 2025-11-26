@extends('layouts.app')
@section('title')
{{ __('messages.user.profile_details') }}
@endsection
@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-end mb-5">
        <h1 class="fw-bold">{{ __('messages.user.edit_profile') }}</h1>
    </div>

    <div class="col-12">
        @include('layouts.errors')
        @include('flash::message')
    </div>

    {{-- Debug: Deep data verification with null/empty checks --}}
    <script>
        console.log('=== DEEP PROFILE EDIT DEBUG ===');

        // Parse user and patient data as objects
        const userData = @json($user ?? null);
        const patientData = @json($patient ?? null);
        const patientAddress = @json($patient->address ?? null);

        console.log('User Object:', userData);
        console.log('Patient Object:', patientData);
        console.log('Patient Address:', patientAddress);

        // Deep verification of user properties
        console.log('=== USER DATA VERIFICATION ===');
        console.log('User exists?', userData !== null);
        console.log('User ID:', userData?.id || 'NULL');
        console.log('User Email:', userData?.email || 'NULL');
        console.log('User Department ID:', userData?.department_id || 'NULL');
        console.log('User Office ID:', userData?.office_id || 'NULL');
        console.log('User Year Level ID:', userData?.year_level_id || 'NULL');
        console.log('User College ID:', userData?.college_id || 'NULL');

        // Deep verification of department_id specifically
        console.log('=== DEPARTMENT ID DEEP CHECK ===');
        console.log('Type of department_id:', typeof userData?.department_id);
        console.log('Is NULL?', userData?.department_id === null);
        console.log('Is undefined?', userData?.department_id === undefined);
        console.log('Is empty string?', userData?.department_id === '');
        console.log('Is zero?', userData?.department_id === 0);
        console.log('Truthiness:', !!userData?.department_id);

        // Dropdown data arrays
        console.log('=== DROPDOWN DATA ARRAYS ===');
        console.log('Departments Count:', {
            {
                count($data['departments'] ?? [])
            }
        });
        console.log('Offices Count:', {
            {
                count($data['offices'] ?? [])
            }
        });
        console.log('States Count:', {
            {
                count($data['states'] ?? [])
            }
        });
        console.log('Cities Count:', {
            {
                count($data['cities'] ?? [])
            }
        });

        // Selected values verification
        console.log('=== SELECTED VALUES VERIFICATION ===');
        const deptId = '{{ $user->department_id ?? "" }}';
        const officeId = '{{ $user->office_id ?? "" }}';
        const stateId = '{{ !empty($patient->address) ? $patient->address->state_id : "" }}';
        const cityId = '{{ !empty($patient->address) ? $patient->address->city_id : "" }}';

        console.log('Department ID from Blade:', deptId, '| Length:', deptId.length, '| Empty?', deptId === '');
        console.log('Office ID from Blade:', officeId, '| Length:', officeId.length, '| Empty?', officeId === '');
        console.log('State ID from Blade:', stateId, '| Length:', stateId.length, '| Empty?', stateId === '');
        console.log('City ID from Blade:', cityId, '| Length:', cityId.length, '| Empty?', cityId === '');

        // Check actual HTML rendered (truncated to avoid console clutter)
        document.addEventListener('DOMContentLoaded', function() {
            console.log('=== HTML VERIFICATION ===');

            // Check hidden debug divs for server-rendered values
            const debugDept = document.getElementById('debug-dept-value');
            const debugState = document.getElementById('debug-state-value');

            if (debugDept) {
                console.log('DEBUG DEPT DIV:', debugDept.textContent.trim());
                console.log('DEBUG DEPT DATA-VALUE:', debugDept.getAttribute('data-value'));
            }

            if (debugState) {
                console.log('DEBUG STATE DIV:', debugState.textContent.trim());
                console.log('DEBUG STATE DATA-VALUE:', debugState.getAttribute('data-value'));
            }

            const deptDropdown = document.getElementById('departmentSelect');
            const stateDropdown = document.getElementById('patientProfileStateId');
            const cityDropdown = document.getElementById('patientProfileCityId');

            if (deptDropdown) {
                const selectedOption = deptDropdown.querySelector('option[selected]');
                console.log('Department dropdown options:', deptDropdown.options.length);
                console.log('Department selected option:', selectedOption?.value || 'NONE');
                console.log('Department current value:', deptDropdown.value);

                // List all options to verify if the value exists
                console.log('Department options:', Array.from(deptDropdown.options).map(o => ({
                    value: o.value,
                    text: o.text,
                    selected: o.selected
                })));
            } else {
                console.log('Department dropdown: NOT FOUND');
            }

            if (stateDropdown) {
                const selectedOption = stateDropdown.querySelector('option[selected]');
                console.log('State dropdown options:', stateDropdown.options.length);
                console.log('State selected option:', selectedOption?.value || 'NONE');
                console.log('State current value:', stateDropdown.value);
            } else {
                console.log('State dropdown: NOT FOUND');
            }

            if (cityDropdown) {
                const selectedOption = cityDropdown.querySelector('option[selected]');
                console.log('City dropdown options:', cityDropdown.options.length);
                console.log('City selected option:', selectedOption?.value || 'NONE');
                console.log('City current value:', cityDropdown.value);
            } else {
                console.log('City dropdown: NOT FOUND');
            }
        });
    </script>

    <form id="profileForm" method="POST" action="{{ route('update.profile.setting') }}" enctype="multipart/form-data">
        {{ Form::hidden('is_edit', true, ['id' => 'staffProfileIsEdit']) }}
        {{ Form::hidden('is_edit', true, ['id' => 'patientProfileIsEdit']) }}
        {{ Form::hidden(
                'edit_patient_country_id',
                isset($patient->address->country_id) ? $patient->address->country_id : null,
                ['id' => 'editPatientProfileCountryId'],
            ) }}
        {{ Form::hidden(
                'edit_patient_state_id',
                isset($patient->address->state_id) ? $patient->address->state_id : null,
                ['id' => 'editPatientProfileStateId'],
            ) }}
        {{ Form::hidden('edit_patient_city_id', isset($patient->address->city_id) ? $patient->address->city_id : null, [
                'id' => 'editPatientProfileCityId',
            ]) }}
        @csrf
        @method('PUT')

        <!-- Profile Picture Card -->
        <div class="card shadow-sm mb-5">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="symbol symbol-100px symbol-lg-150px me-5">
                        @php $styleCss = 'style' @endphp
                        <div class="mb-3" io-image-input="true">
                            <div class="d-block">
                                <div class="image-picker">
                                    <div class="image previewImage" id="exampleInputImage" {{ $styleCss }}="background-image: url('{{ (getLogInUser()->hasRole('patient')) ? getLogInUser()->patient->profile : $user->profile_image }}')">
                                    </div>
                                    <span class="picker-edit rounded-circle text-gray-500 fs-small"
                                        data-bs-toggle="tooltip"
                                        data-bs-original-title="{{ __('messages.user.edit_profile') }}">
                                        <label>
                                            <i class="fa-solid fa-pen" id="profileImageIcon"></i>
                                            <input type="file" id="profilePicture" name="image"
                                                class="image-upload d-none profile-validation" accept="image/*" />
                                        </label>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div>
                        <h3 class="fw-bold mb-1">{{ $user->full_name }}</h3>
                        <div class="text-muted">{{ $user->email }}</div>
                        <div class="text-muted mt-2">
                            <i class="fas fa-camera text-primary me-2"></i>
                            <small>{{ __('Click the edit icon to change profile picture') }}</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Personal Information Card -->
        <div class="card shadow-sm mb-5">
            <div class="card-header">
                <h3 class="card-title fw-bold">
                    <i class="fas fa-user-circle text-primary me-2"></i>
                    {{ __('messages.patient.personal_information') }}
                </h3>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4 mb-5">
                        {{ Form::label('firstName',__('messages.patient.first_name').':' ,['class' => 'form-label required fw-semibold']) }}
                        {{ Form::text('first_name', $user->first_name, ['class'=> 'form-control form-control-lg', 'placeholder' => __('messages.patient.first_name'), 'required']) }}
                    </div>
                    <div class="col-md-4 mb-5">
                        {{ Form::label('middleName',__('messages.patient.middle_name').':' ,['class' => 'form-label fw-semibold']) }}
                        {{ Form::text('middle_name', $user->middle_name, ['class'=> 'form-control form-control-lg', 'placeholder' => __('messages.patient.middle_name')]) }}
                    </div>
                    <div class="col-md-4 mb-5">
                        {{ Form::label('lastName',__('messages.patient.last_name').':' ,['class' => 'form-label required fw-semibold']) }}
                        {{ Form::text('last_name', $user->last_name, ['class'=> 'form-control form-control-lg', 'placeholder' =>__('messages.patient.last_name'), 'required']) }}
                    </div>
                </div>

                <div class="separator separator-dashed my-6"></div>

                <div class="row">
                    <div class="col-md-6 mb-5">
                        {{ Form::label('email',__('messages.patient.email').':' ,['class' => 'form-label required fw-semibold']) }}
                        {{ Form::email('email', $user->email, ['class'=> 'form-control form-control-lg', 'placeholder' => __('messages.user.email'), 'required']) }}
                    </div>
                    <div class="col-md-6 mb-5">
                        {{ Form::label('contact', __('messages.patient.contact_no').':', ['class' => 'form-label fw-semibold']) }}
                        {{ Form::tel('contact', $user->contact ? '+' . $user->country_code . $user->contact : null, ['id' => 'phoneNumber', 'class' => 'form-control form-control-lg', 'placeholder' => __('messages.user.contact_number'), 'onkeyup' => 'if (/\D/g.test(this.value)) this.value = this.value.replace(/\D/g,"")']) }}
                        {{ Form::hidden('country_code', !empty($user->country_code) ? $user->country_code : null, ['id' => 'prefix_code']) }}
                        <span id="valid-msg" class="text-success d-none fw-400 fs-small mt-2">{{ __('messages.valid_number') }}</span>
                        <span id="error-msg" class="text-danger d-none fw-400 fs-small mt-2">{{ __('messages.invalid_number') }}</span>
                    </div>
                    <div class="col-md-6 mb-5">
                        {{ Form::label('emergencyName',__('messages.patient.emergency_contact_name').':' ,['class' => 'form-label fw-semibold']) }}
                        {{ Form::text('emergency_contact_name', $user->emergency_contact_name, ['class' => 'form-control form-control-lg','placeholder' => __('messages.patient.emergency_contact_name')]) }}
                    </div>
                    <div class="col-md-6 mb-5">
                        {{ Form::label('emergencyNo',__('messages.patient.emergency_contact_no').':' ,['class' => 'form-label fw-semibold']) }}
                        {{ Form::text('emergency_contact_no', $user->emergency_contact_no, ['class' => 'form-control form-control-lg','placeholder' => __('messages.patient.emergency_contact_no')]) }}
                    </div>
                </div>

                <div class="separator separator-dashed my-6"></div>

                <div class="row">
                    <div class="col-md-6 mb-5">
                        {{ Form::label('time_zone', __('messages.user.time_zone').':',['class' => 'form-label required fw-semibold']) }}
                        {{ Form::select('time_zone', App\Models\User::TIME_ZONE_ARRAY, $user->time_zone,['class'=> 'form-control form-control-lg io-select2', 'placeholder' => __('messages.user.select_time_zone'), 'required', 'data-control'=>'select2']) }}
                    </div>
                    <div class="col-md-6 mb-5">
                        {{ Form::label('gender', __('messages.staff.gender') . ':', ['class' => 'form-label required fw-semibold']) }}
                        <div class="mt-3">
                            <div class="form-check form-check-custom form-check-solid form-check-lg form-check-inline">
                                <input class="form-check-input" type="radio" name="gender" value="1" id="genderMale"
                                    {{ !empty($user) && $user->gender === 1 ? 'checked' : '' }}>
                                <label class="form-check-label fw-semibold" for="genderMale">
                                    <i class="fas fa-mars text-primary me-1"></i>
                                    {{ __('messages.staff.male') }}
                                </label>
                            </div>
                            <div class="form-check form-check-custom form-check-solid form-check-lg form-check-inline ms-5">
                                <input class="form-check-input" type="radio" name="gender" value="2" id="genderFemale"
                                    {{ !empty($user) && $user->gender === 2 ? 'checked' : '' }}>
                                <label class="form-check-label fw-semibold" for="genderFemale">
                                    <i class="fas fa-venus text-danger me-1"></i>
                                    {{ __('messages.staff.female') }}
                                </label>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="separator separator-dashed my-6"></div>

                <div class="row">
                    <div class="col-md-4 mb-5">
                        {{ Form::label('dob', __('messages.patient.dob') . ':', ['class' => 'form-label fw-semibold']) }}
                        {{ Form::text('dob', !empty($user) ? $user->dob : null, ['class' => 'form-control form-control-lg patient-dob', 'id' => __('messages.patient.dob'), 'placeholder' => __('messages.doctor.select_dob')]) }}
                    </div>
                    <div class="col-md-4 mb-5">
                        {{ Form::label('blood_type', __('messages.patient.blood_type') . ':', ['class' => 'form-label fw-semibold']) }}
                        {{ Form::select('blood_type', $data['bloodGroupList'], !empty($user) ? $user->blood_type : null, ['placeholder' => __('messages.patient.select_blood_type'), 'class' => 'form-control form-control-lg io-select2', 'aria-label' => 'Select a Blood Group', 'data-control' => 'select2']) }}
                    </div>
                    <div class="col-md-4 mb-5">
                        {{ Form::label('vaccination_id',__('messages.patient.vaccination_status').':',['class'=>'form-label fw-semibold']) }}
                        {{ Form::select('vaccination_id', $data['vaccination_data'] ,!empty($user) ? $user->vaccination_id : null, ['placeholder' => __('messages.patient.vaccination_status'),'class' => 'form-control form-control-lg io-select2', 'aria-label'=>"Select a Vaccination Status",'data-control'=>'select2']) }}
                    </div>
                </div>
            </div>
        </div>

        <!-- Address Information Card -->
        <div class="card shadow-sm mb-5">
            <div class="card-header">
                <h3 class="card-title fw-bold">
                    <i class="fas fa-map-marker-alt text-primary me-2"></i>
                    {{ __('messages.patient.address_information') }}
                </h3>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6 mb-7">
                        {{ Form::label('address1', __('messages.patient.address1') . ':', ['class' => 'form-label fw-semibold']) }}
                        {{ Form::text('address1', !empty($patient->address) ? $patient->address->address1 : null, ['class' => 'form-control form-control-lg', 'placeholder' => __('messages.patient.address1')]) }}
                    </div>
                    <div class="col-md-6 mb-7">
                        {{ Form::label('address2', __('messages.patient.address2') . ':', ['class' => 'form-label fw-semibold']) }}
                        {{ Form::text('address2', !empty($patient->address) ? $patient->address->address2 : null, ['class' => 'form-control form-control-lg', 'placeholder' => __('messages.patient.address2')]) }}
                    </div>
                    <div class="col-md-6 mb-7">
                        {{ Form::label('country_id', __('messages.country.country') . ':', ['class' => 'form-label fw-semibold']) }}
                        {{ Form::select('country_id', $data['countries'], !empty($patient->address) ? $patient->address->country_id : null, [
                                'id' => 'patientProfileCountryId',
                                'data-placeholder' => __('messages.country.country'),
                                'class' => 'form-control form-control-lg io-select2',
                                'aria-label' => 'Select a Country',
                                'data-control' => 'select2'
                            ]) }}
                    </div>
                    <div class="col-md-6 mb-7">
                        {{ Form::label('state_id', __('messages.province.province') . ':', ['class' => 'form-label fw-semibold']) }}

                        {{-- DEEP DEBUG: Verify exact value being passed for state --}}
                        @php
                        $stateIdValue = !empty($patient->address) ? $patient->address->state_id : null;
                        \Log::info('State Field Rendering:', [
                        'address_exists' => !empty($patient->address),
                        'state_id' => $patient->address->state_id ?? 'NULL',
                        'is_null' => isset($patient->address->state_id) ? is_null($patient->address->state_id) : 'NO ADDRESS',
                        'is_empty' => isset($patient->address->state_id) ? empty($patient->address->state_id) : 'NO ADDRESS',
                        'type' => isset($patient->address->state_id) ? gettype($patient->address->state_id) : 'NO ADDRESS',
                        'final_value' => $stateIdValue,
                        ]);
                        @endphp
                        <div style="display:none;" id="debug-state-value" data-value="{{ $stateIdValue }}">
                            ADDRESS EXISTS: {{ !empty($patient->address) ? 'YES' : 'NO' }}
                            | RAW: {{ $patient->address->state_id ?? 'NULL' }}
                            | VALUE: "{{ $stateIdValue }}"
                        </div>

                        {{ Form::select('state_id', $data['states'] ?? [], $stateIdValue, [
                                'id' => 'patientProfileStateId',
                                'class' => 'form-control form-control-lg io-select2',
                                'data-placeholder' => __('messages.common.select_province'),
                                'aria-label' => 'Select State',
                                'data-control' => 'select2'
                            ]) }}
                    </div>
                    <div class="col-md-6 mb-7">
                        {{ Form::label('city_id', __('messages.city.city') . ':', ['class' => 'form-label fw-semibold']) }}
                        {{ Form::select('city_id', $data['cities'] ?? [], !empty($patient->address) ? $patient->address->city_id : null, ['id' => 'patientProfileCityId', 'class' => 'form-control form-control-lg io-select2', 'data-placeholder' => __('messages.common.select_city'), 'aria-label' => 'Select City', 'data-control' => 'select2']) }}
                    </div>
                    <div class="col-md-6 mb-7">
                        {{ Form::label('postalCode', __('messages.patient.postal_code') . ':', ['class' => 'form-label fw-semibold']) }}
                        {{ Form::text('postal_code', !empty($patient->address) ? $patient->address->postal_code : null, ['class' => 'form-control form-control-lg', 'placeholder' => __('messages.patient.postal_code')]) }}
                    </div>
                </div>
            </div>
        </div>

        @if(getLogInUser()->hasRole('patient'))
        <!-- Student Information Card -->
        <div class="card shadow-sm mb-5">
            <div class="card-header">
                <h3 class="card-title fw-bold">
                    <i class="fas fa-graduation-cap text-primary me-2"></i>
                    {{ __('messages.student.student_information') }}
                </h3>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6 mb-5">
                        <div class="form-check">
                            {{ Form::checkbox('is_employee', 1, !empty($patient->user) && in_array($patient->user->year_level_id, [7, 8]), ['class' => 'form-check-input', 'id' => 'isEmployeeCheckbox']) }}
                            {{ Form::label('is_employee', __('messages.student.is_employee'), ['class' => 'form-check-label']) }}
                        </div>
                    </div>
                    <div class="col-md-6 mb-5">
                        <div class="form-check">
                            {{ Form::checkbox('is_guest', 1, !empty($patient->user) && $patient->user->year_level_id == 9, ['class' => 'form-check-input', 'id' => 'isGuestCheckbox']) }}
                            {{ Form::label('is_guest', __('Is Guest'), ['class' => 'form-check-label']) }}
                        </div>
                    </div>

                    <!-- Employee Position Field (Faculty/Staff) -->
                    <div class="col-md-6 mb-7" id="positionFieldContainer" style="display: none;">
                        {{ Form::label('position_type', __('Position').':',['class'=>'form-label']) }}
                        @php
                        $positionType = null;
                        if (!empty($patient->user) && $patient->user->year_level_id == 7) {
                        $positionType = 'faculty';
                        } elseif (!empty($patient->user) && $patient->user->year_level_id == 8) {
                        $positionType = 'staff';
                        }
                        @endphp
                        {{ Form::select('position_type', ['faculty' => 'Faculty', 'staff' => 'Staff'], $positionType, ['placeholder' => 'Select Position','class' => 'form-select io-select2', 'aria-label'=>"Select Position",'data-control'=>'select2', 'id' => 'positionTypeSelect']) }}
                    </div>
                    <div class="col-md-6 mb-7" id="campusFieldContainer">
                        {{ Form::label('campus_id',__('messages.student.campus').':',['class'=>'form-label']) }}
                        {{ Form::select('campus_id', $data['campuses'] ,!empty($patient->user) ? $patient->user->campus_id : null, ['placeholder' => __('messages.student.select_campus'),'class' => 'form-select io-select2', 'aria-label'=>"Select a Campus",'data-control'=>'select2']) }}
                    </div>

                    <div class="col-md-6 mb-7" id="collegeFieldContainer">
                        {{ Form::label('college_id',__('messages.student.college').':',['class'=>'form-label']) }}
                        {{ Form::select('college_id', $data['colleges'] ,!empty($patient->user) ? $patient->user->college_id : null, ['placeholder' => __('messages.student.select_college'),'class' => 'form-select io-select2', 'aria-label'=>"Select a College",'data-control'=>'select2']) }}
                    </div>

                    <!-- Course field (for Students) -->
                    <div class="col-md-6 mb-7" id="courseFieldContainer">
                        {{ Form::label('course_id',__('messages.student.course').':',['class'=>'form-label', 'id' => 'courseFieldLabel']) }}
                        {{ Form::select('course_id', $data['courses'] ,!empty($patient->user) ? $patient->user->course_id : null, ['placeholder' => __('messages.student.select_course'),'class' => 'form-select io-select2', 'aria-label'=>"Select a Course",'data-control'=>'select2', 'id' => 'courseSelect']) }}
                    </div>

                    <!-- Department field (for Faculty - uses departments dropdown) -->
                    <div class="col-md-6 mb-7" id="departmentFieldContainer" style="display: none;">
                        {{ Form::label('department_id',__('Department').':',['class'=>'form-label']) }}

                        {{-- DEEP DEBUG: Verify exact value being passed --}}
                        @php
                        $deptIdValue = $user->department_id ?? null;
                        \Log::info('Department Field Rendering:', [
                        'user_department_id' => $user->department_id,
                        'is_null' => is_null($user->department_id),
                        'is_empty' => empty($user->department_id),
                        'type' => gettype($user->department_id),
                        'final_value' => $deptIdValue,
                        ]);
                        @endphp
                        <div style="display:none;" id="debug-dept-value" data-value="{{ $deptIdValue }}">
                            RAW: {{ $user->department_id }}
                            | NULL CHECK: {{ is_null($user->department_id) ? 'IS NULL' : 'NOT NULL' }}
                            | EMPTY CHECK: {{ empty($user->department_id) ? 'IS EMPTY' : 'NOT EMPTY' }}
                            | TYPE: {{ gettype($user->department_id) }}
                            | VALUE: "{{ $deptIdValue }}"
                        </div>

                        {{ Form::select('department_id', $data['departments'] ?? [], $deptIdValue, ['placeholder' => 'Select Department','class' => 'form-select io-select2', 'aria-label'=>"Select Department",'data-control'=>'select2', 'id' => 'departmentSelect']) }}
                    </div>

                    <!-- Office field (for Staff only) -->
                    <div class="col-md-6 mb-7" id="officeFieldContainer" style="display: none;">
                        {{ Form::label('office_id',__('Office').':',['class'=>'form-label']) }}
                        {{ Form::select('office_id', $data['offices'] ?? [], $user->office_id ?? null, ['placeholder' => 'Select Office','class' => 'form-select io-select2', 'aria-label'=>"Select Office",'data-control'=>'select2', 'id' => 'officeSelect']) }}
                    </div>

                    <div class="col-md-6 mb-7" id="yearLevelFieldContainer">
                        {{ Form::label('year_level_id', __('messages.student.year_level').':',['class'=>'form-label', 'id' => 'yearLevelLabel']) }}
                        {{ Form::select('year_level_id', $data['year_levels'], !empty($patient->user) ? $patient->user->year_level_id : null, ['placeholder' => __('messages.student.select_year_level'),'class' => 'form-select io-select2', 'aria-label'=>"Select a Year Level",'data-control'=>'select2', 'id' => 'yearLevelSelect']) }}
                    </div>
                </div>

                {{-- Hidden inputs to store original year level data --}}
                {{ Form::hidden('all_year_levels', json_encode($data['year_levels']), ['id' => 'allYearLevels']) }}
            </div>
        </div>
        @endif

        <!-- Action Buttons -->
        <div class="card shadow-sm">
            <div class="card-body">
                <div class="d-flex justify-content-end gap-3">

                    <button type="submit" class="btn btn-primary btn-lg">
                        <i class="fas fa-save me-2"></i>
                        {{ __('messages.common.save') }}
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>

@if(getLogInUser()->hasRole('patient'))
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const isEmployeeCheckbox = document.getElementById('isEmployeeCheckbox');
        const isGuestCheckbox = document.getElementById('isGuestCheckbox');
        const yearLevelSelect = document.getElementById('yearLevelSelect');
        const yearLevelLabel = document.getElementById('yearLevelLabel');
        const yearLevelFieldContainer = document.getElementById('yearLevelFieldContainer');
        const courseFieldContainer = document.getElementById('courseFieldContainer');
        const courseSelect = document.getElementById('courseSelect');
        const courseFieldLabel = document.getElementById('courseFieldLabel');
        const campusFieldContainer = document.getElementById('campusFieldContainer');
        const collegeFieldContainer = document.getElementById('collegeFieldContainer');
        const positionFieldContainer = document.getElementById('positionFieldContainer');
        const positionTypeSelect = document.getElementById('positionTypeSelect');
        const departmentFieldContainer = document.getElementById('departmentFieldContainer');
        const departmentSelect = document.getElementById('departmentSelect');
        const officeFieldContainer = document.getElementById('officeFieldContainer');
        const officeSelect = document.getElementById('officeSelect');
        const collegeSelect = document.querySelector('#collegeFieldContainer select[name="college_id"]');
        const campusSelect = document.querySelector('#campusFieldContainer select[name="campus_id"]');

        const allYearLevels = JSON.parse(document.getElementById('allYearLevels').value);

        // Split year levels:
        // Student year levels (1-6), Faculty (7), Staff (8), Guest (9)
        const studentYearLevels = {};
        const facultyYearLevel = {};
        const staffYearLevel = {};
        const guestYearLevel = {};

        let count = 0;
        Object.entries(allYearLevels).forEach(([id, name]) => {
            count++;
            if (count >= 1 && count <= 6) {
                // Student (1-6)
                studentYearLevels[id] = name;
            } else if (count === 7) {
                // Faculty
                facultyYearLevel[id] = name;
            } else if (count === 8) {
                // Staff
                staffYearLevel[id] = name;
            } else if (count === 9) {
                // Guest
                guestYearLevel[id] = name;
            }
        });

        function updateFieldsDisplay() {
            const isEmployee = isEmployeeCheckbox.checked;
            const isGuest = isGuestCheckbox.checked;
            const positionType = $(positionTypeSelect).val();

            // Prevent both checkboxes from being checked
            if (isEmployee && isGuest) {
                if (this === isEmployeeCheckbox) {
                    isGuestCheckbox.checked = false;
                } else {
                    isEmployeeCheckbox.checked = false;
                }
            }

            // Update which year level options to show
            const currentValue = $(yearLevelSelect).val();
            $(yearLevelSelect).empty();

            let placeholderText, optionsToShow;

            if (isEmployee) {
                yearLevelFieldContainer.style.display = 'none';
                positionFieldContainer.style.display = 'block';
                campusFieldContainer.style.display = 'none';

                // Show/hide fields based on position type
                if (positionType === 'faculty') {
                    // Faculty (ID 7): Show College and Department, hide Course and Office
                    optionsToShow = facultyYearLevel;
                    collegeFieldContainer.style.display = 'block';
                    departmentFieldContainer.style.display = 'block';
                    courseFieldContainer.style.display = 'none';
                    officeFieldContainer.style.display = 'none';
                    $(courseSelect).val(null);
                    $(officeSelect).val(null);
                    $(campusSelect).val(null);
                } else if (positionType === 'staff') {
                    // Staff (ID 8): Show Office only, hide College, Course and Department
                    optionsToShow = staffYearLevel;
                    collegeFieldContainer.style.display = 'none';
                    departmentFieldContainer.style.display = 'none';
                    courseFieldContainer.style.display = 'none';
                    officeFieldContainer.style.display = 'block';
                    $(courseSelect).val(null);
                    $(departmentSelect).val(null);
                    $(collegeSelect).val(null);
                    $(campusSelect).val(null);
                } else {
                    // No position selected - hide all conditional fields
                    collegeFieldContainer.style.display = 'none';
                    courseFieldContainer.style.display = 'none';
                    departmentFieldContainer.style.display = 'none';
                    officeFieldContainer.style.display = 'none';
                    optionsToShow = {};
                }

                // Auto-select the appropriate year level for employee
                if (optionsToShow && Object.keys(optionsToShow).length > 0) {
                    Object.entries(optionsToShow).forEach(([value, text]) => {
                        const option = new Option(text, value, true, true);
                        $(yearLevelSelect).append(option);
                    });
                }
            } else if (isGuest) {
                // Guest (ID 9)
                optionsToShow = guestYearLevel;
                yearLevelFieldContainer.style.display = 'none';
                positionFieldContainer.style.display = 'none';
                campusFieldContainer.style.display = 'none';
                collegeFieldContainer.style.display = 'none';
                courseFieldContainer.style.display = 'none';
                departmentFieldContainer.style.display = 'none';
                officeFieldContainer.style.display = 'none';

                // Clear all fields - no dropdowns needed for guests (without triggering change events)
                $(courseSelect).val(null);
                $(officeSelect).val(null);
                $(positionTypeSelect).val(null);
                $(departmentSelect).val(null);
                $(collegeSelect).val(null);
                $(campusSelect).val(null);

                // Automatically select guest year level without showing dropdown
                $(yearLevelSelect).empty();
                Object.entries(guestYearLevel).forEach(([value, text]) => {
                    const option = new Option(text, value, true, true);
                    $(yearLevelSelect).append(option);
                });

                return; // Exit early - no need to populate dropdowns
            } else {
                // Student (IDs 1-6)
                optionsToShow = studentYearLevels;
                placeholderText = '{{ __("messages.student.select_year_level") }}';
                yearLevelFieldContainer.style.display = 'block';
                positionFieldContainer.style.display = 'none';
                campusFieldContainer.style.display = 'block';
                collegeFieldContainer.style.display = 'block';
                courseFieldContainer.style.display = 'block';
                departmentFieldContainer.style.display = 'none';
                officeFieldContainer.style.display = 'none';

                // Clear employee fields
                $(positionTypeSelect).val(null);
                $(officeSelect).val(null);
                $(departmentSelect).val(null);
            }

            // Populate year level dropdown if not hidden
            if (yearLevelFieldContainer.style.display !== 'none') {
                $(yearLevelSelect).append('<option value="">' + placeholderText + '</option>');
                Object.entries(optionsToShow).forEach(([value, text]) => {
                    const option = new Option(text, value, false, value == currentValue);
                    $(yearLevelSelect).append(option);
                });
            }

            // Trigger Select2 refresh for visible dropdowns to ensure values display correctly
            if (positionType === 'faculty') {
                $(collegeSelect).trigger('change.select2');
                $(departmentSelect).trigger('change.select2');
            } else if (positionType === 'staff') {
                $(officeSelect).trigger('change.select2');
            }

            // Year level select is already initialized with Select2, no need to refresh
        }

        // Make function globally accessible for edit page
        window.updateFieldsDisplay = updateFieldsDisplay;

        // Initialize on page load
        updateFieldsDisplay();

        // Force-set dropdown values using JavaScript since Form::select() isn't working properly
        setTimeout(function() {
            const positionType = $(positionTypeSelect).val();

            console.log('=== SETTING VALUES ===');

            // For Faculty - set department
            if (positionType === 'faculty' && departmentSelect) {
                const savedDeptId = '{{ $user->department_id ?? "" }}';
                console.log('Attempting to set department to:', savedDeptId);
                if (savedDeptId) {
                    // First check if the option exists
                    const optionExists = $(departmentSelect).find('option[value="' + savedDeptId + '"]').length > 0;
                    console.log('Department option exists?', optionExists);
                    if (optionExists) {
                        $(departmentSelect).val(savedDeptId).trigger('change');
                        console.log('Department set successfully');
                    }
                }
            }

            // For Staff - set office
            if (positionType === 'staff' && officeSelect) {
                const savedOfficeId = '{{ $user->office_id ?? "" }}';
                if (savedOfficeId) {
                    $(officeSelect).val(savedOfficeId).trigger('change');
                    console.log('Set office to:', savedOfficeId);
                }
            }

            // Address fields - set state (province)
            const savedStateId = '{{ !empty($patient->address) ? $patient->address->state_id : "" }}';
            console.log('Attempting to set state to:', savedStateId);
            if (savedStateId) {
                const stateOptionExists = $('#patientProfileStateId').find('option[value="' + savedStateId + '"]').length > 0;
                console.log('State option exists?', stateOptionExists);
                if (stateOptionExists) {
                    $('#patientProfileStateId').val(savedStateId).trigger('change');
                    console.log('State set successfully');

                    // After setting state, we need to load cities via AJAX, then set city value
                    setTimeout(function() {
                        const savedCityId = '{{ !empty($patient->address) ? $patient->address->city_id : "" }}';
                        console.log('Attempting to set city to:', savedCityId);
                        if (savedCityId) {
                            // Check if city option was loaded by AJAX
                            const cityOptionExists = $('#patientProfileCityId').find('option[value="' + savedCityId + '"]').length > 0;
                            console.log('City option exists?', cityOptionExists);
                            if (cityOptionExists) {
                                $('#patientProfileCityId').val(savedCityId).trigger('change');
                                console.log('City set successfully');
                            }
                        }
                    }, 1000); // Wait for AJAX to complete
                }
            }
        }, 800);

        // Update when checkboxes change
        isEmployeeCheckbox.addEventListener('change', updateFieldsDisplay);
        isGuestCheckbox.addEventListener('change', updateFieldsDisplay);

        // Update when position type changes (using off/on to prevent multiple bindings)
        $(positionTypeSelect).off('change').on('change', updateFieldsDisplay);
    });
</script>
@endif
@endsection