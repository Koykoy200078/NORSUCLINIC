@if($row->priority)
<span class="badge bg-danger" title="Priority Patient">
    <i class="fas fa-exclamation-triangle"></i> PRIORITY
</span>
@else
<span class="badge bg-secondary">Normal</span>
@endif