<div class="card-toolbar ms-auto">
    <a href="{{ 
        isRole('clinic_admin') ? route('medicine-availability.create') : 
        (isRole('staff') ? route('staff.medicine-availability.create') : 
        (isRole('doctor') ? route('doctors.medicine-availability.create') : route('medicine-availability.create'))) 
    }}"
        class="btn btn-primary">{{ __('messages.medicine_availability.medicine_availability') }}</a>
</div>