{{-- Full CRUD access for all roles including doctors --}}
<div class="dropdown">
    @if(Auth::user()->hasRole('Pharmacist'))
    <a href="#" class="btn btn-primary" id="dropdownMenuButton" data-bs-toggle="dropdown"
        aria-haspopup="true" aria-expanded="false">{{ __('messages.common.actions') }}
        <i class="fa fa-chevron-down"></i>
    </a>
    <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton">
        <li>
            <a href="javascript:void(0)" onclick="bootstrap.Modal.getOrCreateInstance(document.getElementById('add_generic_modal')).show()"
                class="dropdown-item  px-5">{{ __('messages.medicine.new_medicine_generic') }}</a>
        </li>
        {{-- Excel export removed - route does not exist
        <li>
            <a href="{{ 
                isRole('clinic_admin') ? route('generics.excel') : 
                (isRole('staff') ? route('staff.generics.excel') : route('generics.excel')) 
            }}"
        class="dropdown-item px-5">{{ __('messages.common.export_to_excel') }}</a>
        </li>
        --}}
    </ul>
    @else
    <a href="javascript:void(0)" onclick="bootstrap.Modal.getOrCreateInstance(document.getElementById('add_generic_modal')).show()"
        class="btn btn-primary">{{ __('messages.medicine.new_medicine_generic') }}</a>
    @endif
</div>