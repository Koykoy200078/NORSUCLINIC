@php
$patientUser = $row->user;
$patientDisplayName = $patientUser
? trim(($patientUser->first_name ?? '') . ' ' . ($patientUser->last_name ?? ''))
: ('Patient #' . $row->id);
@endphp

<div class="d-flex justify-content-center">
    @if($patientUser && !$row->trashed() && empty($patientUser->email_verified_at))
    <a href="javascript:void(0)" data-id="{{ $row->user->id }}"
        data-verification-url="{{
            isRole('clinic_admin') ? route('resend.email.verification', $row->user->id) : 
            (isRole('staff') ? route('staff.resend.email.verification', $row->user->id) : 
            (isRole('doctor') ? route('doctors.resend.email.verification', $row->user->id) : route('resend.email.verification', $row->user->id)))
        }}"
        class="btn px-2 text-primary fs-2 patient-email-verification" data-bs-toggle="tooltip"
        data-bs-original-title="{{__('messages.resend_email_verification')}}">
        <span class="svg-icon svg-icon-3">
            <i class="fas fa-envelope"></i>
        </span>
    </a>
    @endif

    @if($patientUser && !$row->trashed())
    <!-- View Select Patient -->
    <a href="{{ 
        isRole('clinic_admin') ? route('patients.showMyHistory', ['patient' => $row->id]) : 
        (isRole('staff') ? route('staff.patients.showMyHistory', ['patient' => $row->id]) : 
        (isRole('doctor') ? route('doctors.patients.showMyHistory', ['patient' => $row->id]) : route('patients.showMyHistory', ['patient' => $row->id])))
    }}" title="View Patient" data-bs-toggle="tooltip"
        data-bs-original-title="{{ __('messages.common.edit') }}"
        class="btn px-2 text-primary fs-2" data-turbolinks="false">
        <i class="fa fa-eye" aria-hidden="true"></i>
    </a>

    <!-- End View Select Patient -->
    <a href="{{ 
        isRole('clinic_admin') ? route('patients.edit', $row->id) : 
        (isRole('staff') ? route('staff.patients.edit', $row->id) : 
        (isRole('doctor') ? route('doctors.patients.edit', $row->id) : route('patients.edit', $row->id)))
    }}" title="{{ __('messages.common.edit') }}" data-bs-toggle="tooltip"
        data-bs-original-title="{{ __('messages.common.edit') }}"
        class="btn px-2 text-primary fs-2" data-turbolinks="false">
        <i class="fa-solid fa-pen-to-square"></i>
    </a>
    <a href="javascript:void(0)"
        data-id="{{ $row->user->id }}"
        data-reset-url="{{ 
            isRole('clinic_admin') ? route('patients.reset.password', $row->user->id) : 
            (isRole('staff') ? route('staff.patients.reset.password', $row->user->id) : 
            (isRole('doctor') ? route('doctors.patients.reset.password', $row->user->id) : route('patients.reset.password', $row->user->id)))
        }}"
        title="{{ __('Reset Password') }}"
        data-bs-toggle="tooltip"
        data-bs-original-title="{{ __('Reset Password') }}"
        class="btn px-2 text-warning fs-2 patient-reset-password-btn">
        <i class="fa-solid fa-key"></i>
    </a>
    @endif

    @if($row->trashed())
    <a href="javascript:void(0)"
        data-id="{{ $row->id }}"
        data-patient-name="{{ $patientDisplayName }}"
        data-restore-url="{{ 
            isRole('clinic_admin') ? route('patients.restore', $row->id) : 
            (isRole('staff') ? route('staff.patients.restore', $row->id) : 
            (isRole('doctor') ? route('doctors.patients.restore', $row->id) : route('patients.restore', $row->id)))
        }}"
        title="Restore Patient"
        data-bs-toggle="tooltip"
        data-bs-original-title="Restore Patient"
        class="btn px-2 text-success fs-2 patient-restore-btn">
        <i class="fa-solid fa-trash-can-arrow-up"></i>
    </a>
    @elseif($patientUser)
    <a href="javascript:void(0)"
        data-id="{{ $row->id }}"
        data-patient-name="{{ $patientDisplayName }}"
        data-delete-url="{{ 
            isRole('clinic_admin') ? route('patients.destroy', $row->id) : 
            (isRole('staff') ? route('staff.patients.destroy', $row->id) : 
            (isRole('doctor') ? route('doctors.patients.destroy', $row->id) : route('patients.destroy', $row->id)))
        }}"
        title="Archive Patient"
        data-bs-toggle="tooltip"
        data-bs-original-title="Archive Patient"
        class="btn px-2 text-danger fs-2 patient-delete-btn">
        <i class="fa-solid fa-box-archive"></i>
    </a>
    @endif
</div>