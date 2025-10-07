{{-- Full CRUD access for all roles including doctors --}}
<div class="d-flex justify-content-center me-4">
    <a href="{{ 
    isRole('clinic_admin') ? route('brands.edit', $row->id) : 
    (isRole('staff') ? route('staff.brands.edit', $row->id) : 
    (isRole('doctor') ? route('doctors.brands.edit', $row->id) : route('brands.edit', $row->id))) 
}}" title="<?php echo __('messages.common.edit') ?>"
        class="btn px-1 text-primary fs-3 ps-0 brand-edit-btn">
        <i class="fa-solid fa-pen-to-square"></i>
    </a>
    <a href="javascript:void(0)" title="<?php echo __('messages.common.delete') ?>" data-id="{{$row->id}}" wire:key="{{$row->id}}"
        class="brand-delete-btn btn px-1 text-danger fs-3 ps-0">
        <i class="fa-solid fa-trash"></i>
    </a>
</div>