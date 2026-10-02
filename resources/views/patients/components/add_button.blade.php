{{-- Registering a patient is a staff/nurse (and clinic admin) task; doctors view and edit existing patients. --}}
@if(request()->query('module') !== 'prescription' && ! isRole('doctor'))
<a type="button" class="btn btn-primary ms-3" href="{{ isRole('staff') ? route('staff.patients.create') : route('patients.create') }}">
    {{ __('messages.patient.add') }}
</a>
@endif
