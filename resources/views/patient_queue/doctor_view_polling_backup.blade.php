@extends('layouts.app')
@section('title')
{{ __('Patient Queue - Doctor View') }}
@endsection
@section('content')
<div class="container-fluid">
    @include('flash::message')

    <div class="d-flex justify-content-between align-items-end mb-4">
        <h1><i class="fas fa-stethoscope"></i> @yield('title')</h1>
        <div>
            <span id="websocket-status" class="badge bg-secondary me-2">
                <i class="fas fa-circle-notch fa-spin"></i> Initializing...
            </span>
            <button class="btn btn-info" onclick="location.reload()">
                <i class="fas fa-sync"></i> Refresh
            </button>
        </div>
    </div>

    <!-- Patients In Progress Section -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-warning shadow">
                <div class="card-header bg-warning text-dark">
                    <h4 class="mb-0">
                        <i class="fas fa-user-md"></i> Patients In Progress
                        <span id="in-progress-count" class="badge bg-dark ms-2">{{ $queues->where('status', 'in_progress')->count() }}</span>
                    </h4>
                </div>
                <div class="card-body" id="in-progress-list">
                    @php
                    $inProgressQueues = $queues->where('status', 'in_progress');
                    @endphp
                    @if($inProgressQueues->count() > 0)
                    <div class="row">
                        @foreach($inProgressQueues as $queue)
                        <div class="col-md-6 mb-3" data-queue-id="{{ $queue->id }}">
                            <div class="card border-warning h-100">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <div>
                                            <h5 class="mb-1">
                                                <i class="fas fa-user-clock text-warning"></i>
                                                {{ $queue->patient->user->full_name }}
                                            </h5>
                                            <p class="mb-1">
                                                <strong>Patient ID:</strong> {{ $queue->patient->user->university_id_number ?? $queue->patient->patient_unique_id }}
                                                @if($queue->room_number)
                                                | <strong>Room:</strong> <span class="badge bg-info">{{ $queue->room_number }}</span>
                                                @endif
                                            </p>
                                            <small class="text-muted">
                                                <i class="fas fa-clock"></i> Called: {{ $queue->called_at->format('h:i A') }}
                                                ({{ $queue->called_at->diffForHumans() }})
                                            </small>
                                        </div>
                                        @if($queue->is_priority)
                                        <span class="badge bg-danger">PRIORITY</span>
                                        @endif
                                    </div>
                                    @if($queue->notes)
                                    <div class="alert alert-light mb-2 py-2">
                                        <small><strong>Notes:</strong> {{ $queue->notes }}</small>
                                    </div>
                                    @endif
                                    <div class="d-flex justify-content-between align-items-center">
                                        <small class="text-muted">
                                            <i class="fas fa-user"></i> {{ $queue->addedBy->full_name }}
                                        </small>
                                        <form action="{{ route('doctors.patient-queue.complete', $queue) }}" method="POST" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-success btn-sm">
                                                <i class="fas fa-check-circle"></i> Complete
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    @else
                    <div class="text-center py-4 empty-state">
                        <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                        <p class="text-muted mb-0">No patients currently in consultation</p>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Waiting Queue Section -->
    <div class="row">
        <!-- Priority Queue -->
        <div class="col-md-6 mb-4">
            <div class="card border-danger shadow h-100">
                <div class="card-header bg-danger text-white">
                    <h4 class="mb-0">
                        <i class="fas fa-exclamation-triangle"></i> Priority Patients
                        <span id="priority-count" class="badge bg-dark ms-2">{{ $queues->where('is_priority', true)->where('status', 'waiting')->count() }}</span>
                    </h4>
                </div>
                <div class="card-body" id="priority-queue-list">
                    @php
                    $priorityQueues = $queues->where('is_priority', true)->where('status', 'waiting');
                    @endphp
                    @if($priorityQueues->count() > 0)
                    <div class="list-group">
                        @foreach($priorityQueues as $index => $queue)
                        <div class="list-group-item list-group-item-action queue-item" data-queue-id="{{ $queue->id }}">
                            <div class="d-flex w-100 justify-content-between">
                                <h5 class="mb-1">
                                    <span class="badge bg-danger me-2">{{ $index + 1 }}</span>
                                    {{ $queue->patient->user->full_name }}
                                </h5>
                                <small class="text-muted">{{ $queue->created_at->diffForHumans() }}</small>
                            </div>
                            <p class="mb-1">
                                <strong>ID:</strong> {{ $queue->patient->user->university_id_number ?? $queue->patient->patient_unique_id }}
                                @if($queue->room_number)
                                | <strong>Room:</strong> <span class="badge bg-info">{{ $queue->room_number }}</span>
                                @endif
                            </p>
                            @if($queue->notes)
                            <p class="mb-2"><small><strong>Notes:</strong> {{ $queue->notes }}</small></p>
                            @endif
                            <div class="d-flex justify-content-between align-items-center">
                                <small class="text-muted">
                                    <i class="fas fa-user"></i> {{ $queue->addedBy->full_name }}
                                </small>
                                <form action="{{ route('doctors.patient-queue.call-next', $queue) }}" method="POST">
                                    @csrf
                                    <input type="hidden" name="queue_id" value="{{ $queue->id }}">
                                    <button type="submit" class="btn btn-sm btn-success">
                                        <i class="fas fa-phone"></i> Call Next
                                    </button>
                                </form>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    @else
                    <div class="text-center py-4 empty-state">
                        <i class="fas fa-check-circle fa-3x text-success mb-3"></i>
                        <p class="text-muted mb-0">No priority patients waiting</p>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Regular Queue -->
        <div class="col-md-6 mb-4">
            <div class="card border-primary shadow h-100">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0">
                        <i class="fas fa-users"></i> Regular Queue
                        <span id="regular-count" class="badge bg-dark ms-2">{{ $queues->where('is_priority', false)->where('status', 'waiting')->count() }}</span>
                    </h4>
                </div>
                <div class="card-body" id="regular-queue-list">
                    @php
                    $regularQueues = $queues->where('is_priority', false)->where('status', 'waiting');
                    @endphp
                    @if($regularQueues->count() > 0)
                    <div class="list-group">
                        @foreach($regularQueues as $index => $queue)
                        <div class="list-group-item list-group-item-action queue-item" data-queue-id="{{ $queue->id }}">
                            <div class="d-flex w-100 justify-content-between">
                                <h5 class="mb-1">
                                    <span class="badge bg-primary me-2">{{ $index + 1 }}</span>
                                    {{ $queue->patient->user->full_name }}
                                </h5>
                                <small class="text-muted">{{ $queue->created_at->diffForHumans() }}</small>
                            </div>
                            <p class="mb-1">
                                <strong>ID:</strong> {{ $queue->patient->user->university_id_number ?? $queue->patient->patient_unique_id }}
                                @if($queue->room_number)
                                | <strong>Room:</strong> <span class="badge bg-info">{{ $queue->room_number }}</span>
                                @endif
                            </p>
                            @if($queue->notes)
                            <p class="mb-2"><small><strong>Notes:</strong> {{ $queue->notes }}</small></p>
                            @endif
                            <div class="d-flex justify-content-between align-items-center">
                                <small class="text-muted">
                                    <i class="fas fa-user"></i> {{ $queue->addedBy->full_name }}
                                </small>
                                <form action="{{ route('doctors.patient-queue.call-next', $queue) }}" method="POST">
                                    @csrf
                                    <input type="hidden" name="queue_id" value="{{ $queue->id }}">
                                    <button type="submit" class="btn btn-sm btn-success">
                                        <i class="fas fa-phone"></i> Call Next
                                    </button>
                                </form>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    @else
                    <div class="text-center py-4 empty-state">
                        <i class="fas fa-check-circle fa-3x text-success mb-3"></i>
                        <p class="text-muted mb-0">No patients waiting in regular queue</p>
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
                    <h5 class="mb-0"><i class="fas fa-chart-bar"></i> Queue Statistics (Live)</h5>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-md-3">
                            <div class="stat-box">
                                <h2 class="text-primary mb-0" id="stat-total-waiting">{{ $queues->where('status', 'waiting')->count() }}</h2>
                                <p class="text-muted mb-0">Total Waiting</p>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="stat-box">
                                <h2 class="text-danger mb-0" id="stat-priority">{{ $queues->where('is_priority', true)->where('status', 'waiting')->count() }}</h2>
                                <p class="text-muted mb-0">Priority Waiting</p>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="stat-box">
                                <h2 class="text-warning mb-0" id="stat-in-progress">{{ $queues->where('status', 'in_progress')->count() }}</h2>
                                <p class="text-muted mb-0">In Progress</p>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="stat-box">
                                @php
                                $avgWaitTime = $queues->where('status', 'waiting')->avg(function($q) {
                                return $q->created_at->diffInMinutes(now());
                                });
                                @endphp
                                <h2 class="text-info mb-0" id="stat-avg-wait">{{ $avgWaitTime ? round($avgWaitTime) : 0 }} min</h2>
                                <p class="text-muted mb-0">Avg. Wait Time</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Notification Toast Container -->
