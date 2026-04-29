@php
$typeName = optional($row->patientType)->name;
$typeCode = optional($row->patientType)->code;

$badgeClass = match ($typeCode) {
    'student' => 'bg-primary',
    'faculty' => 'bg-success',
    'staff' => 'bg-warning',
    'guest' => 'bg-info',
    default => 'bg-secondary',
};
@endphp

@if($typeName)
    <span class="badge {{ $badgeClass }}">{{ $typeName }}</span>
@else
    <span class="badge bg-secondary">{{ __('messages.common.n/a') }}</span>
@endif
