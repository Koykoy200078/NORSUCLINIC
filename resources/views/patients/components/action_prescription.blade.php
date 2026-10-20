@php
$patientUser = $row->user;
@endphp

<div class="d-flex justify-content-center">
    {{-- Staff may start a prescription only with the prescriptions module (nurses, triage, front desk... get a 403). --}}
    @if($patientUser && !$row->trashed() && (isRole('clinic_admin') || isRole('staff') || isRole('doctor')) && canUseModule('prescriptions'))
    <a href="{{ 
        getRouteByRole('prescriptions.create', ['patientId' => $row->id])
    }}" title="Create Prescription" data-bs-toggle="tooltip"
        data-bs-original-title="Create Prescription"
        class="btn px-2 text-success fs-2" data-turbolinks="false">
        <i class="fa-solid fa-file-prescription"></i>
    </a>
    @endif
</div>