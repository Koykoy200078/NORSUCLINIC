<div class="d-flex justify-content-center">
    @if(empty($row->user->email_verified_at))
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

    <!-- View Select Patient -->
    @if(isset($row->request_documents_count) && $row->request_documents_count > 0)
    <a href="{{ 
        isRole('clinic_admin') ? route('patients.showMyHistory', ['patient' => $row->id]) : 
        (isRole('staff') ? route('staff.patients.showMyHistory', ['patient' => $row->id]) : 
        (isRole('doctor') ? route('doctors.patients.showMyHistory', ['patient' => $row->id]) : route('patients.showMyHistory', ['patient' => $row->id])))
    }}" title="View Patient" data-bs-toggle="tooltip"
        data-bs-original-title="{{ __('messages.common.edit') }}"
        class="btn px-2 text-primary fs-2" data-turbolinks="false">
        <i class="fa fa-eye" aria-hidden="true"></i>
    </a>
    @endif

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
    <a href="javascript:void(0)" data-id="{{ $row->id }}" title="{{ __('messages.common.delete') }}" data-bs-toggle="tooltip"
        data-bs-original-title="{{ __('messages.common.delete') }}"
        class="btn px-2 text-danger fs-2 patient-delete-btn">
        <i class="fa-solid fa-trash"></i>
    </a>
</div>