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

    <!-- Dynamic queue content (refreshed via AJAX) -->
    <div id="queue-dynamic-content">
        @include('patient_queue.doctor_view_partial')
    </div>
</div>

<script>
    const queueRefreshUrl = "{{ route('doctors.patient-queue.refresh') }}";

    // Global state
    window.queueAutoRefresh = {
        enabled: localStorage.getItem('queueAutoRefresh') !== 'false',
        countdownInterval: null,
        refreshTimeout: null,
        countdownSeconds: 5
    };

    // Fetch only the dynamic content and swap it in — no full page reload
    window.fetchQueueContent = function() {
        const icon = document.getElementById('refresh-icon');
        if (icon) icon.classList.add('spinning');

        fetch(queueRefreshUrl, {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(function(response) { return response.text(); })
        .then(function(html) {
            const container = document.getElementById('queue-dynamic-content');
            if (container) container.innerHTML = html;
            if (icon) icon.classList.remove('spinning');

            // Restart countdown if auto-refresh is still ON
            if (window.queueAutoRefresh.enabled) {
                window.startQueueCountdown();
            }
        })
        .catch(function() {
            if (icon) icon.classList.remove('spinning');
            if (window.queueAutoRefresh.enabled) {
                window.startQueueCountdown();
            }
        });
    };

    // Manual refresh button
    window.manualQueueRefresh = function() {
        const state = window.queueAutoRefresh;
        if (state.enabled) {
            if (state.countdownInterval) clearInterval(state.countdownInterval);
            if (state.refreshTimeout) clearTimeout(state.refreshTimeout);
        }
        window.fetchQueueContent();
    };

    // Toggle auto-refresh function
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

    // Start countdown — triggers fetchQueueContent (not location.reload)
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
                window.fetchQueueContent();
            }
        }, 5000);
    };

    // Update countdown badge helper
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

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', initQueuePage);
        } else {
            initQueuePage();
        }
    })();
</script>
@endsection