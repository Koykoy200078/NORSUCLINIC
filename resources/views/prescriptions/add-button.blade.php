{{-- The button opens the patient list, so staff need the patients module too (a pharmacist has none: it answered 403). --}}
@if((isRole('doctor') || isRole('clinic_admin') || isRole('staff')) && auth()->user()->can('manage_patients') && canStaffAccessModule('patients'))
@php($patientListRoute = isRole('doctor') ? route('doctors.patients.index', ['module' => 'prescription']) : (isRole('staff') ? route('staff.patients.index', ['module' => 'prescription']) : route('patients.index', ['module' => 'prescription'])))
<a href="{{ $patientListRoute }}" class="btn btn-primary">
    {{ __('messages.prescription.new_prescription') }}
</a>
@endif