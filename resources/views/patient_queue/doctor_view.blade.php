@extends('layouts.app')
@section('title')
{{ __('Patient Queue - Doctor View') }}
@endsection
@section('content')
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

    .gap-2 {
        gap: 0.5rem;
    }

    #refresh-icon.spinning {
        animation: spin 1s linear;
    }

    @keyframes spin {
        from {
            transform: rotate(0deg);
        }

        to {
            transform: rotate(360deg);
        }
    }

    #countdown-badge {
        font-size: 0.9rem;
        padding: 0.5rem 0.75rem;
        transition: background-color 0.3s ease, transform 0.3s ease;
    }

    #countdown-badge.pulse {
        animation: pulse 1s ease-in-out;
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

    /* Color states for countdown */
    #countdown-badge.countdown-5 {
        background-color: #28a745 !important;
        /* Green - Just started */
    }

    #countdown-badge.countdown-4 {
        background-color: #20c997 !important;
        /* Teal */
    }

    #countdown-badge.countdown-3 {
        background-color: #ffc107 !important;
        /* Yellow - Halfway */
        color: #000 !important;
    }

    #countdown-badge.countdown-2 {
        background-color: #fd7e14 !important;
        /* Orange - Getting close */
        color: #fff !important;
    }

    #countdown-badge.countdown-1 {
        background-color: #dc3545 !important;
        /* Red - About to refresh */
        color: #fff !important;
        animation: pulse 0.5s ease-in-out;
    }
</style>
<div class="container-fluid">
    @include('flash::message')

    <div class="d-flex justify-content-between align-items-end mb-5">
        <h1><i class="fas fa-stethoscope"></i> @yield('title')</h1>
        <div class="d-flex gap-2">
            <button class="btn btn-info" id="manual-refresh-btn" onclick="window.manualQueueRefresh()">
                <i class="fas fa-sync" id="refresh-icon"></i> Refresh Queue
            </button>
            <button class="btn btn-outline-primary" id="toggle-auto-refresh" onclick="window.toggleQueueAutoRefresh()">
                <i class="fas fa-play"></i> <span id="toggle-text">Auto-Refresh OFF</span>
            </button>
            <span class="badge d-none d-flex align-items-center countdown-5" id="countdown-badge">
                <i class="fas fa-clock me-1"></i> <span id="countdown">5</span>s
            </span>
        </div>
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

<script>
    // Define functions IMMEDIATELY - inline in content section
    // Global state
    window.queueAutoRefresh = {
        enabled: localStorage.getItem('queueAutoRefresh') === 'true',
        countdownInterval: null,
        refreshTimeout: null,
        countdownSeconds: 5
    };

    // Manual refresh function - defined immediately
    window.manualQueueRefresh = function() {
        const state = window.queueAutoRefresh;
        const icon = document.getElementById('refresh-icon');
        if (icon) icon.classList.add('spinning');

        if (state.enabled) {
            if (state.countdownInterval) clearInterval(state.countdownInterval);
            if (state.refreshTimeout) clearTimeout(state.refreshTimeout);
        }

        setTimeout(function() {
            location.reload();
        }, 300);
    };

    // Toggle auto-refresh function - defined immediately
    window.toggleQueueAutoRefresh = function() {
        const state = window.queueAutoRefresh;
        state.enabled = !state.enabled;
        localStorage.setItem('queueAutoRefresh', state.enabled);

        const toggleBtn = document.getElementById('toggle-auto-refresh');
        const toggleText = document.getElementById('toggle-text');
        const icon = toggleBtn ? toggleBtn.querySelector('i') : null;

        if (state.enabled) {
            if (toggleBtn) {
                toggleBtn.classList.remove('btn-outline-primary');
                toggleBtn.classList.add('btn-outline-success');
            }
            if (icon) {
                icon.classList.remove('fa-play');
                icon.classList.add('fa-pause');
            }
            if (toggleText) toggleText.textContent = 'Auto-Refresh ON';
            window.startQueueCountdown();
        } else {
            if (toggleBtn) {
                toggleBtn.classList.remove('btn-outline-success');
                toggleBtn.classList.add('btn-outline-primary');
            }
            if (icon) {
                icon.classList.remove('fa-pause');
                icon.classList.add('fa-play');
            }
            if (toggleText) toggleText.textContent = 'Auto-Refresh OFF';

            if (state.countdownInterval) clearInterval(state.countdownInterval);
            if (state.refreshTimeout) clearTimeout(state.refreshTimeout);

            updateCountdown();
        }
    };

    // Start countdown function - defined immediately
    window.startQueueCountdown = function() {
        const state = window.queueAutoRefresh;
        if (!state.enabled) return;

        state.countdownSeconds = 5;
        updateCountdown();

        if (state.countdownInterval) clearInterval(state.countdownInterval);
        if (state.refreshTimeout) clearTimeout(state.refreshTimeout);

        state.countdownInterval = setInterval(function() {
            state.countdownSeconds--;
            updateCountdown();
            if (state.countdownSeconds <= 0) {
                clearInterval(state.countdownInterval);
            }
        }, 1000);

        state.refreshTimeout = setTimeout(function() {
            if (state.enabled) {
                location.reload();
            }
        }, 5000);
    };

    // Update countdown helper function
    function updateCountdown() {
        const state = window.queueAutoRefresh;
        const badge = document.getElementById('countdown-badge');
        const countdownEl = document.getElementById('countdown');

        if (!badge || !countdownEl) return;

        countdownEl.textContent = state.countdownSeconds;

        if (state.enabled) {
            badge.classList.remove('d-none');
            badge.classList.remove('countdown-5', 'countdown-4', 'countdown-3', 'countdown-2', 'countdown-1');

            if (state.countdownSeconds >= 1 && state.countdownSeconds <= 5) {
                badge.classList.add('countdown-' + state.countdownSeconds);
            }

            badge.classList.add('pulse');
            setTimeout(function() {
                badge.classList.remove('pulse');
            }, 300);
        } else {
            badge.classList.add('d-none');
        }
    }

    // Initialize when page loads
    (function() {
        'use strict';

        function initQueuePage() {
            const state = window.queueAutoRefresh;
            const toggleBtn = document.getElementById('toggle-auto-refresh');
            const toggleText = document.getElementById('toggle-text');
            const icon = toggleBtn ? toggleBtn.querySelector('i') : null;
            const countdownBadge = document.getElementById('countdown-badge');

            if (!toggleBtn) return;

            // Initialize UI based on saved state
            if (state.enabled) {
                toggleBtn.classList.add('btn-outline-success');
                toggleBtn.classList.remove('btn-outline-primary');
                if (icon) {
                    icon.classList.add('fa-pause');
                    icon.classList.remove('fa-play');
                }
                if (toggleText) toggleText.textContent = 'Auto-Refresh ON';
                if (countdownBadge) countdownBadge.classList.remove('d-none');
                window.startQueueCountdown();
            } else {
                toggleBtn.classList.add('btn-outline-primary');
                toggleBtn.classList.remove('btn-outline-success');
                if (icon) {
                    icon.classList.add('fa-play');
                    icon.classList.remove('fa-pause');
                }
                if (toggleText) toggleText.textContent = 'Auto-Refresh OFF';
                if (countdownBadge) countdownBadge.classList.add('d-none');
            }
        }

        // Initialize when DOM is ready
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', initQueuePage);
        } else {
            initQueuePage();
        }
    })();
</script>
@endsection