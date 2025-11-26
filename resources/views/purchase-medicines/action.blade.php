<div class="card-toolbar ms-auto">
    <a href="{{ 
        isRole('clinic_admin') ? route('medicine-purchase.create') : 
        (isRole('staff') ? route('staff.medicine-purchase.create') : 
        (isRole('doctor') ? route('doctors.medicine-purchase.create') : route('medicine-purchase.create'))) 
    }}"
        class="btn btn-primary">{{ __('messages.purchase_medicine.purchase_medicine') }}</a>
</div>