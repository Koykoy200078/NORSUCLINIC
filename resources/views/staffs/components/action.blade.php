<div class="d-flex justify-content-center">
    <a href="{{ route('staffs.edit', $row->id)  }}" title="{{__('messages.common.edit') }}"
        class="btn px-1 text-primary fs-3 ps-0">
        <i class="fa-solid fa-pen-to-square"></i>
    </a>
    @if(isRole('clinic_admin'))
    <a href="javascript:void(0)"
        data-id="{{ $row->id }}"
        data-reset-url="{{ route('staffs.reset.password', $row->id) }}"
        title="{{ __('Reset Password') }}"
        data-bs-toggle="tooltip"
        data-bs-original-title="{{ __('Reset Password') }}"
        class="staff-reset-password-btn btn px-1 text-warning fs-3 ps-0">
        <i class="fa-solid fa-key"></i>
    </a>
    <a href="javascript:void(0)" title="{{__('messages.common.delete')}}"
        data-id="{{ $row->id }}"
        data-delete-url="{{ route('staffs.destroy', $row->id) }}"
        wire:key="{{$row->id}}"
        class="staff-delete-btn btn px-1 text-danger fs-3 ps-0">
        <i class="fa-solid fa-trash"></i>
    </a>
    @endif
</div>