<div class="d-flex justify-content-center">
    <a href="{{ 
        isRole('clinic_admin') ? route('services.edit', $row->id) : 
        (isRole('staff') ? route('staff.services.edit', $row->id) : 
        (isRole('doctor') ? route('doctors.services.edit', $row->id) : route('services.edit', $row->id)))
    }}" title="{{ __('messages.common.edit') }}"
        class="btn px-2 text-primary fs-2  edit-btn" data-bs-toggle="tooltip"
        data-bs-original-title="{{ __('messages.common.edit') }}" data-id="{{$row->id}}">
        <i class="fa-solid fa-pen-to-square"></i>
    </a>
    <a href="javascript:void(0)" data-id="{{ $row->id }}" title="{{ __('messages.common.delete') }}" data-bs-toggle="tooltip"
        data-bs-original-title="{{ __('messages.common.delete') }}"
        class="btn px-2 text-danger fs-2 service-delete-btn">
        <i class="fa-solid fa-trash"></i>
    </a>
</div>