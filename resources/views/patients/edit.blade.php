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
                   getRouteByRole('patients.index')
               }}">{{ __('messages.common.back') }}</a>
    </div>

    <div class="col-12">
        @include('layouts.errors')
    </div>
    <div class="card">
        <div class="card-body">
            {{ html()->modelForm($patient, 'patch', route(getRouteNameByRole('patients.update'), [$patient->id]))->attribute('accept-charset', 'UTF-8')->acceptsFiles()->attributes(['id'=>'editPatientForm'])->open() }}
            {{ html()->hidden('is_edit', true)->attributes(['id' => 'staffIsEdit']) }}
            {{ html()->hidden('is_edit', true)->attributes(['id' => 'patientIsEdit']) }}
            {{ html()->hidden('edit_patient_country_id', isset($patient->address->country_id) ? $patient->address->country_id:null)->attributes(['id' => 'editPatientCountryId']) }}
            {{ html()->hidden('edit_patient_state_id', isset($patient->address->state_id) ? $patient->address->state_id:null)->attributes(['id' => 'editPatientStateId']) }}
            {{ html()->hidden('edit_patient_city_id', isset($patient->address->city_id) ? $patient->address->city_id:null)->attributes(['id' => 'editPatientCityId']) }}
            {{ html()->hidden('edit_patient_barangay_id', isset($patient->address->barangay_id) ? $patient->address->barangay_id:null)->attributes(['id' => 'editPatientBarangayId']) }}
            {{ html()->hidden('backgroundImg', asset('web/media/avatars/male.png'))->attributes(['id' => 'patientBackgroundImg']) }}
            <input type="hidden" id="existingYearLevelId" value="{{ !empty($patient->user) ? $patient->user->year_level_id : '' }}">
            <input type="hidden" id="existingPositionType" value="{{ !empty($patient->user) ? $patient->user->position_type : '' }}">
            @include('patients.fields')
            {{ html()->closeModelForm() }}
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