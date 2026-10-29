@php
$badge = match ($row->source) {
    \App\Models\DispenseHistoryEntry::SOURCE_CONSULTATION => 'bg-light-success',
    \App\Models\DispenseHistoryEntry::SOURCE_PRESCRIPTION => 'bg-light-warning',
    \App\Models\DispenseHistoryEntry::SOURCE_MEDS_ONLY => 'bg-light-info',
    default => 'bg-light-primary',
};
@endphp
<span class="badge {{ $badge }}">{{ $row->source }}</span>
@if ($row->isConsultation() && $row->used_for)
<div class="text-muted fs-7 mt-1">{{ collect(explode(',', $row->used_for))->map(fn ($part) => ucfirst($part))->implode(' + ') }}</div>
@endif
