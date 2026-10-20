<x-row-actions module="doctors">
    @if(empty($row->user->email_verified_at))
    <a href="javascript:void(0)" data-id="{{ $row->user->id }}"
        class="dropdown-item px-2 text-primary fs-2 doctor-email-verification"
        data-bs-toggle="tooltip" data-bs-original-title="{{__('messages.resend_email_verification')}}" role="menuitem" tabindex="-1">
        <span class="svg-icon svg-icon-3">
            <i class="fas fa-envelope"></i>
        </span>
        <span>{{__('messages.resend_email_verification')}}</span>
    </a>
    @endif

    <a data-id="{{ $row->user->id }}" data-bs-toggle="tooltip"
        data-bs-original-title="{{ __('messages.doctor.add_qualification') }}"
        class="dropdown-item px-2 fs-2 add-qualification" role="menuitem" href="javascript:void(0)" tabindex="-1">
        <i class="fa-solid fa-plus"></i>
        <span>{{ __('messages.doctor.add_qualification') }}</span>
    </a>
    <a href="{{ 
        getRouteByRole('doctors.edit', [$row->id])
    }}" title="{{ __('messages.common.edit') }}" class="dropdown-item px-2 text-primary fs-2 doctor-edit-btn" data-bs-toggle="tooltip"
        data-bs-original-title="Edit" data-turbolinks="false" role="menuitem" tabindex="-1">
        <i class="fa-solid fa-pen-to-square"></i>
        <span>{{ __('messages.common.edit') }}</span>
    </a>
    <a href="javascript:void(0)"
        data-id="{{ $row->user->id }}"
        data-reset-url="{{ 
            getRouteByRole('doctors.reset.password', [$row->user->id])
        }}"
        title="{{ __('Reset Password') }}"
        data-bs-toggle="tooltip"
        data-bs-original-title="{{ __('Reset Password') }}"
        class="dropdown-item px-2 text-warning fs-2 doctor-reset-password-btn" role="menuitem" tabindex="-1">
        <i class="fa-solid fa-key"></i>
        <span>{{ __('Reset Password') }}</span>
    </a>
    <a href="javascript:void(0)"
        data-id="{{ $row->id }}"
        data-delete-url="{{ 
            getRouteByRole('doctors.destroy', [$row->id])
        }}"
        title="{{ __('messages.common.delete') }}"
        class="dropdown-item px-2 text-danger fs-2 doctor-delete-btn"
        data-bs-toggle="tooltip"
        data-bs-original-title="Delete" role="menuitem" tabindex="-1">
        <i class="fa-solid fa-trash"></i>
        <span>{{ __('messages.common.delete') }}</span>
    </a>
</x-row-actions>