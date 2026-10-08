@php
$patientUser = $row->user;
$patientDisplayName = $patientUser
? trim(($patientUser->first_name ?? '') . ' ' . ($patientUser->last_name ?? ''))
: ('Patient #' . $row->id);
$isPrescriptionModule = request()->query('module') === 'prescription';
@endphp

<div class="d-flex justify-content-center">
    @if($isPrescriptionModule)
    @if($patientUser && !$row->trashed() && (isRole('clinic_admin') || isRole('staff') || isRole('doctor')) && canStaffAccessModule('prescriptions'))
    <a href="{{ getRouteByRole('prescriptions.create', ['patientId' => $row->id]) }}" title="Create Prescription" data-bs-toggle="tooltip"
        data-bs-original-title="Create Prescription"
        class="btn px-2 text-success fs-2" data-turbolinks="false">
        <i class="fa-solid fa-file-prescription"></i>
    </a>
    @endif
    @else
    @if($patientUser && !$row->trashed() && empty($patientUser->email_verified_at))
    <a href="javascript:void(0)" data-id="{{ $row->user->id }}"
        data-verification-url="{{ getRouteByRole('resend.email.verification', ['userId' => $row->user->id]) }}"
        class="btn px-2 text-primary fs-2 patient-email-verification" data-bs-toggle="tooltip"
        data-bs-original-title="{{__('messages.resend_email_verification')}}">
        <span class="svg-icon svg-icon-3">
            <i class="fas fa-envelope"></i>
        </span>
    </a>
    @endif

    @if($patientUser && !$row->trashed())
    <!-- View Select Patient -->
    <a href="{{ getRouteByRole('patients.showMyHistory', ['patient' => $row->id]) }}" title="View Patient" data-bs-toggle="tooltip"
        data-bs-original-title="{{ __('messages.common.edit') }}"
        class="btn px-2 text-primary fs-2" data-turbolinks="false">
        <i class="fa fa-eye" aria-hidden="true"></i>
    </a>

    <!-- End View Select Patient -->
    <a href="{{ getRouteByRole('patients.edit', ['patient' => $row->id]) }}" title="{{ __('messages.common.edit') }}" data-bs-toggle="tooltip"
        data-bs-original-title="{{ __('messages.common.edit') }}"
        class="btn px-2 text-primary fs-2" data-turbolinks="false">
        <i class="fa-solid fa-pen-to-square"></i>
    </a>
    {{-- Staff may start a prescription only with the prescriptions module (nurses, triage, front desk... get a 403). --}}
    @if((isRole('clinic_admin') || isRole('staff') || isRole('doctor')) && canStaffAccessModule('prescriptions'))
    <a href="{{ getRouteByRole('prescriptions.create', ['patientId' => $row->id]) }}" title="Create Prescription" data-bs-toggle="tooltip"
        data-bs-original-title="Create Prescription"
        class="btn px-2 text-success fs-2" data-turbolinks="false">
        <i class="fa-solid fa-file-prescription"></i>
    </a>
    @endif
    {{-- The reset-password route is admin-only (staff/doctor would get a 403), so only the admin sees the key. --}}
    @if(isRole('clinic_admin'))
    <a href="javascript:void(0)"
        data-id="{{ $row->user->id }}"
        data-reset-url="{{ getRouteByRole('patients.reset.password', ['user' => $row->user->id]) }}"
        title="{{ __('Reset Password') }}"
        data-bs-toggle="tooltip"
        data-bs-original-title="{{ __('Reset Password') }}"
        class="btn px-2 text-warning fs-2 patient-reset-password-btn">
        <i class="fa-solid fa-key"></i>
    </a>
    @endif
    @endif

    {{-- Archive / restore belong to staff/nurse and the clinic admin; doctors only view and edit. --}}
    @if(! isRole('doctor'))
    @if($row->trashed())
    <a href="javascript:void(0)"
        data-id="{{ $row->id }}"
        data-patient-name="{{ $patientDisplayName }}"
        data-restore-url="{{ getRouteByRole('patients.restore', ['patient' => $row->id]) }}"
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
        data-delete-url="{{ getRouteByRole('patients.destroy', ['patient' => $row->id]) }}"
        title="Archive Patient"
        data-bs-toggle="tooltip"
        data-bs-original-title="Archive Patient"
        class="btn px-2 text-danger fs-2 patient-delete-btn">
        <i class="fa-solid fa-box-archive"></i>
    </a>
    @endif
    @endif
    @endif
</div>