@if($row->room_number)
<span class="badge bg-info text-dark">
    <i class="fas fa-door-open"></i> Room {{ $row->room_number }}
</span>
@else
<span class="text-muted">-</span>
@endif