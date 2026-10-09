@php
// A doctor links to the doctor page; a nurse (or anyone else) who recorded a consultation has no doctor page.
$doctorUrl = null;
if ($row->doctor_id && canUseModule('doctors')) {
    if (isRole('staff')) {
        // Only the clinic head has the doctors module; every other staff designation would get a 403 from the link,
        // and must never fall through to the administrator URL below.
        $doctorUrl = (canUseModule('doctors') && \Illuminate\Support\Facades\Route::has('staff.doctors.show'))
            ? route('staff.doctors.show', $row->doctor_id) : null;
    } elseif (isRole('doctor') && \Illuminate\Support\Facades\Route::has('doctors.doctors.detail')) {
        $doctorUrl = route('doctors.doctors.detail', $row->doctor_id);
    } elseif (\Illuminate\Support\Facades\Route::has('doctors.show')) {
        $doctorUrl = route('doctors.show', $row->doctor_id);
    }
}
@endphp

@if ($row->given_by)
<div class="d-flex flex-column">
    @if ($doctorUrl)
    <a href="{{ $doctorUrl }}" class="text-decoration-none mb-1">{{ $row->given_by }}</a>
    @else
    <span class="mb-1">{{ $row->given_by }}</span>
    @endif
    <span>{{ $row->given_by_email ?? 'N/A' }}</span>
</div>
@else
NA
@endif
