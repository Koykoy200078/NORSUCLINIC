    <!-- Account Information -->
    <div class="fw-bolder fs-3 mb-7 mt-5">{{ __('messages.patient.account_information') }}</div>
    <div class="col-lg-6 mt-5 d-none">
        <div class="mb-3" io-image-input="true">
            <label for="exampleInputImage" class="form-label">{{__('messages.patient.profile')}}:</label>
            <div class="d-block">
                <div class="image-picker">
                    <div class="image previewImage" id="exampleInputImage" style="background-image: url({{ !empty($patient->profile) ? $patient->profile : asset('web/media/avatars/male.png') }})">
                    </div>
                    <span class="picker-edit rounded-circle text-gray-500 fs-small" data-bs-toggle="tooltip"
                        data-placement="top" data-bs-original-title="{{ __('messages.user.edit_profile') }}">
                        <label>
                            <i class="fa-solid fa-pen" id="profileImageIcon"></i>
                            <input type="file" name="profile" id="profilePicture" class="image-upload d-none profile-validation" accept="image/*" />
                        </label>
                    </span>
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-md-6 mb-5">
            {{ html()->label()->for('patient_type_id')->text(__('Patient Type').':')->attributes(['class' => 'form-label required']) }}
            {{ html()->select('patient_type_id', $data['patient_types'] ?? [], !empty($patient) ? $patient->patient_type_id : old('patient_type_id'))->placeholder(__('Select Patient Type'))->attributes(['class' => 'form-select io-select2', 'aria-label'=>'Select Patient Type', 'data-control'=>'select2', 'id' => 'patientTypeSelect', 'required']) }}
        </div>
        <div class="col-md-6 mb-5 d-none" id="universityIdWrapper">
            {{ html()->label()->for('university_id_number')->text(__('University ID Number').':')->attributes(['class' => 'form-label required', 'id' => 'universityIdLabel']) }}
            {{ html()->text('university_id_number', !empty($patient->user) ? $patient->user->university_id_number : old('university_id_number'))->attributes(['class' => 'form-control', 'placeholder' => __('Student/Staff ID Number'), 'id' => 'universityIdInput']) }}
        </div>
        <div class="col-md-6 mb-5 d-none" id="emailWrapper">
            {{ html()->label()->for('email')->text(__('Email Address:'))->attributes(['class' => 'form-label']) }}
            {{ html()->email('email', !empty($patient->user) ? $patient->user->email : null)->attributes(['class' => 'form-control', 'placeholder' => __('Email Address'), 'id' => 'emailInput']) }}
        </div>
    </div>

    <!-- Personal Information -->
    <div class="fw-bolder fs-3 mb-7 mt-5">{{ __('messages.patient.personal_information') }}</div>
    <div class="row">
        <div class="col-md-6 mb-5">
            {{ html()->label()->for('firstName')->text(__('messages.patient.first_name').':')->attributes(['class' => 'form-label required']) }}
            {{ html()->text('first_name', !empty($patient->user) ? $patient->user->first_name : null)->forgetAttribute('id')->attributes(['class' => 'form-control', 'placeholder' => __('messages.patient.first_name'), 'required']) }}
        </div>
        <div class="col-md-6 mb-5">
            {{ html()->label()->for('middleName')->text(__('messages.patient.middle_name').':')->attributes(['class' => 'form-label']) }}
            {{ html()->text('middle_name', !empty($patient->user) ? $patient->user->middle_name : null)->forgetAttribute('id')->attributes(['class' => 'form-control', 'placeholder' => __('messages.patient.middle_name')]) }}
        </div>
        <div class="col-md-6 mb-5">
            {{ html()->label()->for('lastName')->text(__('messages.patient.last_name').':')->attributes(['class' => 'form-label required']) }}
            {{ html()->text('last_name', !empty($patient->user) ? $patient->user->last_name : null)->forgetAttribute('id')->attributes(['class' => 'form-control', 'placeholder' => __('messages.patient.last_name'), 'required']) }}
        </div>
        <div class="col-md-6 mb-5">
            {{ html()->label()->for('contact')->text(__('messages.patient.contact_no').':')->attributes(['class' => 'form-label']) }}
            @if (isset($patient))
            {{ html()->text('contact', !empty($patient->user) ? $patient->user->contact : null)->attributes(['class' => 'form-control', 'data-ph-phone' => 'true', 'placeholder' => __('messages.patient.contact_no')]) }}
            @else
            {{ html()->text('contact', old('contact'))->attributes(['class' => 'form-control', 'data-ph-phone' => 'true', 'placeholder' => __('messages.patient.contact_no')]) }}
            @endif
        </div>
        <div class="col-md-6 mb-5">
            {{ html()->label()->for('emergencyName')->text(__('messages.patient.emergency_contact_name').':')->attributes(['class' => 'form-label']) }}
            {{ html()->text('emergency_contact_name', !empty($patient->user) ? $patient->user->emergency_contact_name : null)->forgetAttribute('id')->attributes(['class' => 'form-control', 'placeholder' => __('messages.patient.emergency_contact_name'), 'required']) }}
        </div>

        <div class="col-md-6 mb-5">
            {{ html()->label()->for('emergencyNo')->text(__('messages.patient.emergency_contact_no').':')->attributes(['class' => 'form-label']) }}
            {{ html()->text('emergency_contact_no', !empty($patient->user) ? $patient->user->emergency_contact_no : null)->forgetAttribute('id')->attributes(['class' => 'form-control', 'data-ph-phone' => 'true', 'placeholder' => __('messages.patient.emergency_contact_no'), 'required']) }}
        </div>
        <div class="col-md-6 mb-5">
            {{ html()->label()->for('emergency_relationship')->text(__('Emergency Contact Relationship').':')->attributes(['class' => 'form-label']) }}
            {{ html()->text('emergency_relationship', !empty($patient->user) ? $patient->user->emergency_relationship : old('emergency_relationship'))->attributes(['class' => 'form-control', 'placeholder' => __('e.g. Mother, Father, Spouse, Guardian')]) }}
        </div>
        <div class="col-md-6 mb-5">
            {{ html()->label()->for('gender')->text(__('messages.staff.gender').':')->attributes(['class' => 'form-label required']) }}
            <span class="is-valid">
                <div class="mt-2">
                    <input class="form-check-input" type="radio" name="gender" value="1" checked
                        {{ !empty($patient->user) && $patient->user->gender === 1 ? 'checked' : '' }}>
                    <label class="form-label">{{ __('messages.staff.male') }}</label>&nbsp;&nbsp;
                    <input class="form-check-input" type="radio" name="gender" value="2"
                        {{ !empty($patient->user) && $patient->user->gender === 2 ? 'checked' : '' }}>
                    <label class="form-label">{{ __('messages.staff.female') }}</label>
                </div>
            </span>
        </div>
        <div class="col-md-6 mb-5">
            {{ html()->label()->for('dob')->text(__('messages.patient.dob').':')->attributes(['class' => 'form-label']) }}
            {{ html()->text('dob', !empty($patient->user) ? $patient->user->dob : null)->attributes(['class' => 'form-control patient-dob', 'id' => __('messages.patient.dob'), 'placeholder' => __('messages.doctor.select_dob')]) }}
        </div>
        <div class="col-md-6 mb-5">
            {{ html()->label()->for('nationality_citizenship')->text(__('Nationality/Citizenship').':')->attributes(['class' => 'form-label required']) }}
            {{ html()->text('nationality_citizenship', !empty($patient->user) ? $patient->user->nationality_citizenship : old('nationality_citizenship'))->attributes(['class' => 'form-control', 'placeholder' => __('Nationality/Citizenship'), 'required']) }}
        </div>
        <div class="col-md-6 mb-5">
            <label class="form-label">{{ __('messages.patient.blood_type').':' }}</label>
            {{ html()->select('blood_type', $data['bloodGroupList'], !empty($patient->user) ? $patient->user->blood_type : null)->placeholder(__('messages.patient.select_blood_type'))->forgetAttribute('id')->attributes(['class' => 'form-select io-select2', 'aria-label'=>"Select a Blood Type", 'data-control'=>'select2']) }}
        </div>
        <div class="col-md-6 mb-7">
            {{ html()->label()->for('vaccination_id')->text(__('messages.patient.vaccination_status').':')->attributes(['class'=>'form-label']) }}
            {{ html()->select('vaccination_id', $data['vaccination_data'], !empty($patient->user) ? $patient->user->vaccination_id : null)->placeholder(__('messages.patient.vaccination_status'))->attributes(['class' => 'form-select io-select2', 'aria-label'=>"Select a Vaccination Status", 'data-control'=>'select2']) }}
        </div>
    </div>

    <!-- Address Information -->
    <div class="row">
        <div class="fw-bolder fs-3 mb-7 mt-5">{{ __('messages.patient.address_information') }}
        </div>
        <div class="col-md-6 mb-7">
            {{ html()->label()->for('address1')->text(__('messages.patient.address1').':')->attributes(['class' => 'form-label']) }}
            {{ html()->text('address1', !empty($patient->address) ? $patient->address->address1 : null)->attributes(['class' => 'form-control', 'placeholder' => __('messages.patient.address1')]) }}
        </div>
        <div class="col-md-6 mb-7">
            {{ html()->label()->for('address2')->text(__('messages.patient.address2').':')->attributes(['class' => 'form-label']) }}
            {{ html()->text('address2', !empty($patient->address) ? $patient->address->address2 : null)->attributes(['class' => 'form-control', 'placeholder' => __('messages.patient.address2')]) }}
        </div>
        <div class="col-md-6 mb-7">
            {{ html()->label()->for('country_id')->text(__('messages.country.country').':')->attributes(['class'=>'form-label']) }}
            {{ html()->select('country_id', $data['countries'], null)->attributes(['id' => 'patientCountryId', 'data-placeholder' => __('messages.country.country'), 'class' => 'form-select io-select2', 'aria-label'=>"Select a Country", 'data-control'=>'select2']) }}
        </div>
        <div class="col-md-6 mb-7">
            {{ html()->label()->for('state_id')->text(__('messages.province.province').':')->attributes(['class'=>'form-label']) }}
            {{ html()->select('state_id', $data['provinces'], !empty($patient->address) ? $patient->address->state_id : null)->placeholder(__('messages.province.select_province'))->attributes(['id' => 'patientStateId', 'class' => 'form-select io-select2', 'aria-label'=>"Select a Province", 'data-control'=>'select2']) }}
        </div>
        <div class="col-md-6 mb-7">
            {{ html()->label()->for('city_id')->text(__('City/Municipality').':')->attributes(['class'=>'form-label']) }}
            {{ html()->select('city_id', isset($patient) && !empty($patient->address) ? $data['cities'] : [], !empty($patient->address) ? $patient->address->city_id : null)->placeholder(__('City/Municipality'))->attributes(['id' => 'patientCityId', 'class' => 'form-select io-select2', 'aria-label'=>"Select a City", 'data-control'=>'select2']) }}
        </div>
        <div class="col-md-6 mb-7">
            {{ html()->label()->for('barangay_id')->text(__('Barangay').':')->attributes(['class'=>'form-label']) }}
            {{ html()->select('barangay_id', isset($patient) && !empty($patient->address) ? ($data['barangays'] ?? []) : [], !empty($patient->address) ? $patient->address->barangay_id : null)->placeholder(__('Select Barangay'))->attributes(['id' => 'patientBarangayId', 'class' => 'form-select io-select2', 'aria-label'=>"Select a Barangay", 'data-control'=>'select2']) }}
        </div>
        <div class="col-md-6 mb-7">
            {{ html()->label()->for('postalCode')->text(__('messages.patient.postal_code').':')->attributes(['class' => 'form-label']) }}
            {{ html()->number('postal_code', !empty($patient->address) ? $patient->address->postal_code : null)->forgetAttribute('id')->attributes(['class' => 'form-control', 'placeholder' => __('messages.patient.postal_code'), 'inputmode' => 'numeric', 'pattern' => '[0-9]*', 'oninput' => "this.value = this.value.replace(/[^0-9]/g, '')"]) }}
        </div>
    </div>

    <!-- Patient Information -->
    <div id="patientInformationHeadingContainer">
        <div class="fw-bolder fs-3 mb-7 mt-5">{{ __('Patient Information') }}</div>
    </div>
    <div class="row">
        <div class="col-md-6 mb-7" id="campusFieldContainer">
            {{ html()->label()->for('campus_id')->text(__('messages.student.campus').':')->attributes(['class'=>'form-label']) }}
            {{ html()->select('campus_id', $data['campuses'], !empty($patient->user) ? $patient->user->campus_id : null)->placeholder(__('messages.student.select_campus'))->attributes(['class' => 'form-select io-select2', 'aria-label'=>"Select a Campus", 'data-control'=>'select2', 'id' => 'campusSelect']) }}
        </div>

        <div class="col-md-6 mb-7" id="collegeFieldContainer">
            {{ html()->label()->for('college_id')->text(__('messages.student.college').':')->attributes(['class'=>'form-label']) }}
            {{ html()->select('college_id', $data['colleges'], !empty($patient->user) ? $patient->user->college_id : null)->placeholder(__('messages.student.select_college'))->attributes(['class' => 'form-select io-select2', 'aria-label'=>"Select a College", 'data-control'=>'select2', 'id' => 'collegeSelect']) }}
        </div>

        <div class="col-md-6 mb-7" id="courseFieldContainer">
            {{ html()->label()->for('course_id')->text(__('messages.student.course').':')->attributes(['class'=>'form-label']) }}
            {{ html()->select('course_id', $data['courses'], !empty($patient->user) ? $patient->user->course_id : null)->placeholder(__('messages.student.select_course'))->attributes(['class' => 'form-select io-select2', 'aria-label'=>"Select a Course", 'data-control'=>'select2', 'id' => 'courseSelect']) }}
        </div>

        <div class="col-md-6 mb-7" id="departmentFieldContainer" style="display: none;">
            {{ html()->label()->for('department_id')->text(__('Department').':')->attributes(['class'=>'form-label']) }}
            {{ html()->select('department_id', $data['departments'] ?? [], !empty($patient->user) ? $patient->user->department_id : null)->placeholder('Select Department')->attributes(['class' => 'form-select io-select2', 'aria-label'=>"Select Department", 'data-control'=>'select2', 'id' => 'departmentSelect']) }}
        </div>

        <div class="col-md-6 mb-7" id="officeFieldContainer" style="display: none;">
            {{ html()->label()->for('office_id')->text(__('Office').':')->attributes(['class'=>'form-label']) }}
            {{ html()->select('office_id', $data['offices'] ?? [], !empty($patient->user) ? $patient->user->office_id : null)->placeholder('Select Office')->attributes(['class' => 'form-select io-select2', 'aria-label'=>"Select Office", 'data-control'=>'select2', 'id' => 'officeSelect']) }}
        </div>

        <div class="col-md-6 mb-7" id="yearLevelFieldContainer">
            {{ html()->label()->for('year_level_id')->text(__('messages.student.year_level').':')->attributes(['class'=>'form-label']) }}
            {{ html()->select('year_level_id', $data['year_levels'], !empty($patient->user) ? $patient->user->year_level_id : null)->placeholder(__('messages.student.select_year_level'))->attributes(['class' => 'form-select io-select2', 'aria-label'=>"Select a Year Level", 'data-control'=>'select2', 'id' => 'yearLevelSelect']) }}
        </div>
    </div>

    <div class="fw-bolder fs-3 mb-7 mt-5">{{ __('Patient Record Details') }}</div>
    <div class="row">
        <div class="col-md-12 mb-5">
            {{ html()->label()->for('immunization_record')->text(__('Immunization Record').':')->attributes(['class' => 'form-label required']) }}
            {{ html()->textarea('immunization_record')->text(html()->value('immunization_record', !empty($patient) ? $patient->immunization_record : old('immunization_record')))->attributes(['cols' => 50])->attributes(['class' => 'form-control', 'rows' => 3, 'placeholder' => __('Include required vaccines (Hepatitis B, MMR, COVID-19, etc.)'), 'required']) }}
        </div>
        <div class="col-md-6 mb-5">
            {{ html()->label()->for('insurance_provider_id')->text(__('Health Insurance Provider').':')->attributes(['class' => 'form-label']) }}
            {{ html()->select('insurance_provider_id', $data['insurance_providers'] ?? [], !empty($patient) ? $patient->insurance_provider_id : old('insurance_provider_id'))->placeholder(__('Select Insurance Provider'))->attributes(['class' => 'form-select io-select2', 'aria-label' => 'Select Insurance Provider', 'data-control' => 'select2']) }}
        </div>
        <div class="col-md-6 mb-5">
            {{ html()->label()->for('insurance_policy_number')->text(__('Insurance Policy Number').':')->attributes(['class' => 'form-label']) }}
            {{ html()->text('insurance_policy_number', !empty($patient) ? $patient->insurance_policy_number : old('insurance_policy_number'))->attributes(['class' => 'form-control', 'placeholder' => __('Policy Number')]) }}
        </div>
        <div class="col-md-6 mb-5">
            {{ html()->label()->for('primary_care_physician_name')->text(__('Primary Care Physician (PCP)').':')->attributes(['class' => 'form-label']) }}
            {{ html()->text('primary_care_physician_name', !empty($patient) ? $patient->primary_care_physician_name : old('primary_care_physician_name'))->attributes(['class' => 'form-control', 'placeholder' => __('Primary Care Physician Name')]) }}
        </div>
        <div class="col-md-6 mb-5">
            {{ html()->label()->for('primary_care_physician_contact')->text(__('PCP Contact Number').':')->attributes(['class' => 'form-label']) }}
            {{ html()->text('primary_care_physician_contact', !empty($patient) ? $patient->primary_care_physician_contact : old('primary_care_physician_contact'))->attributes(['class' => 'form-control', 'placeholder' => __('PCP Contact Number')]) }}
        </div>
        <div class="col-md-6 mb-5">
            {{ html()->label()->for('primary_care_physician_email')->text(__('PCP Email').':')->attributes(['class' => 'form-label']) }}
            {{ html()->email('primary_care_physician_email', !empty($patient) ? $patient->primary_care_physician_email : old('primary_care_physician_email'))->attributes(['class' => 'form-control', 'placeholder' => __('PCP Email Address')]) }}
        </div>
    </div>

    @php
    $patientTypeLookup = collect($data['patient_types'] ?? [])->mapWithKeys(function ($name, $id) {
    $normalized = strtolower(trim((string) $name));
    if ($normalized === 'dependent') {
    $normalized = 'guest';
    }

    return [(string) $id => $normalized];
    })->toArray();
    @endphp

    {{ html()->hidden('all_year_levels', json_encode($data['year_levels']))->attributes(['id' => 'allYearLevels']) }}
    {{ html()->hidden('patient_type_lookup', json_encode($patientTypeLookup))->attributes(['id' => 'patientTypeLookup']) }}

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const patientTypeSelect = document.getElementById('patientTypeSelect');
            const campusFieldContainer = document.getElementById('campusFieldContainer');
            const collegeFieldContainer = document.getElementById('collegeFieldContainer');
            const courseFieldContainer = document.getElementById('courseFieldContainer');
            const departmentFieldContainer = document.getElementById('departmentFieldContainer');
            const officeFieldContainer = document.getElementById('officeFieldContainer');
            const yearLevelFieldContainer = document.getElementById('yearLevelFieldContainer');
            const universityIdFieldContainer = document.getElementById('universityIdFieldContainer');
            const patientInformationHeadingContainer = document.getElementById('patientInformationHeadingContainer');
            const universityIdInput = document.querySelector('#universityIdFieldContainer input[name="university_id_number"]');

            const campusSelect = document.getElementById('campusSelect');
            const collegeSelect = document.getElementById('collegeSelect');
            const courseSelect = document.getElementById('courseSelect');
            const departmentSelect = document.getElementById('departmentSelect');
            const officeSelect = document.getElementById('officeSelect');
            const yearLevelSelect = document.getElementById('yearLevelSelect');
            const vaccinationStatusSelect = document.querySelector('select[name="vaccination_id"]');
            const immunizationRecordTextarea = document.querySelector('textarea[name="immunization_record"]');

            const allYearLevels = JSON.parse(document.getElementById('allYearLevels').value || '{}');
            const patientTypeLookup = JSON.parse(document.getElementById('patientTypeLookup').value || '{}');

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
                setVisible(universityIdFieldContainer, !isGuest);
                setVisible(patientInformationHeadingContainer, selectedPatientTypeId !== '' && !isGuest);

                if (universityIdInput) {
                    if (isGuest) {
                        universityIdInput.removeAttribute('required');
                        universityIdInput.value = '';
                    } else {
                        universityIdInput.setAttribute('required', 'required');
                    }
                }

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

            function syncVaccinationStatusToImmunizationRecord() {
                if (!vaccinationStatusSelect || !immunizationRecordTextarea) {
                    return;
                }

                const selectedValue = String(vaccinationStatusSelect.value || '').trim();
                if (selectedValue === '') {
                    return;
                }

                const selectedText = vaccinationStatusSelect.options[vaccinationStatusSelect.selectedIndex]?.text?.trim() || '';
                if (selectedText === '') {
                    return;
                }

                const covidLine = `COVID-19 Vaccination Status: ${selectedText}`;
                const existingLines = (immunizationRecordTextarea.value || '')
                    .split(/\r?\n/)
                    .map((line) => line.trim())
                    .filter((line) => line !== '');

                let hasCovidLine = false;
                const updatedLines = [];

                existingLines.forEach((line) => {
                    if (/^covid(?:-19)?\s+vaccination\s+status\s*:/i.test(line)) {
                        if (!hasCovidLine) {
                            updatedLines.push(covidLine);
                            hasCovidLine = true;
                        }

                        return;
                    }

                    updatedLines.push(line);
                });

                if (!hasCovidLine) {
                    updatedLines.push(covidLine);
                }

                immunizationRecordTextarea.value = updatedLines.join('\n');
            }

            if (vaccinationStatusSelect && immunizationRecordTextarea) {
                vaccinationStatusSelect.addEventListener('change', syncVaccinationStatusToImmunizationRecord);

                if (String(vaccinationStatusSelect.value || '').trim() !== '') {
                    syncVaccinationStatusToImmunizationRecord();
                }
            }

            const $comorbiditySelect = $('#patientComorbidities');
            if ($comorbiditySelect.length && $.fn.select2) {
                if ($comorbiditySelect.hasClass('select2-hidden-accessible')) {
                    $comorbiditySelect.select2('destroy');
                }

                $comorbiditySelect.select2({
                    tags: true,
                    tokenSeparators: [','],
                    placeholder: 'Select or type comorbidities',
                    width: '100%'
                });
            }
        });
    </script>

    <!-- Medical History Information -->
    <div class="fw-bolder fs-3 mb-7 mt-5">{{ __('Medical History & Allergies') }}</div>
    <div class="row">
        <div class="col-md-12 mb-5">
            {{ html()->label()->for('allergies')->text('Allergies:')->attributes(['class' => 'form-label']) }}
            {{ html()->textarea('allergies')->text(html()->value('allergies', !empty($patient) ? $patient->allergies : null))->attributes(['cols' => 50])->attributes(['class' => 'form-control', 'rows' => 3, 'placeholder' => 'List any allergies (Drug, Food, etc.)']) }}
        </div>
        <div class="col-md-12 mb-5">
            {{ html()->label()->for('comorbidities')->text('Comorbidities/Underlying Conditions:')->attributes(['class' => 'form-label']) }}
            @php
            $comorbidityOptions = collect($data['comorbidities'] ?? [])->mapWithKeys(function ($value) {
            return [$value => $value];
            })->toArray();

            $selectedComorbidities = old('comorbidities');
            if ($selectedComorbidities === null && !empty($patient) && !empty($patient->comorbidities)) {
            $decoded = json_decode($patient->comorbidities, true);
            if (is_array($decoded)) {
            $selectedComorbidities = $decoded;
            } else {
            $selectedComorbidities = array_values(array_filter(array_map('trim', explode(',', $patient->comorbidities))));
            }
            }

            if (!empty($selectedComorbidities) && is_array($selectedComorbidities)) {
            foreach ($selectedComorbidities as $selectedComorbidity) {
            $selectedComorbidity = trim((string) $selectedComorbidity);
            if ($selectedComorbidity !== '' && !isset($comorbidityOptions[$selectedComorbidity])) {
            $comorbidityOptions[$selectedComorbidity] = $selectedComorbidity;
            }
            }
            }
            @endphp
            {{ html()->select('comorbidities[]', $comorbidityOptions, old('comorbidities', $selectedComorbidities))->multiple()->attributes(['class' => 'form-select io-select2', 'data-control' => 'select2', 'data-placeholder' => 'Select comorbidities', 'id' => 'patientComorbidities']) }}
        </div>
        <div class="col-md-12 mb-5">
            {{ html()->label()->for('admissions_surgeries')->text('Hospital Admissions / Surgeries:')->attributes(['class' => 'form-label']) }}
            {{ html()->textarea('admissions_surgeries')->text(html()->value('admissions_surgeries', !empty($patient) ? $patient->admissions_surgeries : null))->attributes(['cols' => 50])->attributes(['class' => 'form-control', 'rows' => 3, 'placeholder' => 'List previous admissions or surgeries if any']) }}
        </div>
        <div class="col-md-12 mb-5">
            {{ html()->label()->for('maintenance')->text('Maintenance/Current Medications:')->attributes(['class' => 'form-label']) }}
            {{ html()->textarea('maintenance')->text(html()->value('maintenance', !empty($patient) ? $patient->maintenance : null))->attributes(['cols' => 50])->attributes(['class' => 'form-control', 'rows' => 3, 'placeholder' => 'List current maintenance medications']) }}
        </div>
    </div>

    <div>
        {{ html()->input('submit', null, __('messages.common.save'))->attributes(['class' => 'btn btn-primary me-2']) }}
        <a href="{{ getRouteByRole('patients.index') }}" type="reset"
            class="btn btn-secondary">{{__('messages.common.discard')}}</a>
    </div>

    @push('scripts')
    <script>
        (function() {
            'use strict';

            function initPatientTypeToggle() {
                var $patientTypeSelect = $('#patientTypeSelect');
                var $universityIdWrapper = $('#universityIdWrapper');
                var $universityIdInput = $('#universityIdInput');
                var $emailWrapper = $('#emailWrapper');

                if (!$patientTypeSelect.length) {
                    console.warn('[PatientForm] patientTypeSelect not found in DOM');
                    return false;
                }
                if (!$universityIdWrapper.length) {
                    console.warn('[PatientForm] universityIdWrapper not found in DOM');
                }
                if (!$emailWrapper.length) {
                    console.warn('[PatientForm] emailWrapper not found in DOM');
                }

                // Patient type IDs from seeder: 1=student, 2=staff, 3=faculty, 4=guest
                var GUEST_TYPE_ID = '4';

                function toggleConditionalFields() {
                    var rawVal = $patientTypeSelect.val();
                    var selectedType = (rawVal !== null && rawVal !== undefined) ? String(rawVal) : '';

                    var isGuest = selectedType === GUEST_TYPE_ID;
                    var hasSelection = selectedType !== '';

                    if (!hasSelection) {
                        $universityIdWrapper.addClass('d-none');
                        $emailWrapper.addClass('d-none');
                        $universityIdInput.removeAttr('required');

                        return;
                    }

                    if (isGuest) {
                        $universityIdWrapper.addClass('d-none');
                        $emailWrapper.removeClass('d-none');
                        $universityIdInput.removeAttr('required');

                    } else {
                        $universityIdWrapper.removeClass('d-none');
                        $emailWrapper.removeClass('d-none');
                        $universityIdInput.attr('required', 'required');

                    }
                }

                // Bind to Select2 events
                $patientTypeSelect.on('change.select2', function(e) {

                    toggleConditionalFields();
                });
                $patientTypeSelect.on('select2:select', function(e) {

                    toggleConditionalFields();
                });
                $patientTypeSelect.on('change', function(e) {

                    toggleConditionalFields();
                });

                // Run immediately and delayed for Select2 initialization
                toggleConditionalFields();
                setTimeout(function() {

                    toggleConditionalFields();
                }, 300);

                return true;
            }

            if (typeof jQuery !== 'undefined') {
                jQuery(function($) {
                    var ok = initPatientTypeToggle();
                    if (!ok) {
                        setTimeout(function() {
                            initPatientTypeToggle();
                        }, 500);
                    }
                });
            } else {
                console.error('[PatientForm] jQuery is not loaded');
            }
        })();
    </script>
    @endpush