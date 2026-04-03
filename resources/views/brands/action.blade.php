{{-- Full CRUD access for all roles including doctors --}}
<div class="d-flex justify-content-center align-items-center">
    <a href="{{ 
    isRole('clinic_admin') ? route('brands.edit', $row->id) : 
    (isRole('staff') ? route('staff.brands.edit', $row->id) : 
    (isRole('doctor') ? route('doctors.brands.edit', $row->id) : route('brands.edit', $row->id))) 
}}" title="<?php echo __('messages.common.edit') ?>"
        class="btn px-2 text-primary fs-3">
        <i class="fa-solid fa-pen-to-square"></i>
    </a>
    <a href="javascript:void(0)" title="<?php echo __('messages.common.delete') ?>" data-id="{{$row->id}}" wire:key="{{$row->id}}"
        data-delete-url="{{
            isRole('clinic_admin') ? route('brands.destroy', $row->id) :
            (isRole('staff') ? route('staff.brands.destroy', $row->id) :
            (isRole('doctor') ? route('doctors.brands.destroy', $row->id) : route('brands.destroy', $row->id)))
        }}"
        class="brand-delete-btn btn px-2 text-danger fs-3">
        <i class="fa-solid fa-trash"></i>
    </a>
</div>