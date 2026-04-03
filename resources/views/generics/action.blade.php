{{-- Full CRUD access for all roles including doctors --}}
<div class="d-flex justify-content-center align-items-center">
    <a href="{{ 
    isRole('clinic_admin') ? route('generics.edit', $row->id) : 
    (isRole('staff') ? route('staff.generics.edit', $row->id) : 
    (isRole('doctor') ? route('doctors.generics.edit', $row->id) : route('generics.edit', $row->id))) 
}}" title="<?php echo __('messages.common.edit') ?>"
        class="btn px-2 text-primary fs-3">
        <i class="fa-solid fa-pen-to-square"></i>
    </a>
    <a href="javascript:void(0)" title="<?php echo __('messages.common.delete') ?>" data-id="{{$row->id}}" data-name="{{$row->name}}" wire:key="{{$row->id}}"
        class="generic-delete-btn btn px-2 text-danger fs-3">
        <i class="fa-solid fa-trash"></i>
    </a>
</div>