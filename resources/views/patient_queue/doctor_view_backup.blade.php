@extends('layouts.app')
@section('title')
{{ __('Patient Queue - Doctor View') }}
@endsection
@section('content')
<div class="container-fluid">
    @include('flash::message')

    <div class="d-flex justify-content-between align-items-end mb-5">
        <h1><i class="fas fa-stethoscope"></i> @yield('title')</h1>
        <button class="btn btn-info" onclick="location.reload()">
            <i class="fas fa-sync"></i> Refresh Queue
        </button>
    </div>

    <!-- Current Patient in Progress -->
    @php
    $inProgressQueue = $queues->where('status', 'in_progress')->first();
    @endphp
    @if($inProgressQueue)
    <div class="alert alert-warning border-warning" role="alert">
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
                <form action="{{ route('doctors.patient-queue.complete', $inProgressQueue) }}" method="POST" class="d-inline">
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
                    @php
                    $priorityQueues = $queues->where('is_priority', true)->where('status', 'waiting');
                    @endphp
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
                            </p>
                            @if($queue->notes)
                            <p class="mb-2"><strong>Notes:</strong> {{ $queue->notes }}</p>
                            @endif
                            <div class="d-flex justify-content-between align-items-center">
                                <small class="text-muted">Added by: {{ $queue->addedBy->full_name }}</small>
                                @if(!$inProgressQueue)
                                <form action="{{ route('doctors.patient-queue.call-next', $queue) }}" method="POST">
                                    @csrf
                                    <input type="hidden" name="queue_id" value="{{ $queue->id }}">
                                    <button type="submit" class="btn btn-sm btn-success">
                                        <i class="fas fa-phone"></i> Call Next
                                    </button>
                                </form>
                                @endif
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
                    @php
                    $regularQueues = $queues->where('is_priority', false)->where('status', 'waiting');
                    @endphp
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
                            </p>
                            @if($queue->notes)
                            <p class="mb-2"><strong>Notes:</strong> {{ $queue->notes }}</p>
                            @endif
                            <div class="d-flex justify-content-between align-items-center">
                                <small class="text-muted">Added by: {{ $queue->addedBy->full_name }}</small>
                                @if(!$inProgressQueue && $priorityQueues->count() === 0)
                                <form action="{{ route('doctors.patient-queue.call-next', $queue) }}" method="POST">
                                    @csrf
                                    <input type="hidden" name="queue_id" value="{{ $queue->id }}">
                                    <button type="submit" class="btn btn-sm btn-success">
                                        <i class="fas fa-phone"></i> Call Next
                                    </button>
                                </form>
                                @endif
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
                                @php
                                $avgWaitTime = $queues->where('status', 'waiting')->avg(function($q) {
                                return $q->created_at->diffInMinutes(now());
                                });
                                @endphp
                                <h2 class="text-info mb-0">{{ $avgWaitTime ? round($avgWaitTime) : 0 }} min</h2>
                                <p class="text-muted mb-0">Avg. Wait Time</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('styles')
<style>
    .stat-box {
        padding: 1rem;
        border-radius: 0.5rem;
        background-color: #f8f9fa;
    }

    .list-group-item {
        transition: all 0.3s ease;
    }

    .list-group-item:hover {
        transform: translateX(5px);
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    }
</style>
@endsection

