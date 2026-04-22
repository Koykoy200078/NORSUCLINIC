@extends('layouts.app')
@section('title')
{{ __('Patient Queue Management') }}
@endsection
@section('content')
<style>
    #pq-countdown-badge {
        font-size: 0.9rem;
        padding: 0.5rem 0.75rem;
        transition: background-color 0.3s ease, transform 0.3s ease;
    }

    #pq-countdown-badge.pulse {
        animation: pqPulse 1s ease-in-out;
    }

    @keyframes pqPulse {

        0%,
        100% {
            transform: scale(1)
        }

        50% {
            transform: scale(1.1)
        }
    }

    #pq-countdown-badge.countdown-5 {
        background-color: #28a745 !important;
    }

    #pq-countdown-badge.countdown-4 {
        background-color: #20c997 !important;
    }

    #pq-countdown-badge.countdown-3 {
        background-color: #ffc107 !important;
        color: #000 !important;
    }

    #pq-countdown-badge.countdown-2 {
        background-color: #fd7e14 !important;
        color: #fff !important;
    }

    #pq-countdown-badge.countdown-1 {
        background-color: #dc3545 !important;
        color: #fff !important;
        animation: pqPulse 0.5s ease-in-out;
    }

    #pq-refresh-icon.spinning {
        animation: pqSpin 1s linear;
    }

    @keyframes pqSpin {
        from {
            transform: rotate(0deg)
        }

        to {
            transform: rotate(360deg)
        }
    }
</style>
<div class="container-fluid">
    @include('flash::message')

    <div class="d-flex justify-content-between align-items-end mb-5">
        <h1>@yield('title')</h1>
        <div class="d-flex gap-2 align-items-center">
            @if(!isRole('doctor'))
            <a class="btn btn-primary" href="{{ getRouteByRole('patient-queue.create') }}">
                <i class="fas fa-plus"></i> Add Patient to Queue
            </a>
            @endif
            <button class="btn btn-info" id="pq-manual-refresh-btn" onclick="window.pqManualRefresh()">
                <i class="fas fa-sync" id="pq-refresh-icon"></i> Refresh Queue
            </button>
            <button class="btn btn-outline-primary" id="pq-toggle-auto-refresh" onclick="window.pqToggleAutoRefresh()">
                <i class="fas fa-play"></i> <span id="pq-toggle-text">Auto-Refresh OFF</span>
            </button>
            <span class="badge d-none d-flex align-items-center countdown-5" id="pq-countdown-badge">
                <i class="fas fa-clock me-1"></i> <span id="pq-countdown">5s</span>
            </span>
        </div>
    </div>

    <!-- Dynamic queue content (refreshed via AJAX) -->
    <div id="queue-dynamic-content">
        @include('patient_queue.index_partial')
    </div>
</div>

