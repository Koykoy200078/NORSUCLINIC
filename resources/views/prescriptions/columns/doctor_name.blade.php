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
if (\Illuminate\Support\Facades\Route::has(getRouteNameByRole('doctors.show'))) {
    $doctorUrl = getRouteByRole('doctors.show', [$doctor->id]);
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