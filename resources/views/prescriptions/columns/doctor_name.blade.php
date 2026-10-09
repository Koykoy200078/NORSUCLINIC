@php
$doctor = $row->doctor ?? null;
$doctorUser = null;
if ($doctor && $doctor->relationLoaded('user')) {
$doctorUser = $doctor->user;
} elseif ($doctor && $doctor->relationLoaded('doctorUser')) {
$doctorUser = $doctor->doctorUser;
}

$doctorUrl = null;
if ($doctor && canUseModule('doctors')) {
if (isRole('staff')) {
// Only the clinic head has the doctors module; every other staff designation would get a 403 from the link,
// and must never fall through to the administrator URL below.
$doctorUrl = (canUseModule('doctors') && \Illuminate\Support\Facades\Route::has('staff.doctors.show'))
? route('staff.doctors.show', $doctor->id) : null;
} elseif (isRole('doctor') && \Illuminate\Support\Facades\Route::has('doctors.doctors.detail')) {
$doctorUrl = route('doctors.doctors.detail', $doctor->id);
} elseif (\Illuminate\Support\Facades\Route::has('doctors.show')) {
$doctorUrl = route('doctors.show', $doctor->id);
}
}

$doctorImage = (($doctorUser?->gender ?? null) == \App\Models\User::FEMALE)
? asset('web/media/avatars/female.png')
: asset('web/media/avatars/male.png');
@endphp

@if ($doctor && $doctorUser)
<div class="d-flex align-items-center">
    <div class="image image-mini me-3">
        <a href="{{ $doctorUrl ?? 'javascript:void(0)' }}">
            <div>
                <img src="{{ $doctorImage }}" alt=""
                    class="user-img image rounded-circle object-contain">
            </div>
        </a>
    </div>
    <div class="d-flex flex-column">
        <a href="{{ $doctorUrl ?? 'javascript:void(0)' }}"
            class="mb-1 text-decoration-none">{{ $doctorUser->full_name }}</a>
        <span>{{ $doctorUser->email ?? 'N/A' }}</span>
    </div>
</div>
@else
N/A
@endif