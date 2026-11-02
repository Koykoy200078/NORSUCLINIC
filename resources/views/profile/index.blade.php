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
        {{ html()->hidden('is_edit', true)->attributes(['id' => 'staffProfileIsEdit']) }}
        {{ html()->hidden('is_edit', true)->attributes(['id' => 'patientProfileIsEdit']) }}
        {{ html()->hidden('edit_patient_country_id', isset($patient->address->country_id) ? $patient->address->country_id : null)->attributes(['id' => 'editPatientProfileCountryId']) }}
        {{ html()->hidden('edit_patient_state_id', isset($patient->address->state_id) ? $patient->address->state_id : null)->attributes(['id' => 'editPatientProfileStateId']) }}
        {{ html()->hidden('edit_patient_city_id', isset($patient->address->city_id) ? $patient->address->city_id : null)->attributes(['id' => 'editPatientProfileCityId']) }}
        {{ html()->hidden('edit_patient_barangay_id', isset($patient->address->barangay_id) ? $patient->address->barangay_id : null)->attributes(['id' => 'editPatientProfileBarangayId']) }}
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
                        {{ html()->label()->for('firstName')->text(__('messages.patient.first_name').':')->attributes(['class' => 'form-label required fw-semibold']) }}
                        {{ html()->text('first_name', $user->first_name)->forgetAttribute('id')->attributes(['class'=> 'form-control form-control-lg', 'placeholder' => __('messages.patient.first_name'), 'required']) }}
                    </div>
                    <div class="col-md-4 mb-5">
                        {{ html()->label()->for('middleName')->text(__('messages.patient.middle_name').':')->attributes(['class' => 'form-label fw-semibold']) }}
                        {{ html()->text('middle_name', $user->middle_name)->forgetAttribute('id')->attributes(['class'=> 'form-control form-control-lg', 'placeholder' => __('messages.patient.middle_name')]) }}
                    </div>
                    <div class="col-md-4 mb-5">
                        {{ html()->label()->for('lastName')->text(__('messages.patient.last_name').':')->attributes(['class' => 'form-label required fw-semibold']) }}
                        {{ html()->text('last_name', $user->last_name)->forgetAttribute('id')->attributes(['class'=> 'form-control form-control-lg', 'placeholder' =>__('messages.patient.last_name'), 'required']) }}
                    </div>
                </div>

                <div class="separator separator-dashed my-6"></div>

                <div class="row">
                    <div class="col-md-6 mb-5">
                        {{ html()->label()->for('email')->text(__('messages.patient.email').':')->attributes(['class' => 'form-label required fw-semibold']) }}
                        {{ html()->email('email', $user->email)->attributes(['class'=> 'form-control form-control-lg', 'placeholder' => __('messages.user.email'), 'required']) }}
                    </div>
                    <div class="col-md-6 mb-5">
                        {{ html()->label()->for('contact')->text(__('messages.patient.contact_no').':')->attributes(['class' => 'form-label fw-semibold']) }}
                        {{ html()->text('contact', $user->contact)->attributes(['id' => 'phoneNumber', 'class' => 'form-control form-control-lg', 'data-ph-phone' => 'true', 'placeholder' => __('messages.user.contact_number')]) }}
                    </div>
                    <div class="col-md-6 mb-5">
                        {{ html()->label()->for('emergencyName')->text(__('messages.patient.emergency_contact_name').':')->attributes(['class' => 'form-label fw-semibold']) }}
                        {{ html()->text('emergency_contact_name', $user->emergency_contact_name)->forgetAttribute('id')->attributes(['class' => 'form-control form-control-lg', 'placeholder' => __('messages.patient.emergency_contact_name')]) }}
                    </div>
                    <div class="col-md-6 mb-5">
                        {{ html()->label()->for('emergencyNo')->text(__('messages.patient.emergency_contact_no').':')->attributes(['class' => 'form-label fw-semibold']) }}
                        {{ html()->text('emergency_contact_no', $user->emergency_contact_no)->forgetAttribute('id')->attributes(['class' => 'form-control form-control-lg', 'data-ph-phone' => 'true', 'placeholder' => __('messages.patient.emergency_contact_no')]) }}
                    </div>
                </div>

                <div class="separator separator-dashed my-6"></div>

                <div class="row">
                    <div class="col-md-6 mb-5">
                        {{ html()->label()->for('time_zone')->text(__('messages.user.time_zone').':')->attributes(['class' => 'form-label required fw-semibold']) }}
                        {{ html()->select('time_zone', App\Models\User::TIME_ZONE_ARRAY, $user->time_zone)->placeholder(__('messages.user.select_time_zone'))->attributes(['class'=> 'form-control form-control-lg io-select2', 'required', 'data-control'=>'select2']) }}
                    </div>
                    <div class="col-md-6 mb-5">
                        {{ html()->label()->for('gender')->text(__('messages.staff.gender') . ':')->attributes(['class' => 'form-label required fw-semibold']) }}
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
                        {{ html()->label()->for('dob')->text(__('messages.patient.dob') . ':')->attributes(['class' => 'form-label fw-semibold']) }}
                        {{ html()->text('dob', !empty($user) ? $user->dob : null)->attributes(['class' => 'form-control form-control-lg patient-dob', 'id' => __('messages.patient.dob'), 'placeholder' => __('messages.doctor.select_dob')]) }}
                    </div>
                    <div class="col-md-4 mb-5">
                        {{ html()->label()->for('blood_type')->text(__('messages.patient.blood_type') . ':')->attributes(['class' => 'form-label fw-semibold']) }}
                        {{ html()->select('blood_type', $data['bloodGroupList'], !empty($user) ? $user->blood_type : null)->placeholder(__('messages.patient.select_blood_type'))->attributes(['class' => 'form-control form-control-lg io-select2', 'aria-label' => 'Select a Blood Group', 'data-control' => 'select2']) }}
                    </div>
                    <div class="col-md-4 mb-5">
                        {{ html()->label()->for('vaccination_id')->text(__('messages.patient.vaccination_status').':')->attributes(['class'=>'form-label fw-semibold']) }}
                        {{ html()->select('vaccination_id', $data['vaccination_data'], !empty($user) ? $user->vaccination_id : null)->placeholder(__('messages.patient.vaccination_status'))->attributes(['class' => 'form-control form-control-lg io-select2', 'aria-label'=>"Select a Vaccination Status", 'data-control'=>'select2']) }}
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
                        {{ html()->label()->for('address1')->text(__('messages.patient.address1') . ':')->attributes(['class' => 'form-label fw-semibold']) }}
                        {{ html()->text('address1', !empty($patient->address) ? $patient->address->address1 : null)->attributes(['class' => 'form-control form-control-lg', 'placeholder' => __('messages.patient.address1')]) }}
                    </div>
                    <div class="col-md-6 mb-7">
                        {{ html()->label()->for('address2')->text(__('messages.patient.address2') . ':')->attributes(['class' => 'form-label fw-semibold']) }}
                        {{ html()->text('address2', !empty($patient->address) ? $patient->address->address2 : null)->attributes(['class' => 'form-control form-control-lg', 'placeholder' => __('messages.patient.address2')]) }}
                    </div>
                    <div class="col-md-6 mb-7">
                        {{ html()->label()->for('country_id')->text(__('messages.country.country') . ':')->attributes(['class' => 'form-label fw-semibold']) }}
                        {{ html()->select('country_id', $data['countries'], !empty($patient->address) ? $patient->address->country_id : null)->attributes(['id' => 'patientProfileCountryId', 'data-placeholder' => __('messages.country.country'), 'class' => 'form-control form-control-lg io-select2', 'aria-label' => 'Select a Country', 'data-control' => 'select2']) }}
                    </div>
                    <div class="col-md-6 mb-7">
                        {{ html()->label()->for('state_id')->text(__('messages.province.province') . ':')->attributes(['class' => 'form-label fw-semibold']) }}

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

                        {{ html()->select('state_id', $data['states'] ?? [], $stateIdValue)->attributes(['id' => 'patientProfileStateId', 'class' => 'form-control form-control-lg io-select2', 'data-placeholder' => __('messages.common.select_province'), 'aria-label' => 'Select Province', 'data-control' => 'select2']) }}
                    </div>
                    <div class="col-md-6 mb-7">
                        {{ html()->label()->for('city_id')->text(__('messages.city.city') . ':')->attributes(['class' => 'form-label fw-semibold']) }}
                        {{ html()->select('city_id', $data['cities'] ?? [], !empty($patient->address) ? $patient->address->city_id : null)->attributes(['id' => 'patientProfileCityId', 'class' => 'form-control form-control-lg io-select2', 'data-placeholder' => __('messages.common.select_city'), 'aria-label' => 'Select City', 'data-control' => 'select2']) }}
                    </div>
                    <div class="col-md-6 mb-7">
                        {{ html()->label()->for('barangay_id')->text(__('messages.barangay.barangay') . ':')->attributes(['class' => 'form-label fw-semibold']) }}
                        {{ html()->select('barangay_id', $data['barangays'] ?? [], !empty($patient->address) ? $patient->address->barangay_id : null)->attributes(['id' => 'patientProfileBarangayId', 'class' => 'form-control form-control-lg io-select2', 'data-placeholder' => __('messages.common.select_barangay'), 'aria-label' => 'Select Barangay', 'data-control' => 'select2']) }}
                    </div>
                    <div class="col-md-6 mb-7">
                        {{ html()->label()->for('postalCode')->text(__('messages.patient.postal_code') . ':')->attributes(['class' => 'form-label fw-semibold']) }}
                        {{ html()->text('postal_code', !empty($patient->address) ? $patient->address->postal_code : null)->forgetAttribute('id')->attributes(['class' => 'form-control form-control-lg', 'placeholder' => __('messages.patient.postal_code')]) }}
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
                        {{ html()->label()->for('patient_type_id')->text(__('Patient Type').':')->attributes(['class'=>'form-label fw-semibold']) }}
                        {{ html()->select('patient_type_id', $data['patient_types'] ?? [], !empty($patient) ? $patient->patient_type_id : null)->placeholder(__('Select Patient Type'))->attributes(['class' => 'form-select io-select2', 'aria-label'=>'Select Patient Type', 'data-control'=>'select2', 'id' => 'patientTypeSelectProfile']) }}
                    </div>

                    <div class="col-md-6 mb-7" id="campusFieldContainerProfile">
                        {{ html()->label()->for('campus_id')->text(__('messages.student.campus').':')->attributes(['class'=>'form-label']) }}
                        {{ html()->select('campus_id', $data['campuses'], !empty($patient->user) ? $patient->user->campus_id : null)->placeholder(__('messages.student.select_campus'))->attributes(['class' => 'form-select io-select2', 'aria-label'=>"Select a Campus", 'data-control'=>'select2', 'id' => 'campusSelectProfile']) }}
                    </div>

                    <div class="col-md-6 mb-7" id="collegeFieldContainerProfile">
                        {{ html()->label()->for('college_id')->text(__('messages.student.college').':')->attributes(['class'=>'form-label']) }}
                        {{ html()->select('college_id', $data['colleges'], !empty($patient->user) ? $patient->user->college_id : null)->placeholder(__('messages.student.select_college'))->attributes(['class' => 'form-select io-select2', 'aria-label'=>"Select a College", 'data-control'=>'select2', 'id' => 'collegeSelectProfile']) }}
                    </div>

                    <div class="col-md-6 mb-7" id="courseFieldContainerProfile">
                        {{ html()->label()->for('course_id')->text(__('messages.student.course').':')->attributes(['class'=>'form-label']) }}
                        {{ html()->select('course_id', $data['courses'], !empty($patient->user) ? $patient->user->course_id : null)->placeholder(__('messages.student.select_course'))->attributes(['class' => 'form-select io-select2', 'aria-label'=>"Select a Course", 'data-control'=>'select2', 'id' => 'courseSelectProfile']) }}
                    </div>

                    <div class="col-md-6 mb-7" id="departmentFieldContainerProfile" style="display: none;">
                        {{ html()->label()->for('department_id')->text(__('Department').':')->attributes(['class'=>'form-label']) }}
                        {{ html()->select('department_id', $data['departments'] ?? [], $user->department_id ?? null)->placeholder('Select Department')->attributes(['class' => 'form-select io-select2', 'aria-label'=>"Select Department", 'data-control'=>'select2', 'id' => 'departmentSelectProfile']) }}
                    </div>

                    <div class="col-md-6 mb-7" id="officeFieldContainerProfile" style="display: none;">
                        {{ html()->label()->for('office_id')->text(__('Office').':')->attributes(['class'=>'form-label']) }}
                        {{ html()->select('office_id', $data['offices'] ?? [], $user->office_id ?? null)->placeholder('Select Office')->attributes(['class' => 'form-select io-select2', 'aria-label'=>"Select Office", 'data-control'=>'select2', 'id' => 'officeSelectProfile']) }}
                    </div>

                    <div class="col-md-6 mb-7" id="yearLevelFieldContainerProfile">
                        {{ html()->label()->for('year_level_id')->text(__('messages.student.year_level').':')->attributes(['class'=>'form-label']) }}
                        {{ html()->select('year_level_id', $data['year_levels'], !empty($patient->user) ? $patient->user->year_level_id : null)->placeholder(__('messages.student.select_year_level'))->attributes(['class' => 'form-select io-select2', 'aria-label'=>"Select a Year Level", 'data-control'=>'select2', 'id' => 'yearLevelSelectProfile']) }}
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

                {{ html()->hidden('all_year_levels', json_encode($data['year_levels']))->attributes(['id' => 'allYearLevelsProfile']) }}
                {{ html()->hidden('patient_type_lookup', json_encode($patientTypeLookupProfile))->attributes(['id' => 'patientTypeLookupProfile']) }}
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