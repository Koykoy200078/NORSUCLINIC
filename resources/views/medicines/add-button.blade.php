{{-- Full CRUD access for all roles including doctors --}}
<div class="dropdown">
    @if(Auth::user()->hasRole('Pharmacist'))
    <a href="#" class="btn btn-primary" id="dropdownMenuButton" data-bs-toggle="dropdown"
        aria-haspopup="true" aria-expanded="false">{{ __('messages.common.actions') }}
        <i class="fa fa-chevron-down"></i>
    </a>
    <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton">
        <li>
            <a href="{{ isRole('clinic_admin') ? route('medicines.create') : (isRole('staff') ? route('staff.medicines.create') : route('doctors.medicines.create')) }}"
                class="dropdown-item  px-5">{{ __('messages.medicine.new_medicine') }}</a>
        </li>
        {{-- Excel export not implemented for medicines module --}}
        {{-- <li>
            <a href="{{ isRole('clinic_admin') ? route('medicines.excel') : (isRole('staff') ? route('staff.medicines.excel') : route('doctors.medicines.excel')) }}"
        class="dropdown-item px-5">{{ __('messages.common.export_to_excel') }}</a>
        </li> --}}
    </ul>
    @else
    <a href="{{ isRole('clinic_admin') ? route('medicines.create') : (isRole('staff') ? route('staff.medicines.create') : route('doctors.medicines.create')) }}"
        class="btn btn-primary">{{ __('messages.medicine.new_medicine') }}</a>
    @endif
</div>