<div id="toast-container" class="position-fixed top-0 end-0 p-3" style="z-index: 9999;"></div>

@endsection

@section('styles')
<style>
    .stat-box {
        padding: 1.5rem;
        border-radius: 0.5rem;
        background-color: #f8f9fa;
        transition: all 0.3s ease;
    }

    .stat-box:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    }

    .queue-item {
        transition: all 0.3s ease;
        border-left: 4px solid transparent;
    }

    .queue-item:hover {
        transform: translateX(5px);
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    }

    .queue-item.new-patient {
        animation: slideIn 0.5s ease-out, highlight 2s ease-out;
        border-left-color: #28a745 !important;
    }

    @keyframes slideIn {
        from {
            opacity: 0;
            transform: translateX(-20px);
        }

        to {
            opacity: 1;
            transform: translateX(0);
        }
    }

    @keyframes highlight {

        0%,
        100% {
            background-color: transparent;
        }

        50% {
            background-color: #d4edda;
        }
    }

    @keyframes pulse {

        0%,
        100% {
            transform: scale(1);
        }

        50% {
            transform: scale(1.1);
        }
    }

    .pulse-animation {
        animation: pulse 0.5s ease-in-out;
    }

    .empty-state {
        opacity: 0.6;
    }

    #websocket-status.connected {
        background-color: #28a745 !important;
    }

    #websocket-status.disconnected {
        background-color: #dc3545 !important;
    }

    .toast-notification {
        min-width: 300px;
        animation: slideInRight 0.3s ease-out;
    }

    @keyframes slideInRight {
        from {
            transform: translateX(100%);
            opacity: 0;
        }

        to {
            transform: translateX(0);
            opacity: 1;
        }
    }
