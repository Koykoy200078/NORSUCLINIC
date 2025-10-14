<div class="d-flex flex-column">
    <span class="badge badge-light-info">
        {{ \Carbon\Carbon::parse($row->date)->format('M d, Y') }}
    </span>
    @if($row->from_time && $row->to_time)
    <small class="text-muted mt-1">
        {{ $row->from_time }} - {{ $row->to_time }}
    </small>
    @endif
</div>