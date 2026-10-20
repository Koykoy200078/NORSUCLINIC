@php
$patientUrl = null;
if ($row->patient && $row->patient->user && canUseModule('patients')) {
    if (\Illuminate\Support\Facades\Route::has(getRouteNameByRole('patients.show'))) {
        $patientUrl = getRouteByRole('patients.show', [$row->patient_id]);
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
