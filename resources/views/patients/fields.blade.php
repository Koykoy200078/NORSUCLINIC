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
        <div class="col-md-6 mb-5">
            <div class="form-check">
                {{ Form::checkbox('is_employee', 1, !empty($patient->user) && $patient->user->year_level_id == 1, ['class' => 'form-check-input', 'id' => 'isEmployeeCheckbox']) }}
                {{ Form::label('is_employee', __('messages.student.is_employee'), ['class' => 'form-check-label']) }}
            </div>
        </div>
        <div class="col-md-6 mb-5">
            <div class="form-check">
                {{ Form::checkbox('is_guest', 1, !empty($patient->user) && $patient->user->year_level_id == 8, ['class' => 'form-check-input', 'id' => 'isGuestCheckbox']) }}
                {{ Form::label('is_guest', __('Is Guest'), ['class' => 'form-check-label']) }}
            </div>
        </div>

        <!-- Employee Position Field (Faculty/Staff) -->
        <div class="col-md-6 mb-7" id="positionFieldContainer" style="display: none;">
            {{ Form::label('position_type', __('Position').':',['class'=>'form-label']) }}
            {{ Form::select('position_type', ['faculty' => 'Faculty', 'staff' => 'Staff'], null, ['placeholder' => 'Select Position','class' => 'form-select io-select2', 'aria-label'=>"Select Position",'data-control'=>'select2', 'id' => 'positionTypeSelect']) }}
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
            {{ Form::select('department_id', $data['departments'] ?? [], !empty($patient->user) ? $patient->user->department_id : null, ['placeholder' => 'Select Department','class' => 'form-select io-select2', 'aria-label'=>"Select Department",'data-control'=>'select2', 'id' => 'departmentSelect']) }}
        </div>

        <!-- Office field (for Staff only) -->
        <div class="col-md-6 mb-7" id="officeFieldContainer" style="display: none;">
            {{ Form::label('office_id',__('Office').':',['class'=>'form-label']) }}
            {{ Form::select('office_id', $data['offices'] ?? [], !empty($patient->user) ? $patient->user->office_id : null, ['placeholder' => 'Select Office','class' => 'form-select io-select2', 'aria-label'=>"Select Office",'data-control'=>'select2', 'id' => 'officeSelect']) }}
        </div>

        <div class="col-md-6 mb-7" id="yearLevelFieldContainer">
            {{ Form::label('year_level_id', __('messages.student.year_level').':',['class'=>'form-label', 'id' => 'yearLevelLabel']) }}
            {{ Form::select('year_level_id', $data['year_levels'], !empty($patient->user) ? $patient->user->year_level_id : null, ['placeholder' => __('messages.student.select_year_level'),'class' => 'form-select io-select2', 'aria-label'=>"Select a Year Level",'data-control'=>'select2', 'id' => 'yearLevelSelect']) }}
        </div>
    </div>

    {{-- Hidden inputs to store original year level data --}}
    {{ Form::hidden('all_year_levels', json_encode($data['year_levels']), ['id' => 'allYearLevels']) }}

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
            // Employee (position 1), Student year levels (2-7), Guest (position 8)
            const employeeYearLevels = {};
            const studentYearLevels = {};
            const guestYearLevels = {};

            let count = 0;
            Object.entries(allYearLevels).forEach(([id, name]) => {
                count++;
                if (count === 1) {
                    // Employee
                    employeeYearLevels[id] = name;
                } else if (count === 8) {
                    // Guest
                    guestYearLevels[id] = name;
                } else {
                    // Student (2-7)
                    studentYearLevels[id] = name;
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
                    optionsToShow = employeeYearLevels;
                    yearLevelFieldContainer.style.display = 'none';
                    positionFieldContainer.style.display = 'block';
                    campusFieldContainer.style.display = 'none';

                    // Show/hide fields based on position type
                    if (positionType === 'faculty') {
                        // Faculty: Show College and Department, hide Course and Office
                        collegeFieldContainer.style.display = 'block';
                        departmentFieldContainer.style.display = 'block';
                        courseFieldContainer.style.display = 'none';
                        officeFieldContainer.style.display = 'none';
                        $(courseSelect).val(null);
                        $(officeSelect).val(null);
                        $(campusSelect).val(null);
                    } else if (positionType === 'staff') {
                        // Staff: Show Office only, hide College, Course and Department
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
                    }
                } else if (isGuest) {
                    optionsToShow = guestYearLevels;
                    yearLevelFieldContainer.style.display = 'none';
                    positionFieldContainer.style.display = 'none';
                    campusFieldContainer.style.display = 'none';
                    collegeFieldContainer.style.display = 'none';
                    courseFieldContainer.style.display = 'none';
                    departmentFieldContainer.style.display = 'none';
                    officeFieldContainer.style.display = 'none';

                    // Clear all fields - no dropdowns needed for guests
                    $(courseSelect).val(null);
                    $(officeSelect).val(null);
                    $(positionTypeSelect).val(null);
                    $(departmentSelect).val(null);

                    // Automatically select guest year level without showing dropdown
                    $(yearLevelSelect).empty();
                    Object.entries(guestYearLevels).forEach(([value, text]) => {
                        const option = new Option(text, value, true, true);
                        $(yearLevelSelect).append(option);
                    });

                    return; // Exit early - no need to populate dropdowns
                } else {
                    // Student
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
                } else {
                    // Automatically select the appropriate year level
                    if (isEmployee) {
                        Object.entries(employeeYearLevels).forEach(([value, text]) => {
                            const option = new Option(text, value, true, true);
                            $(yearLevelSelect).append(option);
                        });
                    } else if (isGuest) {
                        Object.entries(guestYearLevels).forEach(([value, text]) => {
                            const option = new Option(text, value, true, true);
                            $(yearLevelSelect).append(option);
                        });
                    }
                }

                // Year level select is already initialized with Select2, no need to refresh
            }

            // Initialize on page load
            updateFieldsDisplay();

            // Update when checkboxes change
            isEmployeeCheckbox.addEventListener('change', updateFieldsDisplay);
            isGuestCheckbox.addEventListener('change', updateFieldsDisplay);

            // Update when position type changes (using off/on to prevent multiple bindings)
            $(positionTypeSelect).off('change').on('change', updateFieldsDisplay);
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
