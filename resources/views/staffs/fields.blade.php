<div class="row">
    @php
    $selectedRoleDesignationId = old('role_designation_id', isset($staff) ? optional($staff->staffProfile)->role_designation_id : null);
    $selectedRoleDesignationCode = $staffDesignationCodes[$selectedRoleDesignationId] ?? null;
    $isClinicHeadSelected = $selectedRoleDesignationCode === 'clinic_head';

    $assignedStationAttributes = [
    'class' => 'form-select io-select2',
    'id' => 'assigned_station_id',
    'data-control' => 'select2',
    'placeholder' => __('Select Assigned Station'),
    ];

    if (! $isClinicHeadSelected) {
    $assignedStationAttributes['required'] = 'required';
    }
    @endphp

    <div class="col-lg-6">
        <div class="mb-5">
            {{ Form::label('first_name', __('messages.staff.first_name').':', ['class' => 'form-label required']) }}
            {{ Form::text('first_name', old('first_name', isset($staff) ? $staff->first_name : null), ['class' => 'form-control', 'placeholder' => __('messages.patient.first_name'), 'required']) }}
        </div>
    </div>

    <div class="col-lg-6">
        <div class="mb-5">
            {{ Form::label('last_name', __('messages.staff.last_name').':', ['class' => 'form-label required']) }}
            {{ Form::text('last_name', old('last_name', isset($staff) ? $staff->last_name : null), ['class' => 'form-control', 'placeholder' => __('messages.patient.last_name'), 'required']) }}
        </div>
    </div>

    <div class="col-lg-6">
        <div class="mb-5">
            {{ Form::label('email', __('messages.staff.email').':', ['class' => 'form-label required']) }}
            {{ Form::email('email', old('email', isset($staff) ? $staff->email : null), ['class' => 'form-control', 'placeholder' => __('messages.patient.email'), 'required']) }}
        </div>
    </div>

    <div class="col-lg-6">
        <div class="mb-5">
            {{ Form::label('employee_id', __('Employee ID').':', ['class' => 'form-label required']) }}
            {{ Form::text('employee_id', old('employee_id', isset($staff) ? $staff->employee_id : null), ['class' => 'form-control', 'placeholder' => __('Employee ID'), 'required']) }}
        </div>
    </div>

    <div class="col-lg-6">
        <div class="mb-5">
            {{ Form::label('contact', __('messages.staff.contact_no').':', ['class' => 'form-label']) }}
            <br>
            {{ Form::text('contact', old('contact', isset($staff) ? $staff->contact : null), ['class' => 'form-control', 'placeholder' => __('messages.patient.contact_no')]) }}
        </div>
    </div>

    <div class="col-md-6 mb-5">
        <div class="mb-1">
            {{ Form::label('password',__('messages.staff.password').':' ,['class' => 'form-label']) }}
            <span class="text-danger">{{isset($staff) ? null : '*' }}</span>
            <span data-bs-toggle="tooltip" title="{{ __('messages.flash.user_8_or') }}">
                <i class="fa fa-question-circle"></i></span>
            <div class="mb-3 position-relative">
                {{Form::password('password',['class' => 'form-control','placeholder' => __('messages.patient.password'),'autocomplete' => 'off','aria-label'=>"Password",'data-toggle'=>"password"])}}
                <span class="position-absolute d-flex align-items-center top-0 bottom-0 end-0 me-4 input-icon input-password-hide cursor-pointer text-gray-600"> <i class="bi bi-eye-slash-fill"></i> </span>
            </div>
        </div>
    </div>

    <div class="col-md-6 mb-5">
        <div class="fv-row">
            <div class="mb-1">
                {{ Form::label('confirmPassword',__('messages.staff.confirm_password').':' ,['class' => 'form-label']) }}
                <span class="text-danger">{{isset($staff) ? null : '*' }}</span>
                <span data-bs-toggle="tooltip"
                    title="{{ __('messages.flash.user_8_or') }}">
                    <i class="fa fa-question-circle"></i></span>
                <div class="mb-3 position-relative">
                    {{Form::password('password_confirmation',['class' => 'form-control','placeholder' => __('messages.user.confirm_password'),'autocomplete' => 'off','aria-label'=>"Password",'data-toggle'=>"password"])}}
                    <span class="position-absolute d-flex align-items-center top-0 bottom-0 end-0 me-4 input-icon input-password-hide cursor-pointer text-gray-600"> <i class="bi bi-eye-slash-fill"></i> </span>
                </div>
            </div>
        </div>
    </div>

    {{ Form::hidden('role', isset($staff) ? $staff->roles->first()->id : $defaultRoleId) }}


    <div class="col-lg-6">
        <div class="mb-5">
            {{ Form::label('gender', __('messages.staff.gender').':', ['class' => 'form-label required']) }}
            <span class="is-valid">
                <div class="mt-2">
                    <input class="form-check-input" type="radio" name="gender" value="1"
                        {{ old('gender', isset($staff) ? $staff->gender : 1) == 1 ? 'checked' : '' }} required>
                    <label class="form-label mr-3">{{ __('messages.staff.male') }}</label>

                    <input class="form-check-input ms-2" type="radio" name="gender" value="2"
                        {{ old('gender', isset($staff) ? $staff->gender : 1) == 2 ? 'checked' : '' }} required>
                    <label class="form-label mr-3">{{ __('messages.staff.female') }}</label>
                </div>
            </span>
        </div>
    </div>

    <div class="col-lg-6">
        <div class="mb-5">
            {{ Form::label('role_designation_id', __('Role Designation').':', ['class' => 'form-label required']) }}
            {{ Form::select('role_designation_id', $staffDesignations, old('role_designation_id', isset($staff) ? optional($staff->staffProfile)->role_designation_id : null), ['class' => 'form-select io-select2', 'id' => 'role_designation_id', 'data-control' => 'select2', 'placeholder' => __('Select Role Designation'), 'required']) }}
        </div>
    </div>

    <div class="col-lg-6 {{ $isClinicHeadSelected ? 'd-none' : '' }}" id="assigned_station_group">
        <div class="mb-5">
            {{ Form::label('assigned_station_id', __('Assigned Station').':', ['class' => 'form-label'.($isClinicHeadSelected ? '' : ' required'), 'id' => 'assigned_station_label']) }}
            {{ Form::select('assigned_station_id', $clinicStations, old('assigned_station_id', isset($staff) ? optional($staff->staffProfile)->assigned_station_id : null), $assignedStationAttributes) }}
        </div>
    </div>

    <div class="col-lg-6 {{ $isClinicHeadSelected ? '' : 'd-none' }}" id="assigned_station_skip_note">
        <div class="mb-5 mt-4">
            <small class="text-muted">
                <i class="fas fa-info-circle me-1"></i>
                Assigned Station is optional for Clinic Head.
            </small>
        </div>
    </div>

    <div class="col-lg-12">
        <div class="mb-5">
            {{ Form::label('shift_schedule', __('Shift Schedule').':', ['class' => 'form-label required']) }}
            {{ Form::textarea('shift_schedule', old('shift_schedule', isset($staff) ? optional($staff->staffProfile)->shift_schedule : null), ['class' => 'form-control', 'rows' => 3, 'placeholder' => __('e.g. Mon-Fri 8:00 AM - 5:00 PM'), 'required']) }}
        </div>
    </div>

    <div class="col-lg-6 mb-7 d-none">
        <div class="mb-3" io-image-input="true">
            <label for="exampleInputImage" class="form-label">{{__('messages.patient.profile')}}:</label>
            <div class="d-block">
                <div class="image-picker">
                    <div class="image previewImage" id="exampleInputImage" style="background-image: url({{ !empty($staff->profile_image) ? $staff->profile_image : asset('web/media/avatars/male.png') }})">
                    </div>
                    <span class="picker-edit rounded-circle text-gray-500 fs-small" data-bs-toggle="tooltip"
                        data-placement="top" data-bs-original-title="{{ __('messages.user.edit_profile') }}">
                        <label>
                            <i class="fa-solid fa-pen" id="profileImageIcon"></i>
                            <input type="file" name="profile" class="image-upload d-none profile-validation" accept="image/*" />
                        </label>
                    </span>
                </div>
            </div>
        </div>
    </div>
    <div class="d-flex">
        {{ Form::submit(__('messages.common.save'),['class' => 'btn btn-primary me-2']) }}
        <a href="{{ route('staffs.index') }}" type="reset"
            class="btn btn-secondary">{{__('messages.common.discard')}}</a>
    </div>