@section('scripts')
<script>
    let lastUpdateTime = Date.now();
    let isUpdating = false;
    
    // Real-time queue updates using AJAX polling
    function updateQueue() {
        if (isUpdating) return;
        
        isUpdating = true;
        
        console.log('[Queue Update] Fetching queue data...');
        
        fetch('/api/patient-queue/data', {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            credentials: 'same-origin'
        })
        .then(response => {
            console.log('[Queue Update] Response status:', response.status);
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            console.log('[Queue Update] Received data:', data);
            
            // Update queue counts in statistics
            updateStatistics(data);
            
            // Check if there are any changes
            if (hasQueueChanged(data)) {
                console.log('[Queue Update] Changes detected! Showing notification...');
                
                // Show notification
                showNotification(data);
                
                // Reload page to show updated queue
                setTimeout(() => {
                    console.log('[Queue Update] Reloading page...');
                    location.reload();
                }, 1000);
            } else {
                console.log('[Queue Update] No changes detected');
            }
            
            lastUpdateTime = Date.now();
            isUpdating = false;
        })
        .catch(error => {
            console.error('[Queue Update] Error fetching queue data:', error);
            isUpdating = false;
        });
    }
    
    // Store initial queue state - properly encode from PHP
    const initialQueueData = {
        total: parseInt('{{ $queues->where("status", "waiting")->count() }}'),
        priority: parseInt('{{ $queues->where("is_priority", true)->where("status", "waiting")->count() }}'),
        inProgress: parseInt('{{ $queues->where("status", "in_progress")->count() }}')
    };
    
    let currentQueueState = { ...initialQueueData };
    
    console.log('[Queue Init] Initial state:', currentQueueState);
    
    function hasQueueChanged(data) {
        const changed = data.total !== currentQueueState.total ||
               data.priority_count !== currentQueueState.priority ||
               data.in_progress_count !== currentQueueState.inProgress;
        
        console.log('[Queue Compare] Current:', currentQueueState, 'New:', {
            total: data.total,
            priority: data.priority_count,
            inProgress: data.in_progress_count
        }, 'Changed:', changed);
        
        // Update current state for next comparison
        if (changed) {
            currentQueueState = {
                total: data.total,
                priority: data.priority_count,
                inProgress: data.in_progress_count
            };
        }
        
        return changed;
    }
    
    function updateStatistics(data) {
        // Update statistics display (optional, for visual feedback without reload)
        const totalEl = document.querySelector('.text-primary.mb-0');
        const priorityEl = document.querySelector('.text-danger.mb-0');
        const inProgressEl = document.querySelector('.text-warning.mb-0');
        
        if (totalEl && totalEl.textContent != data.total) {
            totalEl.textContent = data.total;
            totalEl.closest('.stat-box').classList.add('pulse-animation');
            setTimeout(() => totalEl.closest('.stat-box').classList.remove('pulse-animation'), 1000);
        }
    }
    
    function showNotification(data) {
        // Play notification sound (optional)
        playNotificationSound();
        
        // Show browser notification if permitted
        if ('Notification' in window && Notification.permission === 'granted') {
            new Notification('Patient Queue Update', {
                body: `Queue updated! ${data.total} patients waiting (${data.priority_count} priority)`,
                icon: '/assets/img/logo.png',
                badge: '/assets/img/logo.png'
            });
        }
        
        // Show toast notification
        const toast = document.createElement('div');
        toast.className = 'alert alert-info alert-dismissible fade show position-fixed top-0 end-0 m-3';
        toast.style.zIndex = '9999';
        toast.innerHTML = `
            <strong><i class="fas fa-bell"></i> Queue Updated!</strong><br>
            ${data.total} patients waiting (${data.priority_count} priority)
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;
        document.body.appendChild(toast);
        
        setTimeout(() => {
            toast.remove();
        }, 5000);
    }
    
    function playNotificationSound() {
        // Create and play a subtle notification sound
        const audioContext = new (window.AudioContext || window.webkitAudioContext)();
        const oscillator = audioContext.createOscillator();
        const gainNode = audioContext.createGain();
        
        oscillator.connect(gainNode);
        gainNode.connect(audioContext.destination);
        
        oscillator.frequency.value = 800;
        oscillator.type = 'sine';
        
        gainNode.gain.setValueAtTime(0.3, audioContext.currentTime);
        gainNode.gain.exponentialRampToValueAtTime(0.01, audioContext.currentTime + 0.5);
        
        oscillator.start(audioContext.currentTime);
        oscillator.stop(audioContext.currentTime + 0.5);
    }
    
    // Request notification permission on page load
    if ('Notification' in window && Notification.permission === 'default') {
        Notification.requestPermission();
    }
    
    // Poll for updates every 5 seconds
    setInterval(updateQueue, 5000);
    
    // Also update when page becomes visible again
    document.addEventListener('visibilitychange', function() {
        if (!document.hidden) {
            updateQueue();
        }
    });
    
    // Manual refresh button
    document.querySelector('.btn-info').addEventListener('click', function(e) {
        e.preventDefault();
        updateQueue();
        setTimeout(() => location.reload(), 500);
    });
</script>

<style>
    @keyframes pulse {
        0% { transform: scale(1); }
        50% { transform: scale(1.05); }
        100% { transform: scale(1); }
    }
    
    .pulse-animation {
        animation: pulse 0.5s ease-in-out;
    }
    
    .list-group-item {
        transition: all 0.3s ease;
    }
    
    .list-group-item:hover {
        transform: translateX(5px);
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    }
    
    /* New patient indicator */
    .new-patient-indicator {
        animation: blink 1s linear infinite;
    }
    
    @keyframes blink {
        0%, 50%, 100% { opacity: 1; }
        25%, 75% { opacity: 0.5; }
    }
</style>
@endsection