@php
$inProgressQueue = $queues->where('status', 'in_progress')->first();
$priorityQueues = $queues->where('is_priority', true)->where('status', 'waiting');
$regularQueues = $queues->where('is_priority', false)->where('status', 'waiting');
$avgWaitTime = $queues->where('status', 'waiting')->avg(function ($q) {
return $q->created_at->diffInMinutes(now());
});
@endphp

<!-- Current Patient in Progress -->
@if($inProgressQueue)
<div class="alert alert-warning border-warning no-auto-hide" role="alert">
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h4 class="alert-heading"><i class="fas fa-user-md"></i> Current Patient</h4>
            <h5>{{ $inProgressQueue->patient->user->full_name }}</h5>
            <p class="mb-0">
                <strong>Patient ID:</strong> {{ $inProgressQueue->patient->patient_unique_id }} |
                @if($inProgressQueue->room_number)
                <strong>Room:</strong> <span class="badge bg-info">{{ $inProgressQueue->room_number }}</span> |
                @endif
                <strong>Called:</strong> {{ $inProgressQueue->called_at->format('h:i A') }}
            </p>
            @if($inProgressQueue->notes)
            <p class="mt-2 mb-0"><strong>Notes:</strong> {{ $inProgressQueue->notes }}</p>
            @endif
        </div>
        <div>
            <form action="{{ route('doctors.patient-queue.complete', $inProgressQueue) }}" method="POST" class="d-inline" onsubmit="return window.completeConsultation(this)">
                @csrf
                <button type="submit" class="btn btn-success btn-lg">
                    <i class="fas fa-check-circle"></i> Complete Consultation
                </button>
            </form>
        </div>
    </div>
</div>
@endif

