@php
$priorityQueues = $queues->where('is_priority', true);
$regularQueues = $queues->where('is_priority', false);
// $todayPatients is passed from the controller (PatientQueueController::index / indexPartial)
@endphp

<div class="row">
    <!-- Priority Queue -->
    <div class="col-md-6 mb-4">
        <div class="card border-danger">
            <div class="card-header bg-danger text-white">
                <h4 class="mb-0"><i class="fas fa-exclamation-triangle"></i> Priority Queue</h4>
            </div>
            <div class="card-body">
                @if($priorityQueues->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Patient Name</th>
                                <th>Room</th>
                                <th>Status</th>
                                <th>Time</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($priorityQueues as $index => $queue)
                            <tr class="{{ $queue->status === 'in_progress' ? 'table-warning' : '' }}">
                                <td><span class="badge bg-danger">{{ $index + 1 }}</span></td>
                                <td>
                                    <strong>{{ $queue->patient->user->full_name }}</strong><br>
                                    <small class="text-muted">{{ $queue->patient->user->university_id_number ?? $queue->patient->patient_unique_id }}</small>
                                    @if($queue->has_consultation_attachment)
                                    <br><span class="badge badge-sm bg-success mt-1">
                                        <i class="fas fa-file-medical"></i> Has Form
                                    </span>
                                    @endif
                                </td>
                                <td>
                                    @if($queue->room_number)
                                    <span class="badge bg-info">{{ $queue->room_number }}</span>
                                    @else
                                    <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    @if($queue->status === 'waiting')
                                    <span class="badge bg-secondary">Waiting</span>
                                    @elseif($queue->status === 'in_progress')
                                    <span class="badge bg-warning">In Progress</span>
                                    @endif
                                </td>
                                <td>
                                    <small>{{ $queue->created_at->diffForHumans() }}</small>
                                </td>
                                <td>
                                    <div class="btn-group" role="group">
                                        @if($queue->status === 'waiting')
                                        <form action="{{ getRouteByRole('patient-queue.call-next', ['patientQueue' => $queue]) }}" method="POST" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-success" title="Call Patient">
                                                <i class="fas fa-phone"></i>
                                            </button>
                                        </form>
                                        @endif

                                        @if($queue->status === 'in_progress')
                                        <form action="{{ getRouteByRole('patient-queue.complete', ['patientQueue' => $queue]) }}" method="POST" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-primary" title="Complete">
                                                <i class="fas fa-check"></i>
                                            </button>
                                        </form>
                                        @endif

                                        <a href="{{ getRouteByRole('patient-queue.edit', ['patientQueue' => $queue]) }}" class="btn btn-sm btn-warning" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>

                                        <form action="{{ getRouteByRole('patient-queue.destroy', ['patientQueue' => $queue]) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger" title="Remove">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @else
                <p class="text-center text-muted">No priority patients in queue</p>
                @endif
            </div>
        </div>
    </div>

    <!-- Regular Queue -->
    <div class="col-md-6 mb-4">
        <div class="card border-primary">
            <div class="card-header bg-primary text-white">
                <h4 class="mb-0"><i class="fas fa-users"></i> Regular Queue</h4>
            </div>
            <div class="card-body">
                @if($regularQueues->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Patient Name</th>
                                <th>Room</th>
                                <th>Status</th>
                                <th>Time</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($regularQueues as $index => $queue)
                            <tr class="{{ $queue->status === 'in_progress' ? 'table-warning' : '' }}">
                                <td><span class="badge bg-primary">{{ $index + 1 }}</span></td>
                                <td>
                                    <strong>{{ $queue->patient->user->full_name }}</strong><br>
                                    <small class="text-muted">{{ $queue->patient->user->university_id_number ?? $queue->patient->patient_unique_id }}</small>
                                    @if($queue->has_consultation_attachment)
                                    <br><span class="badge badge-sm bg-success mt-1">
                                        <i class="fas fa-file-medical"></i> Has Form
                                    </span>
                                    @endif
                                </td>
                                <td>
                                    @if($queue->room_number)
                                    <span class="badge bg-info">{{ $queue->room_number }}</span>
                                    @else
                                    <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    @if($queue->status === 'waiting')
                                    <span class="badge bg-secondary">Waiting</span>
                                    @elseif($queue->status === 'in_progress')
                                    <span class="badge bg-warning">In Progress</span>
                                    @endif
                                </td>
                                <td>
                                    <small>{{ $queue->created_at->diffForHumans() }}</small>
                                </td>
                                <td>
                                    <div class="btn-group" role="group">
                                        @if($queue->status === 'waiting')
                                        <form action="{{ getRouteByRole('patient-queue.call-next', ['patientQueue' => $queue]) }}" method="POST" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-success" title="Call Patient">
                                                <i class="fas fa-phone"></i>
                                            </button>
                                        </form>
                                        @endif

                                        @if($queue->status === 'in_progress')
                                        <form action="{{ getRouteByRole('patient-queue.complete', ['patientQueue' => $queue]) }}" method="POST" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-primary" title="Complete">
                                                <i class="fas fa-check"></i>
                                            </button>
                                        </form>
                                        @endif

                                        <a href="{{ getRouteByRole('patient-queue.edit', ['patientQueue' => $queue]) }}" class="btn btn-sm btn-warning" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>

                                        <form action="{{ getRouteByRole('patient-queue.destroy', ['patientQueue' => $queue]) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger" title="Remove">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @else
                <p class="text-center text-muted">No patients in regular queue</p>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Queue Summary -->
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-body">
                <div class="row text-center">
                    <div class="col-md-3">
                        <h3 class="text-primary">{{ $queues->count() }}</h3>
                        <p class="text-muted">Total in Queue</p>
                    </div>
                    <div class="col-md-3">
                        <h3 class="text-danger">{{ $queues->where('is_priority', true)->count() }}</h3>
                        <p class="text-muted">Priority</p>
                    </div>
                    <div class="col-md-3">
                        <h3 class="text-warning">{{ $queues->where('status', 'in_progress')->count() }}</h3>
                        <p class="text-muted">In Progress</p>
                    </div>
                    <div class="col-md-3">
                        <h3 class="text-secondary">{{ $queues->where('status', 'waiting')->count() }}</h3>
                        <p class="text-muted">Waiting</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- All Patients Today -->
<div class="row mt-4">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header bg-light">
                <h4 class="mb-0"><i class="fas fa-history"></i> All Patients Today</h4>
            </div>
            <div class="card-body">
                @if($todayPatients->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover table-striped">
                        <thead>
                            <tr>
                                <th>Time Added</th>
                                <th>Patient Name</th>
                                <th>Patient ID</th>
                                <th>Room</th>
                                <th>Priority</th>
                                <th>Status</th>
                                <th>Called At</th>
                                <th>Completed At</th>
                                <th>Added By</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($todayPatients as $patient)
                            <tr>
                                <td><small>{{ $patient->created_at->format('h:i A') }}</small></td>
                                <td><strong>{{ $patient->patient->user->full_name }}</strong></td>
                                <td><small class="text-muted">{{ $patient->patient->user->university_id_number ?? $patient->patient->patient_unique_id }}</small></td>
                                <td>
                                    @if($patient->room_number)
                                    <span class="badge bg-info">{{ $patient->room_number }}</span>
                                    @else
                                    <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    @if($patient->is_priority)
                                    <span class="badge bg-danger"><i class="fas fa-exclamation-triangle"></i> Priority</span>
                                    @else
                                    <span class="text-muted">Regular</span>
                                    @endif
                                </td>
                                <td>
                                    @if($patient->status === 'waiting')
                                    <span class="badge bg-secondary">Waiting</span>
                                    @elseif($patient->status === 'in_progress')
                                    <span class="badge bg-warning">In Progress</span>
                                    @elseif($patient->status === 'completed')
                                    <span class="badge bg-success">Completed</span>
                                    @elseif($patient->status === 'cancelled')
                                    <span class="badge bg-dark">Cancelled</span>
                                    @endif
                                </td>
                                <td>
                                    @if($patient->called_at)
                                    <small>{{ \Carbon\Carbon::parse($patient->called_at)->format('h:i A') }}</small>
                                    @else
                                    <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    @if($patient->completed_at)
                                    <small>{{ \Carbon\Carbon::parse($patient->completed_at)->format('h:i A') }}</small>
                                    @else
                                    <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td><small>{{ $patient->addedBy->full_name ?? 'N/A' }}</small></td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Today's Statistics -->
                <div class="row mt-4 pt-3 border-top">
                    <div class="col-md-12">
                        <h5 class="mb-3">Today's Statistics</h5>
                    </div>
                    <div class="col-md-2">
                        <div class="text-center">
                            <h4 class="text-info">{{ $todayPatients->count() }}</h4>
                            <small class="text-muted">Total Today</small>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="text-center">
                            <h4 class="text-success">{{ $todayPatients->where('status', 'completed')->count() }}</h4>
                            <small class="text-muted">Completed</small>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="text-center">
                            <h4 class="text-warning">{{ $todayPatients->where('status', 'in_progress')->count() }}</h4>
                            <small class="text-muted">In Progress</small>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="text-center">
                            <h4 class="text-secondary">{{ $todayPatients->where('status', 'waiting')->count() }}</h4>
                            <small class="text-muted">Waiting</small>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="text-center">
                            <h4 class="text-dark">{{ $todayPatients->where('status', 'cancelled')->count() }}</h4>
                            <small class="text-muted">Cancelled</small>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="text-center">
                            <h4 class="text-danger">{{ $todayPatients->where('is_priority', true)->count() }}</h4>
                            <small class="text-muted">Priority Cases</small>
                        </div>
                    </div>
                </div>
                @else
                <div class="alert alert-info no-auto-hide text-center mb-0">
                    <i class="fas fa-info-circle"></i> No patients have been added to the queue today.
                </div>
                @endif
            </div>
        </div>
    </div>
</div>