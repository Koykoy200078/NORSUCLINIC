@php
use App\Models\PatientQueue;
@endphp

@if($row->priority)
<span class="badge bg-danger">
    <i class="fas fa-exclamation-triangle"></i> PRIORITY
</span>
@else
<span class="badge bg-secondary">Normal</span>
@endif