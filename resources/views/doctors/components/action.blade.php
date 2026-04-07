<div class="d-flex justify-content-center">
    @if(empty($row->user->email_verified_at))
    <a href="javascript:void(0)" data-id="{{ $row->user->id }}"
        class="btn px-2 text-primary fs-2 doctor-email-verification"
        data-bs-toggle="tooltip" data-bs-original-title="{{__('messages.resend_email_verification')}}">
        <span class="svg-icon svg-icon-3">
            <i class="fas fa-envelope"></i>
        </span>
    </a>
    @endif

    <a data-id="{{ $row->user->id }}" data-bs-toggle="tooltip"
        data-bs-original-title="{{ __('messages.doctor.add_qualification') }}"
        class="btn px-2 fs-2 add-qualification">
        <i class="fa-solid fa-plus"></i>
    </a>
    <a href="{{ 
        isRole('clinic_admin') ? route('doctors.edit', $row->id) : 
        (isRole('staff') ? route('staff.doctors.edit', $row->id) : route('doctors.edit', $row->id))
    }}" title="{{ __('messages.common.edit') }}" class="btn px-2 text-primary fs-2 doctor-edit-btn" data-bs-toggle="tooltip"
        data-bs-original-title="Edit" data-turbolinks="false">
        <i class="fa-solid fa-pen-to-square"></i>
    </a>
    <a href="javascript:void(0)"
        data-id="{{ $row->user->id }}"
        data-reset-url="{{ 
            isRole('clinic_admin') ? route('doctors.reset.password', $row->user->id) : 
            (isRole('staff') ? route('staff.doctors.reset.password', $row->user->id) : route('doctors.reset.password', $row->user->id))
        }}"
        title="{{ __('Reset Password') }}"
        data-bs-toggle="tooltip"
        data-bs-original-title="{{ __('Reset Password') }}"
        class="btn px-2 text-warning fs-2 doctor-reset-password-btn">
        <i class="fa-solid fa-key"></i>
    </a>
    <a href="javascript:void(0)"
        data-id="{{ $row->id }}"
        data-delete-url="{{ 
            isRole('clinic_admin') ? route('doctors.destroy', $row->id) : 
            (isRole('staff') ? route('staff.doctors.destroy', $row->id) : route('doctors.destroy', $row->id))
        }}"
        title="{{ __('messages.common.delete') }}"
        class="btn px-2 text-danger fs-2 doctor-delete-btn"
        data-bs-toggle="tooltip"
        data-bs-original-title="Delete">
        <i class="fa-solid fa-trash"></i>
    </a>
</div>