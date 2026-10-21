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
        getRouteByRole('patients.show', [$row->id])
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
                getRouteByRole('patients.show', [$row->id])
            }}" class="text-decoration-none fs-6">
                {{$patientFullName}}
            </a>
            @else
            <span class="fs-6">{{$patientFullName}}</span>
            @endif
        </div>
        <span class="fs-6">{{$patientEmail}}</span>
        @if($row->merged_into_id)
            <span class="badge bg-secondary text-dark">Merged into #{{ $row->merged_into_id }}</span>
        @endif
    </div>
</div>
