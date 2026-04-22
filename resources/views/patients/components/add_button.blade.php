@if(request()->query('module') !== 'prescription')
<a type="button" class="btn btn-primary ms-3" href="{{ 
    isRole('clinic_admin') ? route('patients.create') : 
    (isRole('staff') ? route('staff.patients.create') : 
    (isRole('doctor') ? route('doctors.patients.create') : route('patients.create')))
}}">
    {{ __('messages.patient.add') }}
</a>
@endif