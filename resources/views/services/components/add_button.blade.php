<a type="button" class="btn btn-primary ms-auto" href="{{ 
    isRole('clinic_admin') ? route('services.create') : 
    (isRole('staff') ? route('staff.services.create') : 
    (isRole('doctor') ? route('doctors.services.create') : route('services.create')))
}}">
    {{__('messages.service.add_service')}}
</a>
