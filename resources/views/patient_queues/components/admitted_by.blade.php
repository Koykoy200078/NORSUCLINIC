<div class="d-flex align-items-center">
    @if($row->admittedBy)
    <div>
        <div class="fw-bold">{{ $row->admittedBy->full_name ?? $row->admittedBy->first_name . ' ' . $row->admittedBy->last_name }}</div>
        @if($row->admitted_at)
        <small class="text-muted">{{ $row->admitted_at->format('M d, Y h:i A') }}</small>
        @endif
    </div>
    @else
    <span class="text-muted">-</span>
    @endif
</div>