</div>

@push('scripts')
<script>
    (function() {
        function initAssignedStationToggle() {
            const roleDesignationSelect = document.getElementById('role_designation_id');
            const assignedStationGroup = document.getElementById('assigned_station_group');
            const assignedStationSelect = document.getElementById('assigned_station_id');
            const assignedStationLabel = document.getElementById('assigned_station_label');
            const assignedStationSkipNote = document.getElementById('assigned_station_skip_note');
            const designationCodeMap = @json($staffDesignationCodes ?? []);

            if (!roleDesignationSelect || !assignedStationGroup || !assignedStationSelect) {
                return;
            }

            function getSelectedDesignationText() {
                const selectedOption = roleDesignationSelect.options[roleDesignationSelect.selectedIndex];

                if (!selectedOption) {
                    return '';
                }

                return String(selectedOption.text || '').toLowerCase().trim();
            }

            function getSelectedDesignationCode() {
                const selectedId = String(roleDesignationSelect.value || '').trim();

                if (selectedId === '' || !Object.prototype.hasOwnProperty.call(designationCodeMap, selectedId)) {
                    return null;
                }

                return designationCodeMap[selectedId];
            }

            function isClinicHeadSelected() {
                const selectedCode = getSelectedDesignationCode();

                if (selectedCode === 'clinic_head') {
                    return true;
                }

                // Fallback to designation label matching if code map is unavailable/stale.
                return getSelectedDesignationText().includes('clinic head');
            }

            function applyAssignedStationRequirement(shouldClearAssignedStation) {
                const isClinicHead = isClinicHeadSelected();

                if (isClinicHead) {
                    assignedStationGroup.classList.add('d-none');
                    if (assignedStationSkipNote) {
                        assignedStationSkipNote.classList.remove('d-none');
                    }

                    assignedStationSelect.setAttribute('disabled', 'disabled');
                    assignedStationSelect.removeAttribute('required');
                    if (shouldClearAssignedStation) {
                        assignedStationSelect.value = '';
                    }

                    if (assignedStationLabel) {
                        assignedStationLabel.classList.remove('required');
                    }

                    if (shouldClearAssignedStation && window.jQuery && window.jQuery.fn && window.jQuery.fn.select2) {
                        window.jQuery(assignedStationSelect).val('').trigger('change');
                    }

                    return;
                }

                assignedStationGroup.classList.remove('d-none');
                if (assignedStationSkipNote) {
                    assignedStationSkipNote.classList.add('d-none');
                }

                assignedStationSelect.removeAttribute('disabled');
                assignedStationSelect.setAttribute('required', 'required');
                if (assignedStationLabel) {
                    assignedStationLabel.classList.add('required');
                }
            }

            let lastClinicHeadState = isClinicHeadSelected();

            function onDesignationChanged() {
                const currentClinicHeadState = isClinicHeadSelected();
                const shouldClearAssignedStation = currentClinicHeadState && !lastClinicHeadState;

                applyAssignedStationRequirement(shouldClearAssignedStation);
                lastClinicHeadState = currentClinicHeadState;
            }

            roleDesignationSelect.addEventListener('change', onDesignationChanged);

            if (window.jQuery) {
                window.jQuery(roleDesignationSelect).on('select2:select select2:clear change', onDesignationChanged);
            }

            applyAssignedStationRequirement(false);
        }

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', initAssignedStationToggle);
            return;
        }

        initAssignedStationToggle();
    })();
</script>
@endpush