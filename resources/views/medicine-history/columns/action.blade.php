{{-- Full CRUD access for all roles including doctors --}}
<div class="d-flex align-items-center justify-content-center">
    <a href="{{ isRole('clinic_admin') ? route('medicine-history.show', [$row->id]) : (isRole('staff') ? route('staff.medicine-history.show', [$row->id]) : route('doctors.medicine-history.show', [$row->id])) }}"
        class='btn px-2 text-primary fs-3 ps-0'> <i class="fas fa-eye text-success"></i></a>
    {{-- @if(isset($row->payment_status) && $row->payment_status == false)  --}}
    <a
        href="{{ isRole('clinic_admin') ? route('medicine-history.edit', [$row->id]) : (isRole('staff') ? route('staff.medicine-history.edit', [$row->id]) : route('doctors.medicine-history.edit', [$row->id])) }}"
        title="<?php echo __('messages.common.edit') ?>" class="btn px-2 edit-btn text-primary fs-3 py-2">
        <i class="fa-solid fa-pen-to-square"></i>
    </a>
    {{-- @endif  --}}
    {{-- @if(isset($row->payment_status) && $row->payment_status == true)  --}}
    <a title="<?php echo __('messages.common.delete'); ?>" data-id="{{ $row->id }}"
        class="btn medicine-bill-delete-btn px-2 text-danger pe-0 py-2">
        <i class="fa-solid fa-trash"></i>
    </a>
    {{-- @endif  --}}
</div>
