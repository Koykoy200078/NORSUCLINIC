<a href="{{ 
    isRole('clinic_admin') ? route('doctors.create') : 
    (isRole('staff') ? route('staff.doctors.create') : route('doctors.create'))
}}" class="btn btn-primary">{{ __('messages.common.add') }} {{ __('messages.doctor_session.doctor') }}</a>
