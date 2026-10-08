{{-- One doctor page per panel: /admin/doctors/{id} for the administrator, /staff/doctors/{id} for staff. --}}
@php($doctorShowUrl = getRouteByRole('doctors.show', [$row->id]))
<div class="d-flex align-items-center">
    <a href="{{ $doctorShowUrl }}">
        <div class="image image-circle image-mini me-3">
            <img src="{{$row->user->profile_image}}" alt="" class="user-img">
        </div>
    </a>
    <div class="d-flex flex-column">
        <div class="d-inline-block align-top">
            <div class="d-inline-block align-self-center d-flex">
                <a href="{{ $doctorShowUrl }}" class="mb-1 text-decoration-none fs-6">
                    {{$row->user->full_name}}
                </a>
            </div>
        </div>
        <span class="fs-6">{{$row->user->email}}</span>
    </div>
</div>