</style>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        console.log('[Queue] Initializing real-time updates...');

        // Update status
        const statusBadge = document.getElementById('websocket-status');
        let isUpdating = false;
        let updateInterval;

        // Initialize as active
        statusBadge.innerHTML = '<i class="fas fa-check-circle"></i> Active';
        statusBadge.className = 'badge bg-success me-2 connected';

        // Store initial state
        let currentQueueState = {
            total: parseInt('{{ $queues->where("status", "waiting")->count() }}') || 0,
            priority: parseInt('{{ $queues->where("is_priority", true)->where("status", "waiting")->count() }}') || 0,
            inProgress: parseInt('{{ $queues->where("status", "in_progress")->count() }}') || 0
        };

        console.log('[Queue] Initial state:', currentQueueState);

        // Fetch queue updates
        function fetchQueueUpdates() {
            if (isUpdating) return;

            isUpdating = true;
            console.log('[Queue] Fetching updates...');

            fetch('/api/patient-queue/data', {
                    method: 'GET',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    credentials: 'same-origin'
                })
                .then(response => {
                    if (!response.ok) {
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }
                    return response.json();
                })
                .then(data => {
                    console.log('[Queue] Received data:', data);

                    // Check if there are changes
                    if (hasQueueChanged(data)) {
                        console.log('[Queue] Changes detected!');

                        // Show notification
                        showToastNotification(data);

                        // Play sound
                        playNotificationSound();

                        // Request browser notification permission
                        if (Notification.permission === 'default') {
                            Notification.requestPermission();
                        }

                        // Show browser notification
                        if (Notification.permission === 'granted') {
                            showBrowserNotification(data);
                        }

                        // Update UI dynamically or reload
                        updateQueueDisplay(data);
                    } else {
                        console.log('[Queue] No changes');
                    }

                    isUpdating = false;
                })
                .catch(error => {
                    console.error('[Queue] Error:', error);
                    isUpdating = false;
                });
        }

        // Check if queue has changed
        function hasQueueChanged(data) {
            const changed = data.total !== currentQueueState.total ||
                data.priority_count !== currentQueueState.priority ||
                data.in_progress_count !== currentQueueState.inProgress;

            console.log('[Queue] Compare - Current:', currentQueueState, 'New:', {
                total: data.total,
                priority: data.priority_count,
                inProgress: data.in_progress_count
            }, 'Changed:', changed);

            if (changed) {
                // Update current state
                currentQueueState = {
                    total: data.total,
                    priority: data.priority_count,
                    inProgress: data.in_progress_count
                };
            }

            return changed;
        }

        // Update queue display
        function updateQueueDisplay(data) {
            // Update statistics with animation
            updateStatElement('stat-total-waiting', data.total);
            updateStatElement('stat-priority', data.priority_count);
            updateStatElement('stat-in-progress', data.in_progress_count);

            // Update badge counters
            updateBadgeElement('priority-count', data.priority_count);
            updateBadgeElement('regular-count', data.total - data.priority_count);
            updateBadgeElement('in-progress-count', data.in_progress_count);

            // Reload page after a delay to show updated queue
            setTimeout(() => {
                console.log('[Queue] Reloading to show updated queue...');
                location.reload();
            }, 1500);
        }

        // Update stat element with animation
        function updateStatElement(id, value) {
            const element = document.getElementById(id);
            if (element && element.textContent != value) {
                element.textContent = value;
                element.closest('.stat-box').classList.add('pulse-animation');
                setTimeout(() => {
                    element.closest('.stat-box').classList.remove('pulse-animation');
                }, 500);
            }
        }

        // Update badge element
        function updateBadgeElement(id, value) {
            const element = document.getElementById(id);
            if (element) {
                element.textContent = value;
                element.classList.add('pulse-animation');
                setTimeout(() => {
                    element.classList.remove('pulse-animation');
                }, 500);
            }
        }

        // Listen for queue updates (polling)
        function startPolling() {
            // Poll every 3 seconds
            updateInterval = setInterval(fetchQueueUpdates, 3000);
            console.log('[Queue] Started polling (every 3 seconds)');
        }

        // Stop polling when tab is hidden
        document.addEventListener('visibilitychange', function() {
            if (document.hidden) {
                if (updateInterval) {
                    clearInterval(updateInterval);
                    console.log('[Queue] Stopped polling (tab hidden)');
                }
            } else {
                startPolling();
                fetchQueueUpdates(); // Immediate check when tab becomes visible
            }
        });

        // Show toast notification
        function showToastNotification(data) {
            const container = document.getElementById('toast-container');
            const toast = document.createElement('div');
            toast.className = 'toast toast-notification show';
            toast.setAttribute('role', 'alert');

            let message = `Queue Updated! ${data.total} waiting, ${data.priority_count} priority`;
            let bgClass = 'bg-success';

            toast.innerHTML = `
            <div class="toast-header ${bgClass} text-white">
                <i class="fas fa-bell me-2"></i>
                <strong class="me-auto">Queue Update</strong>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="toast"></button>
            </div>
            <div class="toast-body">
                ${message}
            </div>
        `;

            container.appendChild(toast);

            // Auto remove after 4 seconds
            setTimeout(() => {
                toast.classList.remove('show');
                setTimeout(() => toast.remove(), 300);
            }, 4000);
        }

        // Show browser notification
        function showBrowserNotification(data) {
            new Notification('Patient Queue Update', {
                body: `${data.total} patients waiting (${data.priority_count} priority)`,
                icon: '/assets/img/logo.png',
                badge: '/assets/img/logo.png'
            });
        }

        // Play notification sound
        function playNotificationSound() {
            try {
                const audioContext = new(window.AudioContext || window.webkitAudioContext)();
                const oscillator = audioContext.createOscillator();
                const gainNode = audioContext.createGain();

                oscillator.connect(gainNode);
                gainNode.connect(audioContext.destination);

                oscillator.frequency.value = 800;
                oscillator.type = 'sine';

                gainNode.gain.setValueAtTime(0.3, audioContext.currentTime);
                gainNode.gain.exponentialRampToValueAtTime(0.01, audioContext.currentTime + 0.3);

                oscillator.start(audioContext.currentTime);
                oscillator.stop(audioContext.currentTime + 0.3);
            } catch (error) {
                console.error('[Queue] Sound error:', error);
            }
        }

        // Request notification permission on page load
        if (Notification.permission === 'default') {
            Notification.requestPermission().then(permission => {
                console.log('[Queue] Notification permission:', permission);
            });
        }

        // Start polling
        startPolling();

        // Initial check after 1 second
        setTimeout(fetchQueueUpdates, 1000);
    });
</script>
@endsection