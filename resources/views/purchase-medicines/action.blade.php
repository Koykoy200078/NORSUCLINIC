<div class="card-toolbar ms-auto">
    <div class="dropdown">
        <a href="#" class="btn btn-primary dropdown-toggle" id="dropdownMenuButton"
            data-bs-toggle="dropdown"
            aria-haspopup="true" aria-expanded="false">{{ __('messages.purchase_medicine.actions') }}
        </a>
        <ul class="dropdown-menu action-dropdown" aria-labelledby="dropdownMenuButton">
            <li>
                <a href="{{ 
                    isRole('clinic_admin') ? route('medicine-purchase.create') : 
                    (isRole('staff') ? route('staff.medicine-purchase.create') : 
                    (isRole('doctor') ? route('doctors.medicine-purchase.create') : route('medicine-purchase.create'))) 
                }}"
                    class="dropdown-item  px-5">{{ __('messages.purchase_medicine.purchase_medicine') }}</a>
            </li>
            <li>
                <a href="{{ 
                    isRole('clinic_admin') ? route('purchase-medicine.excel') : 
                    (isRole('staff') ? route('staff.purchase-medicine.excel') : 
                    (isRole('doctor') ? route('doctors.purchase-medicine.excel') : route('purchase-medicine.excel'))) 
                }}"
                    class="dropdown-item  px-5">{{ __('messages.purchase_medicine.export_to_excel') }}</a>
            </li>
        </ul>
    </div>
</div>
