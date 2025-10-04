@if(Auth::user()->hasRole('Pharmacist'))
<div class="dropdown">
    <a href="#" class="btn btn-primary dropdown-toggle" id="dropdownMenuButton"
        data-bs-toggle="dropdown"
        aria-haspopup="true" aria-expanded="false">{{ __('messages.common.actions') }}
    </a>
    <ul class="dropdown-menu action-dropdown" aria-labelledby="dropdownMenuButton">
        <li>
            <a href="{{ isRole('clinic_admin') ? route('medicines.create') : (isRole('staff') ? route('staff.medicines.create') : route('doctors.medicines.create')) }}"
                class="dropdown-item  px-5">{{ __('messages.medicine.new_medicine') }}</a>
        </li>
        {{-- Excel export not implemented for medicines module (only purchase-medicine has excel export) --}}
        {{-- <li>
                <a href="{{ isRole('clinic_admin') ? route('medicines.excel') : (isRole('staff') ? route('staff.medicines.excel') : route('doctors.medicines.excel')) }}"
        class="dropdown-item px-5" >{{ __('messages.common.export_to_excel') }}</a>
        </li> --}}
    </ul>
</div>
@else
<a href="{{ isRole('clinic_admin') ? route('medicines.create') : (isRole('staff') ? route('staff.medicines.create') : route('doctors.medicines.create')) }}" class="btn btn-primary">
    {{ __('messages.medicine.new_medicine') }}
</a>
@endif