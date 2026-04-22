@php
$patient = $row->patient ?? null;
$patientUser = null;
if ($patient && $patient->relationLoaded('user')) {
$patientUser = $patient->user;
} elseif ($patient && $patient->relationLoaded('patientUser')) {
$patientUser = $patient->patientUser;
}

$patientUrl = null;
if ($patient) {
if (isRole('staff') && \Illuminate\Support\Facades\Route::has('staff.patients.show')) {
$patientUrl = route('staff.patients.show', $patient->id);
} elseif (isRole('doctor') && \Illuminate\Support\Facades\Route::has('doctors.patients.show')) {
$patientUrl = route('doctors.patients.show', $patient->id);
} elseif (\Illuminate\Support\Facades\Route::has('patients.show')) {
$patientUrl = route('patients.show', $patient->id);
}
}
@endphp

@if ($patient && $patientUser)
<div class="d-flex align-items-center">
    <div class="image image-mini me-3">
        <a href="{{ $patientUrl ?? 'javascript:void(0)' }}">
            <div>
                <img src="{{ $patient->profile }}" alt=""
                    class="user-img image image-circle object-contain">
            </div>
        </a>
    </div>
    <div class="d-flex flex-column">
        <a href="{{ $patientUrl ?? 'javascript:void(0)' }}"
            class="text-decoration-none mb-1">{{ $patientUser->full_name }}</a>
        <span>{{ $patientUser->email ?? 'N/A' }}</span>
    </div>
</div>
@else
NA
@endif