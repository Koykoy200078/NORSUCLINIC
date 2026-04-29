@php
$patientUser = $row->user;
$patientFullName = $patientUser
? trim(($patientUser->first_name ?? '') . ' ' . ($patientUser->last_name ?? ''))
: ('Archived Patient #' . $row->id);
$patientEmail = $patientUser->email ?? 'No email available';
@endphp

<div class="d-flex align-items-center">
    @if(!$row->trashed())
    <a href="{{ 
        isRole('clinic_admin') ? route('patients.show', $row->id) : 
        (isRole('staff') ? route('staff.patients.show', $row->id) : 
        (isRole('doctor') ? route('doctors.patients.show', $row->id) : route('patients.show', $row->id)))
    }}">
        @endif
        <div class="image image-circle image-mini me-3">
            <img src="{{$row->profile}}" alt="user" class="user-img">
        </div>
        @if(!$row->trashed())
    </a>
    @endif
    <div class="d-flex flex-column">
        <div class="d-flex align-items-center mb-1">
            @if(!$row->trashed())
            <a href="{{ 
                isRole('clinic_admin') ? route('patients.show', $row->id) : 
                (isRole('staff') ? route('staff.patients.show', $row->id) : 
                (isRole('doctor') ? route('doctors.patients.show', $row->id) : route('patients.show', $row->id)))
            }}" class="text-decoration-none fs-6">
                {{$patientFullName}}
            </a>
            @else
            <span class="fs-6">{{$patientFullName}}</span>
            @endif
        </div>
        <span class="fs-6">{{$patientEmail}}</span>
    </div>
</div>