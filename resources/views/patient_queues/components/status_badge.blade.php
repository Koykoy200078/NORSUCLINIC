@php
use App\Models\PatientQueue;
@endphp

@if($row->status == PatientQueue::WAITING)
<span class="badge bg-warning text-dark">
    <i class="fas fa-clock"></i> Waiting
</span>
@elseif($row->status == PatientQueue::IN_PROGRESS)
<span class="badge bg-info text-white">
    <i class="fas fa-stethoscope"></i> In Progress
</span>
@elseif($row->status == PatientQueue::COMPLETED)
<span class="badge bg-success">
    <i class="fas fa-check-circle"></i> Completed
</span>
@elseif($row->status == PatientQueue::CANCELLED)
<span class="badge bg-danger">
    <i class="fas fa-times-circle"></i> Cancelled
</span>
@endif