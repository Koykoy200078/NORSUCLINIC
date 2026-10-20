@php
// A doctor links to the doctor page; a nurse (or anyone else) who recorded a consultation has no doctor page.
$doctorUrl = null;
if ($row->doctor_id && canUseModule('doctors')) {
    if (\Illuminate\Support\Facades\Route::has(getRouteNameByRole('doctors.show'))) {
        $doctorUrl = getRouteByRole('doctors.show', [$row->doctor_id]);
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
