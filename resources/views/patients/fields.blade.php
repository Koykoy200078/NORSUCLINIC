    <!-- Account Information -->
    <div class="fw-bolder fs-3 mb-7 mt-5">{{ __('messages.patient.account_information') }}</div>
    <div class="col-lg-6 mt-5">
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
        <div class="col-md-6 mb-5 d-none">
            {{ Form::label('patientUniqueId',__('messages.patient.patient_unique_id').':' ,['class' => 'form-label required']) }}
            {{ Form::text('patient_unique_id',isset($data['patientUniqueId']) ? $data['patientUniqueId'] : null,['class' => 'form-control','required','maxLength' => '8','readonly']) }}
        </div>
        <div class="col-md-6 mb-5">
            {{ Form::label('email',__('messages.patient.email').'(Optional):' ,['class' => 'form-label']) }}
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
            {{ Form::tel('contact', !empty($patient->user) ? '+'.$patient->user->country_code.$patient->user->contact : null, ['class' => 'form-control',
            'placeholder' => __('messages.patient.contact_no'),'onkeyup' => 'if (/\D/g.test(this.value)) this.value = this.value.replace(/\D/g,"")','id'=>'phoneNumber']) }}
            {{ Form::hidden('country_code',!empty($patient->user) ? $patient->user->country_code : null,['id'=>'prefix_code']) }}
            <span id="valid-msg" class="text-success d-none fw-400 fs-small mt-2">{{ __('messages.valid_number') }}</span>
            <span id="error-msg" class="text-danger d-none fw-400 fs-small mt-2">{{ __('messages.invalid_number') }}</span>
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
            {{ Form::select('state_id', $data['provinces'] ,!empty($patient->address) ? $patient->address->state_id : null, ['placeholder' => __('messages.province.select_province'),'class' => 'form-select io-select2', 'aria-label'=>"Select a Province",'data-control'=>'select2']) }}
        </div>
        <div class="col-md-6 mb-7">
            {{ Form::label('city_id',__('messages.city.city').':',['class'=>'form-label']) }}
            {{ Form::select('city_id', $data['cities'] ,!empty($patient->address) ? $patient->address->city_id : null, ['placeholder' => __('messages.city.select_city'),'class' => 'form-select io-select2', 'aria-label'=>"Select a City",'data-control'=>'select2']) }}
        </div>
        <div class="col-md-6 mb-7">
            {{ Form::label('postalCode',__('messages.patient.postal_code').':' ,['class' => 'form-label']) }}
            {{ Form::text('postal_code',!empty($patient->address) ? $patient->address->postal_code : null,['class' => 'form-control','placeholder' => __('messages.patient.postal_code')]) }}
        </div>
    </div>

    <!-- Patient Information -->
    <div class="fw-bolder fs-3 mb-7 mt-5">{{ __('messages.student.student_information') }}</div>
    <div class="row">
        <div class="col-md-12 mb-5">
            <div class="form-check">
                {{ Form::checkbox('is_employee', 1, !empty($patient->user) && in_array($patient->user->year_level_id, [1, 2, 3]), ['class' => 'form-check-input', 'id' => 'isEmployeeCheckbox']) }}
                {{ Form::label('is_employee', __('messages.student.is_employee'), ['class' => 'form-check-label']) }}
            </div>
        </div>
        <div class="col-md-6 mb-7">
            {{ Form::label('campus_id',__('messages.student.campus').':',['class'=>'form-label']) }}
            {{ Form::select('campus_id', $data['campuses'] ,!empty($patient->user) ? $patient->user->campus_id : null, ['placeholder' => __('messages.student.select_campus'),'class' => 'form-select io-select2', 'aria-label'=>"Select a Campus",'data-control'=>'select2']) }}
        </div>

        <div class="col-md-6 mb-7">
            {{ Form::label('college_id',__('messages.student.college').':',['class'=>'form-label']) }}
            {{ Form::select('college_id', $data['colleges'] ,!empty($patient->user) ? $patient->user->college_id : null, ['placeholder' => __('messages.student.select_college'),'class' => 'form-select io-select2', 'aria-label'=>"Select a College",'data-control'=>'select2']) }}
        </div>

        <div class="col-md-6 mb-7" id="courseFieldContainer">
            {{ Form::label('course_id',__('messages.student.course').':',['class'=>'form-label']) }}
            {{ Form::select('course_id', $data['courses'] ,!empty($patient->user) ? $patient->user->course_id : null, ['placeholder' => __('messages.student.select_course'),'class' => 'form-select io-select2', 'aria-label'=>"Select a Course",'data-control'=>'select2', 'id' => 'courseSelect']) }}
        </div>
        <div class="col-md-6 mb-7">
            {{ Form::label('year_level_id', __('messages.student.year_level').':',['class'=>'form-label', 'id' => 'yearLevelLabel']) }}
            {{ Form::select('year_level_id', $data['year_levels'], !empty($patient->user) ? $patient->user->year_level_id : null, ['placeholder' => __('messages.student.select_year_level'),'class' => 'form-select io-select2', 'aria-label'=>"Select a Year Level",'data-control'=>'select2', 'id' => 'yearLevelSelect']) }}
        </div>
    </div>

    {{-- Hidden inputs to store original year level data --}}
    {{ Form::hidden('all_year_levels', json_encode($data['year_levels']), ['id' => 'allYearLevels']) }}

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const isEmployeeCheckbox = document.getElementById('isEmployeeCheckbox');
            const yearLevelSelect = document.getElementById('yearLevelSelect');
            const yearLevelLabel = document.getElementById('yearLevelLabel');
            const courseFieldContainer = document.getElementById('courseFieldContainer');
            const courseSelect = document.getElementById('courseSelect');
            const allYearLevels = JSON.parse(document.getElementById('allYearLevels').value);

            // Split year levels based on the seeder data
            // Employee levels: Staff, Faculty, Guest (first 3 items)
            // Student levels: 1st Year to 6th Year (remaining items)
            const employeeYearLevels = {};
            const studentYearLevels = {};

            let count = 0;
            Object.entries(allYearLevels).forEach(([id, name]) => {
                if (count < 3) {
                    employeeYearLevels[id] = name;
                } else {
                    studentYearLevels[id] = name;
                }
                count++;
            });

            function updateYearLevelOptions() {
                const isEmployee = isEmployeeCheckbox.checked;
                const currentValue = $(yearLevelSelect).val();

                // Show/hide course field based on checkbox
                if (isEmployee) {
                    // Hide course field and clear its value
                    courseFieldContainer.style.display = 'none';
                    $(courseSelect).val(null).trigger('change');

                    // Update year level label and placeholder
                    yearLevelLabel.textContent = '{{ __("messages.student.position") }}:';
                    $(yearLevelSelect).attr('aria-label', 'Select a Position');
                } else {
                    // Show course field
                    courseFieldContainer.style.display = 'block';

                    // Update year level label and placeholder
                    yearLevelLabel.textContent = '{{ __("messages.student.year_level") }}:';
                    $(yearLevelSelect).attr('aria-label', 'Select a Year Level');
                }

                // Clear current options
                $(yearLevelSelect).empty();

                // Add appropriate placeholder
                const placeholderText = isEmployee ? '{{ __("messages.student.select_position") }}' : '{{ __("messages.student.select_year_level") }}';
                $(yearLevelSelect).append('<option value="">' + placeholderText + '</option>');

                // Add appropriate options based on checkbox state
                const optionsToShow = isEmployee ? employeeYearLevels : studentYearLevels;

                Object.entries(optionsToShow).forEach(([value, text]) => {
                    const option = new Option(text, value, false, value == currentValue);
                    $(yearLevelSelect).append(option);
                });

                // Refresh Select2
                $(yearLevelSelect).trigger('change');
            }

            // Initialize on page load
            updateYearLevelOptions();

            // Update when checkbox changes
            isEmployeeCheckbox.addEventListener('change', updateYearLevelOptions);
        });
    </script>

    <div>
        {{ Form::submit(__('messages.common.save'),['class' => 'btn btn-primary me-2']) }}
        <a href="{{ 
            isRole('clinic_admin') ? route('patients.index') : 
            (isRole('staff') ? route('staff.patients.index') : 
            (isRole('doctor') ? route('doctors.patients.index') : route('patients.index')))
        }}" type="reset"
            class="btn btn-secondary">{{__('messages.common.discard')}}</a>
    </div>