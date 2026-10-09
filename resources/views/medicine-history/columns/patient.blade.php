@php
$patientUrl = null;
if ($row->patient && $row->patient->user && canUseModule('patients')) {
    if (isRole('staff')) {
        // Staff open a patient page only with the patients module (a pharmacist has none: the link would answer 403),
        // and must never fall through to the administrator URL below.
        $patientUrl = (canUseModule('patients') && \Illuminate\Support\Facades\Route::has('staff.patients.show'))
            ? route('staff.patients.show', $row->patient_id) : null;
    } elseif (isRole('doctor') && \Illuminate\Support\Facades\Route::has('doctors.patients.show')) {
        $patientUrl = route('doctors.patients.show', $row->patient_id);
    } elseif (\Illuminate\Support\Facades\Route::has('patients.show')) {
        $patientUrl = route('patients.show', $row->patient_id);
    }
}
@endphp

@if ($row->patient_name)
<div class="d-flex align-items-center">
    <div class="image image-mini me-3">
        <a href="{{ $patientUrl ?? 'javascript:void(0)' }}">
            <div>
                <img src="{{ $row->patient?->profile ?? asset('web/media/avatars/male.png') }}" alt=""
                    class="user-img image image-circle object-contain">
            </div>
        </a>
    </div>
    <div class="d-flex flex-column">
        <a href="{{ $patientUrl ?? 'javascript:void(0)' }}"
            class="text-decoration-none mb-1">{{ $row->patient_name }}</a>
        <span>{{ $row->patient_email ?? 'N/A' }}</span>
    </div>
</div>
@else
NA
@endif
