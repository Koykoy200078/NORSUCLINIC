<x-row-actions module="staffs">
    <a href="{{ route('staffs.edit', $row->id)  }}" title="{{__('messages.common.edit') }}"
        class="dropdown-item px-1 text-primary fs-3 ps-0" role="menuitem" tabindex="-1">
        <i class="fa-solid fa-pen-to-square"></i>
        <span>{{__('messages.common.edit') }}</span>
    </a>
    @if(isRole('clinic_admin'))
    <a href="javascript:void(0)"
        data-id="{{ $row->id }}"
        data-reset-url="{{ route('staffs.reset.password', $row->id) }}"
        title="{{ __('Reset Password') }}"
        data-bs-toggle="tooltip"
        data-bs-original-title="{{ __('Reset Password') }}"
        class="dropdown-item staff-reset-password-btn px-1 text-warning fs-3 ps-0" role="menuitem" tabindex="-1">
        <i class="fa-solid fa-key"></i>
        <span>{{ __('Reset Password') }}</span>
    </a>
    <a href="javascript:void(0)" title="{{__('messages.common.delete')}}"
        data-id="{{ $row->id }}"
        data-delete-url="{{ route('staffs.destroy', $row->id) }}"
        wire:key="{{$row->id}}"
        class="dropdown-item staff-delete-btn px-1 text-danger fs-3 ps-0" role="menuitem" tabindex="-1">
        <i class="fa-solid fa-trash"></i>
        <span>{{__('messages.common.delete')}}</span>
    </a>
    @endif
</x-row-actions>