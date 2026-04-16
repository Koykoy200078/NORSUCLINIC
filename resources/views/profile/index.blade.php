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
        {{ Form::hidden('edit_patient_barangay_id', isset($patient->address->barangay_id) ? $patient->address->barangay_id : null, [
                'id' => 'editPatientProfileBarangayId',
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
                                'aria-label' => 'Select Province',
                                'data-control' => 'select2'
                            ]) }}
                    </div>
                    <div class="col-md-6 mb-7">
                        {{ Form::label('city_id', __('messages.city.city') . ':', ['class' => 'form-label fw-semibold']) }}
                        {{ Form::select('city_id', $data['cities'] ?? [], !empty($patient->address) ? $patient->address->city_id : null, ['id' => 'patientProfileCityId', 'class' => 'form-control form-control-lg io-select2', 'data-placeholder' => __('messages.common.select_city'), 'aria-label' => 'Select City', 'data-control' => 'select2']) }}
                    </div>
                    <div class="col-md-6 mb-7">
                        {{ Form::label('barangay_id', __('messages.barangay.barangay') . ':', ['class' => 'form-label fw-semibold']) }}
                        {{ Form::select('barangay_id', $data['barangays'] ?? [], !empty($patient->address) ? $patient->address->barangay_id : null, ['id' => 'patientProfileBarangayId', 'class' => 'form-control form-control-lg io-select2', 'data-placeholder' => __('messages.common.select_barangay'), 'aria-label' => 'Select Barangay', 'data-control' => 'select2']) }}
                    </div>
                    <div class="col-md-6 mb-7">
                        {{ Form::label('postalCode', __('messages.patient.postal_code') . ':', ['class' => 'form-label fw-semibold']) }}
                        {{ Form::text('postal_code', !empty($patient->address) ? $patient->address->postal_code : null, ['class' => 'form-control form-control-lg', 'placeholder' => __('messages.patient.postal_code')]) }}
                    </div>
                </div>
            </div>
        </div>

        @if(getLogInUser()->hasRole('patient'))
        <!-- Patient Information Card -->
        <div class="card shadow-sm mb-5">
            <div class="card-header">
                <h3 class="card-title fw-bold">
                    <i class="fas fa-graduation-cap text-primary me-2"></i>
                    {{ __('Patient Information') }}
                </h3>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6 mb-7">
                        {{ Form::label('patient_type_id', __('Patient Type').':',['class'=>'form-label fw-semibold']) }}
                        {{ Form::select('patient_type_id', $data['patient_types'] ?? [], !empty($patient) ? $patient->patient_type_id : null, ['placeholder' => __('Select Patient Type'),'class' => 'form-select io-select2', 'aria-label'=>'Select Patient Type', 'data-control'=>'select2', 'id' => 'patientTypeSelectProfile']) }}
                    </div>

                    <div class="col-md-6 mb-7" id="campusFieldContainerProfile">
                        {{ Form::label('campus_id',__('messages.student.campus').':',['class'=>'form-label']) }}
                        {{ Form::select('campus_id', $data['campuses'] ,!empty($patient->user) ? $patient->user->campus_id : null, ['placeholder' => __('messages.student.select_campus'),'class' => 'form-select io-select2', 'aria-label'=>"Select a Campus",'data-control'=>'select2', 'id' => 'campusSelectProfile']) }}
                    </div>

                    <div class="col-md-6 mb-7" id="collegeFieldContainerProfile">
                        {{ Form::label('college_id',__('messages.student.college').':',['class'=>'form-label']) }}
                        {{ Form::select('college_id', $data['colleges'] ,!empty($patient->user) ? $patient->user->college_id : null, ['placeholder' => __('messages.student.select_college'),'class' => 'form-select io-select2', 'aria-label'=>"Select a College",'data-control'=>'select2', 'id' => 'collegeSelectProfile']) }}
                    </div>

                    <div class="col-md-6 mb-7" id="courseFieldContainerProfile">
                        {{ Form::label('course_id',__('messages.student.course').':',['class'=>'form-label']) }}
                        {{ Form::select('course_id', $data['courses'] ,!empty($patient->user) ? $patient->user->course_id : null, ['placeholder' => __('messages.student.select_course'),'class' => 'form-select io-select2', 'aria-label'=>"Select a Course",'data-control'=>'select2', 'id' => 'courseSelectProfile']) }}
                    </div>

                    <div class="col-md-6 mb-7" id="departmentFieldContainerProfile" style="display: none;">
                        {{ Form::label('department_id',__('Department').':',['class'=>'form-label']) }}
                        {{ Form::select('department_id', $data['departments'] ?? [], $user->department_id ?? null, ['placeholder' => 'Select Department','class' => 'form-select io-select2', 'aria-label'=>"Select Department",'data-control'=>'select2', 'id' => 'departmentSelectProfile']) }}
                    </div>

                    <div class="col-md-6 mb-7" id="officeFieldContainerProfile" style="display: none;">
                        {{ Form::label('office_id',__('Office').':',['class'=>'form-label']) }}
                        {{ Form::select('office_id', $data['offices'] ?? [], $user->office_id ?? null, ['placeholder' => 'Select Office','class' => 'form-select io-select2', 'aria-label'=>"Select Office",'data-control'=>'select2', 'id' => 'officeSelectProfile']) }}
                    </div>

                    <div class="col-md-6 mb-7" id="yearLevelFieldContainerProfile">
                        {{ Form::label('year_level_id', __('messages.student.year_level').':',['class'=>'form-label']) }}
                        {{ Form::select('year_level_id', $data['year_levels'], !empty($patient->user) ? $patient->user->year_level_id : null, ['placeholder' => __('messages.student.select_year_level'),'class' => 'form-select io-select2', 'aria-label'=>"Select a Year Level",'data-control'=>'select2', 'id' => 'yearLevelSelectProfile']) }}
                    </div>
                </div>

                @php
                $patientTypeLookupProfile = collect($data['patient_types'] ?? [])->mapWithKeys(function ($name, $id) {
                $normalized = strtolower(trim((string) $name));
                if ($normalized === 'dependent') {
                $normalized = 'guest';
                }

                return [(string) $id => $normalized];
                })->toArray();
                @endphp

                {{ Form::hidden('all_year_levels', json_encode($data['year_levels']), ['id' => 'allYearLevelsProfile']) }}
                {{ Form::hidden('patient_type_lookup', json_encode($patientTypeLookupProfile), ['id' => 'patientTypeLookupProfile']) }}
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
        const patientTypeSelect = document.getElementById('patientTypeSelectProfile');
        const campusFieldContainer = document.getElementById('campusFieldContainerProfile');
        const collegeFieldContainer = document.getElementById('collegeFieldContainerProfile');
        const courseFieldContainer = document.getElementById('courseFieldContainerProfile');
        const departmentFieldContainer = document.getElementById('departmentFieldContainerProfile');
        const officeFieldContainer = document.getElementById('officeFieldContainerProfile');
        const yearLevelFieldContainer = document.getElementById('yearLevelFieldContainerProfile');

        const campusSelect = document.getElementById('campusSelectProfile');
        const collegeSelect = document.getElementById('collegeSelectProfile');
        const courseSelect = document.getElementById('courseSelectProfile');
        const departmentSelect = document.getElementById('departmentSelectProfile');
        const officeSelect = document.getElementById('officeSelectProfile');
        const yearLevelSelect = document.getElementById('yearLevelSelectProfile');

        const allYearLevels = JSON.parse(document.getElementById('allYearLevelsProfile').value || '{}');
        const patientTypeLookup = JSON.parse(document.getElementById('patientTypeLookupProfile').value || '{}');

        const studentYearLevels = {};
        let facultyYearLevelId = null;
        let staffYearLevelId = null;
        let guestYearLevelId = null;

        Object.entries(allYearLevels).forEach(([id, name]) => {
            const normalized = String(name).trim().toLowerCase();
            if (normalized.includes('faculty')) {
                facultyYearLevelId = id;
            } else if (normalized.includes('staff')) {
                staffYearLevelId = id;
            } else if (normalized.includes('guest')) {
                guestYearLevelId = id;
            } else {
                studentYearLevels[id] = name;
            }
        });

        function setVisible(container, visible) {
            if (!container) {
                return;
            }

            container.style.display = visible ? 'block' : 'none';
        }

        function clearSelectValue(selectElement) {
            if (!selectElement) {
                return;
            }

            $(selectElement).val(null).trigger('change');
        }

        function setSingleYearLevel(yearLevelId) {
            if (!yearLevelSelect) {
                return;
            }

            $(yearLevelSelect).empty();

            if (yearLevelId && allYearLevels[yearLevelId]) {
                const option = new Option(allYearLevels[yearLevelId], yearLevelId, true, true);
                $(yearLevelSelect).append(option);
                $(yearLevelSelect).val(yearLevelId).trigger('change');
                return;
            }

            $(yearLevelSelect).append(new Option('{{ __("messages.student.select_year_level") }}', '', true, true));
            $(yearLevelSelect).val('').trigger('change');
        }

        function setStudentYearLevels(currentValue) {
            if (!yearLevelSelect) {
                return;
            }

            const normalizedCurrentValue = Array.isArray(currentValue) ? currentValue[0] : currentValue;
            $(yearLevelSelect).empty();
            $(yearLevelSelect).append(new Option('{{ __("messages.student.select_year_level") }}', '', false, false));

            Object.entries(studentYearLevels).forEach(([value, text]) => {
                const option = new Option(text, value, false, value == normalizedCurrentValue);
                $(yearLevelSelect).append(option);
            });

            if (normalizedCurrentValue && studentYearLevels[normalizedCurrentValue]) {
                $(yearLevelSelect).val(normalizedCurrentValue).trigger('change');
                return;
            }

            $(yearLevelSelect).val('').trigger('change');
        }

        function updateFieldsDisplay() {
            const selectedPatientTypeId = String($(patientTypeSelect).val() || '');
            const selectedPatientType = String(patientTypeLookup[selectedPatientTypeId] || '').toLowerCase();

            const isStudent = selectedPatientType === 'student';
            const isFaculty = selectedPatientType === 'faculty';
            const isStaff = selectedPatientType === 'staff';
            const isGuest = selectedPatientType === 'guest';

            setVisible(campusFieldContainer, isStudent);
            setVisible(collegeFieldContainer, isStudent || isFaculty);
            setVisible(courseFieldContainer, isStudent);
            setVisible(departmentFieldContainer, isFaculty);
            setVisible(officeFieldContainer, isStaff);
            setVisible(yearLevelFieldContainer, isStudent);

            if (!isStudent) {
                clearSelectValue(campusSelect);
                clearSelectValue(courseSelect);
            }

            if (!(isStudent || isFaculty)) {
                clearSelectValue(collegeSelect);
            }

            if (!isFaculty) {
                clearSelectValue(departmentSelect);
            }

            if (!isStaff) {
                clearSelectValue(officeSelect);
            }

            const currentYearLevel = $(yearLevelSelect).val();

            if (isStudent) {
                setStudentYearLevels(currentYearLevel);
            } else if (isFaculty) {
                setSingleYearLevel(facultyYearLevelId);
            } else if (isStaff) {
                setSingleYearLevel(staffYearLevelId);
            } else if (isGuest) {
                setSingleYearLevel(guestYearLevelId);
            } else {
                setSingleYearLevel(null);
            }
        }

        window.updateFieldsDisplay = updateFieldsDisplay;
        updateFieldsDisplay();
        $(patientTypeSelect).off('change.patientType').on('change.patientType', updateFieldsDisplay);

        setTimeout(function() {
            const savedStateId = '{{ !empty($patient->address) ? $patient->address->state_id : "" }}';
            if (savedStateId) {
                const stateOptionExists = $('#patientProfileStateId').find('option[value="' + savedStateId + '"]').length > 0;
                if (stateOptionExists) {
                    $('#patientProfileStateId').val(savedStateId).trigger('change');

                    setTimeout(function() {
                        const savedCityId = '{{ !empty($patient->address) ? $patient->address->city_id : "" }}';
                        if (savedCityId) {
                            const cityOptionExists = $('#patientProfileCityId').find('option[value="' + savedCityId + '"]').length > 0;
                            if (cityOptionExists) {
                                $('#patientProfileCityId').val(savedCityId).trigger('change');
                            }
                        }
                    }, 1000);
                }
            }
        }, 800);
    });
</script>
@endif
@endsection