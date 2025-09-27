<a type="button" class="btn btn-primary ms-auto"
    href="{{ 
        isRole('clinic_admin') ? route('doctor-sessions.create') : 
        (isRole('staff') ? route('staff.doctor-sessions.create') : route('doctor-sessions.create'))
    }}">
    {{ __('messages.doctor_session.add') }}
</a>