<div class="row">
    <!-- Priority Queue -->
    <div class="col-md-6 mb-4">
        <div class="card border-danger shadow">
            <div class="card-header bg-danger text-white">
                <h4 class="mb-0"><i class="fas fa-exclamation-triangle"></i> Priority Patients</h4>
            </div>
            <div class="card-body">
                @if($priorityQueues->count() > 0)
                <div class="list-group">
                    @foreach($priorityQueues as $index => $queue)
                    <div class="list-group-item list-group-item-action">
                        <div class="d-flex w-100 justify-content-between">
                            <h5 class="mb-1">
                                <span class="badge bg-danger me-2">{{ $index + 1 }}</span>
                                {{ $queue->patient->user->full_name }}
                            </h5>
                            <small class="text-muted">{{ $queue->created_at->diffForHumans() }}</small>
                        </div>
                        <p class="mb-1">
                            <strong>ID:</strong> {{ $queue->patient->patient_unique_id }}
                            @if($queue->room_number)
                            | <strong>Room:</strong> <span class="badge bg-info">{{ $queue->room_number }}</span>
                            @endif
                            @if($queue->has_consultation_attachment && $queue->latestConsultation)
                            | <span class="badge bg-success" title="Has consultation form">
                                <i class="fas fa-file-medical"></i> Form Available
                            </span>
                            @endif
                        </p>
                        @if($queue->notes)
                        <p class="mb-2"><strong>Notes:</strong> {{ $queue->notes }}</p>
                        @endif
                        @if($queue->has_consultation_attachment && $queue->latestConsultation)
                        <p class="mb-2">
                            <strong>Latest Consultation:</strong>
                            <small class="text-muted">{{ $queue->latestConsultation->created_at->format('M d, Y h:i A') }}</small>
                            <a href="{{ route('doctors.patient-queue.view-consultation', $queue) }}"
                                class="btn btn-sm btn-outline-primary ms-2">
                                <i class="fas fa-eye"></i> View Form
                            </a>
                        </p>
                        @endif
                        <div class="d-flex justify-content-between align-items-center">
                            <small class="text-muted">Added by: {{ $queue->addedBy->full_name }}</small>
                            <div class="btn-group">
                                @if($queue->has_consultation_attachment && $queue->latestConsultation)
                                <a href="{{ route('doctors.patient-queue.view-consultation', $queue) }}"
                                    class="btn btn-sm btn-primary"
                                    title="View Consultation Form">
                                    <i class="fas fa-file-medical"></i>
                                </a>
                                @endif
                                @if(!$inProgressQueue)
                                <form action="{{ route('doctors.patient-queue.call-next', $queue) }}" method="POST" class="d-inline">
                                    @csrf
                                    <input type="hidden" name="queue_id" value="{{ $queue->id }}">
                                    <button type="submit" class="btn btn-sm btn-success">
                                        <i class="fas fa-phone"></i> Call Next
                                    </button>
                                </form>
                                @endif
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
                @else
                <div class="text-center py-4">
                    <i class="fas fa-check-circle fa-3x text-success mb-3"></i>
                    <p class="text-muted">No priority patients waiting</p>
                </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Regular Queue -->
    <div class="col-md-6 mb-4">
        <div class="card border-primary shadow">
            <div class="card-header bg-primary text-white">
                <h4 class="mb-0"><i class="fas fa-users"></i> Regular Queue</h4>
            </div>
            <div class="card-body">
                @if($regularQueues->count() > 0)
                <div class="list-group">
                    @foreach($regularQueues as $index => $queue)
                    <div class="list-group-item list-group-item-action">
                        <div class="d-flex w-100 justify-content-between">
                            <h5 class="mb-1">
                                <span class="badge bg-primary me-2">{{ $index + 1 }}</span>
                                {{ $queue->patient->user->full_name }}
                            </h5>
                            <small class="text-muted">{{ $queue->created_at->diffForHumans() }}</small>
                        </div>
                        <p class="mb-1">
                            <strong>ID:</strong> {{ $queue->patient->patient_unique_id }}
                            @if($queue->room_number)
                            | <strong>Room:</strong> <span class="badge bg-info">{{ $queue->room_number }}</span>
                            @endif
                            @if($queue->has_consultation_attachment && $queue->latestConsultation)
                            | <span class="badge bg-success" title="Has consultation form">
                                <i class="fas fa-file-medical"></i> Form Available
                            </span>
                            @endif
                        </p>
                        @if($queue->notes)
                        <p class="mb-2"><strong>Notes:</strong> {{ $queue->notes }}</p>
                        @endif
                        @if($queue->has_consultation_attachment && $queue->latestConsultation)
                        <p class="mb-2">
                            <strong>Latest Consultation:</strong>
                            <small class="text-muted">{{ $queue->latestConsultation->created_at->format('M d, Y h:i A') }}</small>
                            <a href="{{ route('doctors.patient-queue.view-consultation', $queue) }}"
                                class="btn btn-sm btn-outline-primary ms-2">
                                <i class="fas fa-eye"></i> View Form
                            </a>
                        </p>
                        @endif
                        <div class="d-flex justify-content-between align-items-center">
                            <small class="text-muted">Added by: {{ $queue->addedBy->full_name }}</small>
                            <div class="btn-group">
                                @if($queue->has_consultation_attachment && $queue->latestConsultation)
                                <a href="{{ route('doctors.patient-queue.view-consultation', $queue) }}"
                                    class="btn btn-sm btn-primary"
                                    title="View Consultation Form">
                                    <i class="fas fa-file-medical"></i>
                                </a>
                                @endif
                                @if(!$inProgressQueue && $priorityQueues->count() === 0)
                                <form action="{{ route('doctors.patient-queue.call-next', $queue) }}" method="POST" class="d-inline">
                                    @csrf
                                    <input type="hidden" name="queue_id" value="{{ $queue->id }}">
                                    <button type="submit" class="btn btn-sm btn-success">
                                        <i class="fas fa-phone"></i> Call Next
                                    </button>
                                </form>
                                @endif
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
                @else
                <div class="text-center py-4">
                    <i class="fas fa-check-circle fa-3x text-success mb-3"></i>
                    <p class="text-muted">No patients waiting in regular queue</p>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Queue Statistics -->
<div class="row">
    <div class="col-md-12">
        <div class="card shadow">
            <div class="card-header bg-light">
                <h5 class="mb-0"><i class="fas fa-chart-bar"></i> Queue Statistics</h5>
            </div>
            <div class="card-body">
                <div class="row text-center">
                    <div class="col-md-3">
                        <div class="stat-box">
                            <h2 class="text-primary mb-0">{{ $queues->where('status', 'waiting')->count() }}</h2>
                            <p class="text-muted mb-0">Total Waiting</p>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stat-box">
                            <h2 class="text-danger mb-0">{{ $queues->where('is_priority', true)->where('status', 'waiting')->count() }}</h2>
                            <p class="text-muted mb-0">Priority Waiting</p>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stat-box">
                            <h2 class="text-warning mb-0">{{ $queues->where('status', 'in_progress')->count() }}</h2>
                            <p class="text-muted mb-0">In Progress</p>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stat-box">
                            <h2 class="text-info mb-0">{{ $avgWaitTime ? round($avgWaitTime) : 0 }} min</h2>
                            <p class="text-muted mb-0">Avg. Wait Time</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>