<script>
    const pqRefreshUrl = "{{ getRouteByRole('patient-queue.refresh') }}";

    window.pqAutoRefresh = {
        enabled: localStorage.getItem('pqAutoRefresh') !== 'false',
        countdownInterval: null,
        refreshTimeout: null,
        countdownSeconds: 5,
        isFetching: false
    };

    // Fetch only the dynamic content and swap it — no full page reload
    window.pqFetchContent = function() {
        const state = window.pqAutoRefresh;
        if (state.isFetching) {
            // Previous request still in flight — reschedule instead of stacking
            if (state.enabled) window.pqStartCountdown();
            return;
        }
        state.isFetching = true;
        pqUpdateCountdown();
        const icon = document.getElementById('pq-refresh-icon');
        if (icon) icon.classList.add('spinning');

        fetch(pqRefreshUrl, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(function(r) {
                return r.text();
            })
            .then(function(html) {
                const container = document.getElementById('queue-dynamic-content');
                if (container) container.innerHTML = html;
                if (icon) icon.classList.remove('spinning');
                state.isFetching = false;
                if (state.enabled) window.pqStartCountdown();
            })
            .catch(function() {
                if (icon) icon.classList.remove('spinning');
                state.isFetching = false;
                if (window.pqAutoRefresh.enabled) window.pqStartCountdown();
            });
    };

    window.pqManualRefresh = function() {
        const state = window.pqAutoRefresh;
        if (state.enabled) {
            if (state.countdownInterval) clearInterval(state.countdownInterval);
            if (state.refreshTimeout) clearTimeout(state.refreshTimeout);
        }
        window.pqFetchContent();
    };

    window.pqToggleAutoRefresh = function() {
        const state = window.pqAutoRefresh;
        state.enabled = !state.enabled;
        localStorage.setItem('pqAutoRefresh', state.enabled);

        const btn = document.getElementById('pq-toggle-auto-refresh');
        const text = document.getElementById('pq-toggle-text');
        const icon = btn ? btn.querySelector('i') : null;

        if (state.enabled) {
            if (btn) {
                btn.classList.remove('btn-outline-primary');
                btn.classList.add('btn-outline-success');
            }
            if (icon) {
                icon.classList.remove('fa-play');
                icon.classList.add('fa-pause');
            }
            if (text) text.textContent = 'Auto-Refresh ON';
            window.pqStartCountdown();
        } else {
            if (btn) {
                btn.classList.remove('btn-outline-success');
                btn.classList.add('btn-outline-primary');
            }
            if (icon) {
                icon.classList.remove('fa-pause');
                icon.classList.add('fa-play');
            }
            if (text) text.textContent = 'Auto-Refresh OFF';
            if (state.countdownInterval) clearInterval(state.countdownInterval);
            if (state.refreshTimeout) clearTimeout(state.refreshTimeout);
            pqUpdateCountdown();
        }
    };

    window.pqStartCountdown = function() {
        const state = window.pqAutoRefresh;
        if (!state.enabled) return;

        state.countdownSeconds = 5;
        pqUpdateCountdown();

        if (state.countdownInterval) clearInterval(state.countdownInterval);
        if (state.refreshTimeout) clearTimeout(state.refreshTimeout);

        state.countdownInterval = setInterval(function() {
            state.countdownSeconds--;
            pqUpdateCountdown();
            if (state.countdownSeconds <= 0) clearInterval(state.countdownInterval);
        }, 1000);

        state.refreshTimeout = setTimeout(function() {
            if (state.enabled) window.pqFetchContent();
        }, 5000);
    };

    function pqUpdateCountdown() {
        const state = window.pqAutoRefresh;
        const badge = document.getElementById('pq-countdown-badge');
        const el = document.getElementById('pq-countdown');
        if (!badge || !el) return;

        if (!state.enabled) {
            badge.classList.add('d-none');
            return;
        }

        badge.classList.remove('d-none', 'countdown-5', 'countdown-4', 'countdown-3', 'countdown-2', 'countdown-1');

        if (state.isFetching) {
            badge.classList.add('countdown-4');
            el.innerHTML = '<i class="fas fa-sync fa-spin"></i>';
            return;
        }

        el.textContent = state.countdownSeconds + 's';
        if (state.countdownSeconds >= 1 && state.countdownSeconds <= 5) {
            badge.classList.add('countdown-' + state.countdownSeconds);
        }
        badge.classList.add('pulse');
        setTimeout(function() {
            badge.classList.remove('pulse');
        }, 300);
    }

    (function() {
        function init() {
            const state = window.pqAutoRefresh;
            const btn = document.getElementById('pq-toggle-auto-refresh');
            const text = document.getElementById('pq-toggle-text');
            const icon = btn ? btn.querySelector('i') : null;
            const badge = document.getElementById('pq-countdown-badge');
            if (!btn) return;

            if (state.enabled) {
                btn.classList.add('btn-outline-success');
                btn.classList.remove('btn-outline-primary');
                if (icon) {
                    icon.classList.add('fa-pause');
                    icon.classList.remove('fa-play');
                }
                if (text) text.textContent = 'Auto-Refresh ON';
                if (badge) badge.classList.remove('d-none');
                window.pqStartCountdown();
            } else {
                btn.classList.add('btn-outline-primary');
                btn.classList.remove('btn-outline-success');
                if (icon) {
                    icon.classList.add('fa-play');
                    icon.classList.remove('fa-pause');
                }
                if (text) text.textContent = 'Auto-Refresh OFF';
                if (badge) badge.classList.add('d-none');
            }
        }

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', init);
        } else {
            init();
        }
    })();
</script>
@endsection