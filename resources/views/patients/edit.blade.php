@extends('layouts.app')
@section('title')
{{ __('messages.patient.edit') }}
@endsection
@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-end mb-5">
        <h1>@yield('title')</h1>
        <a class="btn btn-outline-primary float-end"
            href="{{ 
                   isRole('clinic_admin') ? route('patients.index') : 
                   (isRole('staff') ? route('staff.patients.index') : 
                   (isRole('doctor') ? route('doctors.patients.index') : route('patients.index')))
               }}">{{ __('messages.common.back') }}</a>
    </div>

    <div class="col-12">
        @include('layouts.errors')
    </div>
    <div class="card">
        <div class="card-body">
            {{ Form::model($patient, ['route' => [
                isRole('clinic_admin') ? 'patients.update' : 
                (isRole('staff') ? 'staff.patients.update' : 
                (isRole('doctor') ? 'doctors.patients.update' : 'patients.update')), 
                $patient->id], 'method' => 'patch', 'files' => 'true','id'=>'editPatientForm']) }}
            {{ Form::hidden('is_edit', true,['id' => 'staffIsEdit']) }}
            {{ Form::hidden('is_edit', true,['id' => 'patientIsEdit']) }}
            {{ Form::hidden('edit_patient_country_id', isset($patient->address->country_id) ? $patient->address->country_id:null,
                            ['id' => 'editPatientCountryId']) }}
            {{ Form::hidden('edit_patient_state_id', isset($patient->address->state_id) ? $patient->address->state_id:null,
                            ['id' => 'editPatientStateId']) }}
            {{ Form::hidden('edit_patient_city_id', isset($patient->address->city_id) ? $patient->address->city_id:null,
                            ['id' => 'editPatientCityId']) }}
            {{ Form::hidden('backgroundImg',asset('web/media/avatars/male.png'),['id' => 'patientBackgroundImg']) }}
            <input type="hidden" id="existingYearLevelId" value="{{ !empty($patient->user) ? $patient->user->year_level_id : '' }}">
            <input type="hidden" id="existingPositionType" value="{{ !empty($patient->user) ? $patient->user->position_type : '' }}">
            @include('patients.fields')
            {{ Form::close() }}
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Get existing year level and position type
        const existingYearLevelId = document.getElementById('existingYearLevelId')?.value;
        const existingPositionType = document.getElementById('existingPositionType')?.value;

        if (existingYearLevelId) {
            const yearLevelId = parseInt(existingYearLevelId);
            const isEmployeeCheckbox = document.getElementById('isEmployeeCheckbox');
            const isGuestCheckbox = document.getElementById('isGuestCheckbox');
            const positionTypeSelect = document.getElementById('positionTypeSelect');

            // Year level 7 = Faculty, 8 = Staff (Employee)
            if (yearLevelId === 7 || yearLevelId === 8) {
                if (isEmployeeCheckbox) {
                    isEmployeeCheckbox.checked = true;
                }

                // Set position type based on year level
                if (positionTypeSelect) {
                    if (yearLevelId === 7) {
                        $(positionTypeSelect).val('faculty').trigger('change');
                    } else if (yearLevelId === 8) {
                        $(positionTypeSelect).val('staff').trigger('change');
                    }
                }
            }
            // Year level 9 = Guest
            else if (yearLevelId === 9) {
                if (isGuestCheckbox) {
                    isGuestCheckbox.checked = true;
                }
            }

            // Trigger the updateFieldsDisplay function after a short delay
            // to ensure all Select2 elements are initialized
            setTimeout(function() {
                if (typeof updateFieldsDisplay === 'function') {
                    updateFieldsDisplay();
                }

                // If position type exists, set it again after fields are displayed
                if (existingPositionType && positionTypeSelect) {
                    $(positionTypeSelect).val(existingPositionType).trigger('change');
                }
            }, 300);
        }

        // Add event listeners to clear fields when Guest is selected
        const isGuestCheckboxEdit = document.getElementById('isGuestCheckbox');
        if (isGuestCheckboxEdit) {
            isGuestCheckboxEdit.addEventListener('change', function(e) {
                if (this.checked) {
                    // Uncheck employee checkbox first
                    const isEmployeeCheckbox = document.getElementById('isEmployeeCheckbox');
                    if (isEmployeeCheckbox) {
                        isEmployeeCheckbox.checked = false;
                    }

                    // Trigger the display update to handle everything
                    if (typeof updateFieldsDisplay === 'function') {
                        updateFieldsDisplay();
                    }
                }
            });
        }

        // Add event listener to clear Guest when Employee is selected
        const isEmployeeCheckboxEdit = document.getElementById('isEmployeeCheckbox');
        if (isEmployeeCheckboxEdit) {
            isEmployeeCheckboxEdit.addEventListener('change', function(e) {
                if (this.checked) {
                    const isGuestCheckbox = document.getElementById('isGuestCheckbox');
                    if (isGuestCheckbox) {
                        isGuestCheckbox.checked = false;
                    }

                    // Trigger the display update
                    if (typeof updateFieldsDisplay === 'function') {
                        updateFieldsDisplay();
                    }
                }
            });
        }
    });
</script>
@endsection