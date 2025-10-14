<span class="badge bg-{{ $row->source === 'Consultation' ? 'info' : 'success' }}">
    {{ $row->source }}
</span>
@if($row->source === 'Consultation' && $row->used_for && $row->used_for !== 'N/A')
<br><small class="text-muted">{{ ucfirst($row->used_for) }}</small>
@endif