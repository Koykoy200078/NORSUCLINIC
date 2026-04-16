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
            {{ Form::label('university_id_number',__('University ID Number').':' ,['class' => 'form-label required']) }}
            {{ Form::text('university_id_number', !empty($patient->user) ? $patient->user->university_id_number : old('university_id_number'), ['class' => 'form-control','placeholder' => __('Student/Staff ID Number'),'required']) }}
        </div>
        <div class="col-md-6 mb-5">
            {{ Form::label('email',__('Email Address:') ,['class' => 'form-label']) }}
            {{ Form::email('email',!empty($patient->user) ? $patient->user->email : null,['class' => 'form-control','placeholder' => __('Email Address')]) }}
        </div>

    </div>

    <!-- Personal Information -->
    <div class="fw-bolder fs-3 mb-7 mt-5">{{ __('messages.patient.personal_information') }}</div>
    <div class="row">
        <div class="col-md-6 mb-5">
            {{ Form::label('firstName',__('messages.patient.first_name').':' ,['class' => 'form-label required']) }}
            {{ Form::text('first_name',!empty($patient->user) ? $patient->user->first_name : null,['class' => 'form-control','placeholder' => __('messages.patient.first_name'),'required']) }}
        </div>
        <div class="col-md-6 mb-5">
            {{ Form::label('middleName',__('messages.patient.middle_name').':' ,['class' => 'form-label']) }}
            {{ Form::text('middle_name',!empty($patient->user) ? $patient->user->middle_name : null,['class' => 'form-control','placeholder' => __('messages.patient.middle_name')]) }}
        </div>
        <div class="col-md-6 mb-5">
            {{ Form::label('lastName',__('messages.patient.last_name').':' ,['class' => 'form-label required']) }}
            {{ Form::text('last_name',!empty($patient->user) ? $patient->user->last_name : null,['class' => 'form-control','placeholder' => __('messages.patient.last_name'),'required']) }}
        </div>
        <div class="col-md-6 mb-5">
            {{ Form::label('contact', __('messages.patient.contact_no').':', ['class' => 'form-label']) }}
            @if (isset($patient))
            {{ Form::tel('contact', !empty($patient->user) ? $patient->user->contact : null, ['class' => 'form-control',
                'placeholder' => __('messages.patient.contact_no'),'onkeyup' => 'if (/\D/g.test(this.value)) this.value = this.value.replace(/\D/g,"")','id'=>'phoneNumber']) }}
            {{ Form::hidden('country_code',!empty($patient->user) ? $patient->user->country_code : null,['id'=>'prefix_code']) }}
            <span id="valid-msg" class="text-success d-none fw-400 fs-small mt-2">{{ __('messages.valid_number') }}</span>
            <span id="error-msg" class="text-danger d-none fw-400 fs-small mt-2">{{ __('messages.invalid_number') }}</span>
            @else
            {{ Form::text('contact', old('contact'), ['class' => 'form-control',
                'placeholder' => __('messages.patient.contact_no'),'onkeyup' => 'if (/\D/g.test(this.value)) this.value = this.value.replace(/\D/g,"")','id'=>'patientContactNumber']) }}
            {{ Form::hidden('country_code', old('country_code', getSettingValue('country_code')), ['id'=>'prefix_code']) }}
            @endif
        </div>
        <div class="col-md-6 mb-5">
            {{ Form::label('emergencyName',__('messages.patient.emergency_contact_name').':' ,['class' => 'form-label']) }}
            {{ Form::text('emergency_contact_name',!empty($patient->user) ? $patient->user->emergency_contact_name : null,['class' => 'form-control','placeholder' => __('messages.patient.emergency_contact_name'),'required']) }}
        </div>

        <div class="col-md-6 mb-5">
            {{ Form::label('emergencyNo',__('messages.patient.emergency_contact_no').':' ,['class' => 'form-label']) }}
            {{ Form::text('emergency_contact_no',!empty($patient->user) ? $patient->user->emergency_contact_no : null,['class' => 'form-control','placeholder' => __('messages.patient.emergency_contact_no'),'required']) }}
        </div>
        <div class="col-md-6 mb-5">
            {{ Form::label('emergency_relationship', __('Emergency Contact Relationship').':', ['class' => 'form-label']) }}
            {{ Form::text('emergency_relationship', !empty($patient->user) ? $patient->user->emergency_relationship : old('emergency_relationship'), ['class' => 'form-control', 'placeholder' => __('e.g. Mother, Father, Spouse, Guardian')]) }}
        </div>
        <div class="col-md-6 mb-5">
            {{ Form::label('gender', __('messages.staff.gender').':', ['class' => 'form-label required']) }}
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
            {{ Form::label('dob',__('messages.patient.dob').':' ,['class' => 'form-label']) }}
            {{ Form::text('dob',!empty($patient->user) ? $patient->user->dob : null,['class' => 'form-control patient-dob','id' => __('messages.patient.dob'), 'placeholder' => __('messages.doctor.select_dob')]) }}
        </div>
        <div class="col-md-6 mb-5">
            {{ Form::label('nationality_citizenship',__('Nationality/Citizenship').':' ,['class' => 'form-label required']) }}
            {{ Form::text('nationality_citizenship', !empty($patient->user) ? $patient->user->nationality_citizenship : old('nationality_citizenship'), ['class' => 'form-control','placeholder' => __('Nationality/Citizenship'),'required']) }}
        </div>
        <div class="col-md-6 mb-5">
            {{ Form::label('patient_type_id',__('Patient Type').':' ,['class' => 'form-label required']) }}
            {{ Form::select('patient_type_id', $data['patient_types'] ?? [], !empty($patient) ? $patient->patient_type_id : old('patient_type_id'), ['placeholder' => __('Select Patient Type'),'class' => 'form-select io-select2', 'aria-label'=>'Select Patient Type', 'data-control'=>'select2', 'id' => 'patientTypeSelect', 'required']) }}
        </div>
        <div class="col-md-6 mb-5">
            <label class="form-label">{{ __('messages.patient.blood_type').':' }}</label>
            {{ Form::select('blood_type', $data['bloodGroupList'] ,!empty($patient->user) ? $patient->user->blood_type : null, ['placeholder' => __('messages.patient.select_blood_type'),'class' => 'form-select io-select2', 'aria-label'=>"Select a Blood Type",'data-control'=>'select2']) }}
        </div>
        <div class="col-md-6 mb-7">
            {{ Form::label('vaccination_id',__('messages.patient.vaccination_status').':',['class'=>'form-label']) }}
            {{ Form::select('vaccination_id', $data['vaccination_data'] ,!empty($patient->user) ? $patient->user->vaccination_id : null, ['placeholder' => __('messages.patient.vaccination_status'),'class' => 'form-select io-select2', 'aria-label'=>"Select a Vaccination Status",'data-control'=>'select2']) }}
        </div>
    </div>

    <!-- Address Information -->
    <div class="row">
        <div class="fw-bolder fs-3 mb-7 mt-5">{{ __('messages.patient.address_information') }}
        </div>
        <div class="col-md-6 mb-7">
            {{ Form::label('address1',__('messages.patient.address1').':' ,['class' => 'form-label']) }}
            {{ Form::text('address1',!empty($patient->address) ? $patient->address->address1 : null,['class' => 'form-control','placeholder' => __('messages.patient.address1')]) }}
        </div>
        <div class="col-md-6 mb-7">
            {{ Form::label('address2',__('messages.patient.address2').':' ,['class' => 'form-label']) }}
            {{ Form::text('address2',!empty($patient->address) ? $patient->address->address2 : null,['class' => 'form-control','placeholder' => __('messages.patient.address2')]) }}
        </div>
        <div class="col-md-6 mb-7">
            {{ Form::label('country_id',__('messages.country.country').':',['class'=>'form-label']) }}
            {{ Form::select('country_id', $data['countries'] ,null, ['id' => 'patientCountryId','data-placeholder' => __('messages.country.country'),'class' => 'form-select io-select2', 'aria-label'=>"Select a Country",
        'data-control'=>'select2']) }}
        </div>
        <div class="col-md-6 mb-7">
            {{ Form::label('state_id',__('messages.province.province').':',['class'=>'form-label']) }}
            {{ Form::select('state_id', $data['provinces'] ,!empty($patient->address) ? $patient->address->state_id : null, ['id' => 'patientStateId', 'placeholder' => __('messages.province.select_province'),'class' => 'form-select io-select2', 'aria-label'=>"Select a Province",'data-control'=>'select2']) }}
        </div>
        <div class="col-md-6 mb-7">
            {{ Form::label('city_id',__('City/Municipality').':',['class'=>'form-label']) }}
            {{ Form::select('city_id', isset($patient) && !empty($patient->address) ? $data['cities'] : [] ,!empty($patient->address) ? $patient->address->city_id : null, ['id' => 'patientCityId', 'placeholder' => __('City/Municipality'),'class' => 'form-select io-select2', 'aria-label'=>"Select a City",'data-control'=>'select2']) }}
        </div>
        <div class="col-md-6 mb-7">
            {{ Form::label('barangay_id',__('Barangay').':',['class'=>'form-label']) }}
            {{ Form::select('barangay_id', isset($patient) && !empty($patient->address) ? ($data['barangays'] ?? []) : [] ,!empty($patient->address) ? $patient->address->barangay_id : null, ['id' => 'patientBarangayId', 'placeholder' => __('Select Barangay'),'class' => 'form-select io-select2', 'aria-label'=>"Select a Barangay",'data-control'=>'select2']) }}
        </div>
        <div class="col-md-6 mb-7">
            {{ Form::label('postalCode',__('messages.patient.postal_code').':' ,['class' => 'form-label']) }}
            {{ Form::text('postal_code',!empty($patient->address) ? $patient->address->postal_code : null,['class' => 'form-control','placeholder' => __('messages.patient.postal_code')]) }}
        </div>
    </div>

    <!-- Patient Information -->
    <div class="fw-bolder fs-3 mb-7 mt-5">{{ __('Patient Information') }}</div>
    <div class="row">
        <div class="col-md-6 mb-7" id="campusFieldContainer">
            {{ Form::label('campus_id',__('messages.student.campus').':',['class'=>'form-label']) }}
            {{ Form::select('campus_id', $data['campuses'] ,!empty($patient->user) ? $patient->user->campus_id : null, ['placeholder' => __('messages.student.select_campus'),'class' => 'form-select io-select2', 'aria-label'=>"Select a Campus",'data-control'=>'select2', 'id' => 'campusSelect']) }}
        </div>

        <div class="col-md-6 mb-7" id="collegeFieldContainer">
            {{ Form::label('college_id',__('messages.student.college').':',['class'=>'form-label']) }}
            {{ Form::select('college_id', $data['colleges'] ,!empty($patient->user) ? $patient->user->college_id : null, ['placeholder' => __('messages.student.select_college'),'class' => 'form-select io-select2', 'aria-label'=>"Select a College",'data-control'=>'select2', 'id' => 'collegeSelect']) }}
        </div>

        <div class="col-md-6 mb-7" id="courseFieldContainer">
            {{ Form::label('course_id',__('messages.student.course').':',['class'=>'form-label']) }}
            {{ Form::select('course_id', $data['courses'] ,!empty($patient->user) ? $patient->user->course_id : null, ['placeholder' => __('messages.student.select_course'),'class' => 'form-select io-select2', 'aria-label'=>"Select a Course",'data-control'=>'select2', 'id' => 'courseSelect']) }}
        </div>

        <div class="col-md-6 mb-7" id="departmentFieldContainer" style="display: none;">
            {{ Form::label('department_id',__('Department').':',['class'=>'form-label']) }}
            {{ Form::select('department_id', $data['departments'] ?? [], !empty($patient->user) ? $patient->user->department_id : null, ['placeholder' => 'Select Department','class' => 'form-select io-select2', 'aria-label'=>"Select Department",'data-control'=>'select2', 'id' => 'departmentSelect']) }}
        </div>

        <div class="col-md-6 mb-7" id="officeFieldContainer" style="display: none;">
            {{ Form::label('office_id',__('Office').':',['class'=>'form-label']) }}
            {{ Form::select('office_id', $data['offices'] ?? [], !empty($patient->user) ? $patient->user->office_id : null, ['placeholder' => 'Select Office','class' => 'form-select io-select2', 'aria-label'=>"Select Office",'data-control'=>'select2', 'id' => 'officeSelect']) }}
        </div>

        <div class="col-md-6 mb-7" id="yearLevelFieldContainer">
            {{ Form::label('year_level_id', __('messages.student.year_level').':',['class'=>'form-label']) }}
            {{ Form::select('year_level_id', $data['year_levels'], !empty($patient->user) ? $patient->user->year_level_id : null, ['placeholder' => __('messages.student.select_year_level'),'class' => 'form-select io-select2', 'aria-label'=>"Select a Year Level",'data-control'=>'select2', 'id' => 'yearLevelSelect']) }}
        </div>
    </div>

    <div class="fw-bolder fs-3 mb-7 mt-5">{{ __('Patient Record Details') }}</div>
    <div class="row">
        <div class="col-md-6 mb-5">
            {{ Form::label('campus_address', __('Campus Address').':', ['class' => 'form-label required']) }}
            {{ Form::textarea('campus_address', !empty($patient) ? $patient->campus_address : old('campus_address'), ['class' => 'form-control', 'rows' => 3, 'placeholder' => __('Dormitory/Building and Room Number'), 'required']) }}
        </div>
        <div class="col-md-6 mb-5">
            {{ Form::label('permanent_address', __('Permanent Address').':', ['class' => 'form-label required']) }}
            {{ Form::textarea('permanent_address', !empty($patient) ? $patient->permanent_address : old('permanent_address'), ['class' => 'form-control', 'rows' => 3, 'placeholder' => __('Home address for long-term records'), 'required']) }}
        </div>
        <div class="col-md-12 mb-5">
            {{ Form::label('immunization_record', __('Immunization Record').':', ['class' => 'form-label required']) }}
            {{ Form::textarea('immunization_record', !empty($patient) ? $patient->immunization_record : old('immunization_record'), ['class' => 'form-control', 'rows' => 3, 'placeholder' => __('Include required vaccines (Hepatitis B, MMR, COVID-19, etc.)'), 'required']) }}
        </div>
        <div class="col-md-6 mb-5">
            {{ Form::label('insurance_provider_id', __('Health Insurance Provider').':', ['class' => 'form-label']) }}
            {{ Form::select('insurance_provider_id', $data['insurance_providers'] ?? [], !empty($patient) ? $patient->insurance_provider_id : old('insurance_provider_id'), ['placeholder' => __('Select Insurance Provider'),'class' => 'form-select io-select2', 'aria-label' => 'Select Insurance Provider', 'data-control' => 'select2']) }}
        </div>
        <div class="col-md-6 mb-5">
            {{ Form::label('insurance_policy_number', __('Insurance Policy Number').':', ['class' => 'form-label']) }}
            {{ Form::text('insurance_policy_number', !empty($patient) ? $patient->insurance_policy_number : old('insurance_policy_number'), ['class' => 'form-control', 'placeholder' => __('Policy Number')]) }}
        </div>
        <div class="col-md-6 mb-5">
            {{ Form::label('primary_care_physician_name', __('Primary Care Physician (PCP)').':', ['class' => 'form-label']) }}
            {{ Form::text('primary_care_physician_name', !empty($patient) ? $patient->primary_care_physician_name : old('primary_care_physician_name'), ['class' => 'form-control', 'placeholder' => __('Primary Care Physician Name')]) }}
        </div>
        <div class="col-md-6 mb-5">
            {{ Form::label('primary_care_physician_contact', __('PCP Contact Number').':', ['class' => 'form-label']) }}
            {{ Form::text('primary_care_physician_contact', !empty($patient) ? $patient->primary_care_physician_contact : old('primary_care_physician_contact'), ['class' => 'form-control', 'placeholder' => __('PCP Contact Number')]) }}
        </div>
        <div class="col-md-6 mb-5">
            {{ Form::label('primary_care_physician_email', __('PCP Email').':', ['class' => 'form-label']) }}
            {{ Form::email('primary_care_physician_email', !empty($patient) ? $patient->primary_care_physician_email : old('primary_care_physician_email'), ['class' => 'form-control', 'placeholder' => __('PCP Email Address')]) }}
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

    {{ Form::hidden('all_year_levels', json_encode($data['year_levels']), ['id' => 'allYearLevels']) }}
    {{ Form::hidden('patient_type_lookup', json_encode($patientTypeLookup), ['id' => 'patientTypeLookup']) }}

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const patientTypeSelect = document.getElementById('patientTypeSelect');
            const campusFieldContainer = document.getElementById('campusFieldContainer');
            const collegeFieldContainer = document.getElementById('collegeFieldContainer');
            const courseFieldContainer = document.getElementById('courseFieldContainer');
            const departmentFieldContainer = document.getElementById('departmentFieldContainer');
            const officeFieldContainer = document.getElementById('officeFieldContainer');
            const yearLevelFieldContainer = document.getElementById('yearLevelFieldContainer');

            const campusSelect = document.getElementById('campusSelect');
            const collegeSelect = document.getElementById('collegeSelect');
            const courseSelect = document.getElementById('courseSelect');
            const departmentSelect = document.getElementById('departmentSelect');
            const officeSelect = document.getElementById('officeSelect');
            const yearLevelSelect = document.getElementById('yearLevelSelect');

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
            {{ Form::label('allergies','Allergies:' ,['class' => 'form-label']) }}
            {{ Form::textarea('allergies',!empty($patient) ? $patient->allergies : null,['class' => 'form-control', 'rows' => 3, 'placeholder' => 'List any allergies (Drug, Food, etc.)']) }}
        </div>
        <div class="col-md-12 mb-5">
            {{ Form::label('comorbidities','Comorbidities/Underlying Conditions:' ,['class' => 'form-label']) }}
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
            {{ Form::select('comorbidities[]', $comorbidityOptions, $selectedComorbidities, ['class' => 'form-select io-select2', 'data-control' => 'select2', 'data-placeholder' => 'Select comorbidities', 'multiple' => 'multiple', 'id' => 'patientComorbidities']) }}
        </div>
        <div class="col-md-12 mb-5">
            {{ Form::label('admissions_surgeries','Hospital Admissions / Surgeries:' ,['class' => 'form-label']) }}
            {{ Form::textarea('admissions_surgeries',!empty($patient) ? $patient->admissions_surgeries : null,['class' => 'form-control', 'rows' => 3, 'placeholder' => 'List previous admissions or surgeries if any']) }}
        </div>
        <div class="col-md-12 mb-5">
            {{ Form::label('maintenance','Maintenance/Current Medications:' ,['class' => 'form-label']) }}
            {{ Form::textarea('maintenance',!empty($patient) ? $patient->maintenance : null,['class' => 'form-control', 'rows' => 3, 'placeholder' => 'List current maintenance medications']) }}
        </div>
    </div>

    <div>
        {{ Form::submit(__('messages.common.save'),['class' => 'btn btn-primary me-2']) }}
        <a href="{{ 
            isRole('clinic_admin') ? route('patients.index') : 
            (isRole('staff') ? route('staff.patients.index') : 
            (isRole('doctor') ? route('doctors.patients.index') : route('patients.index')))
        }}" type="reset"
            class="btn btn-secondary">{{__('messages.common.discard')}}</a>
    </div>