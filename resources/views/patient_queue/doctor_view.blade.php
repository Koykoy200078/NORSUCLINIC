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
</style>
<div class="container-fluid">
    @include('flash::message')

    <div class="d-flex justify-content-between align-items-end mb-5">
        <h1><i class="fas fa-stethoscope"></i> @yield('title')</h1>
        <div class="d-flex gap-2">
            <button class="btn btn-info" id="manual-refresh-btn" onclick="window.manualQueueRefresh()">
                <i class="fas fa-sync" id="refresh-icon"></i> Refresh Queue
            </button>
        </div>
    </div>

    <!-- Dynamic queue content (refreshed via AJAX) -->
    <div id="queue-dynamic-content">
        @include('patient_queue.doctor_view_partial')
    </div>
</div>

<script>
    // Redraws itself on the real-time notice for the queue (resources/assets/js/realtime) and, only while the live
    // connection is down, every minute; the Refresh Queue button does the same on demand.
    (function() {
        const queueRefreshUrl = "{{ route('doctors.patient-queue.refresh') }}";
        let fetching = false;
        let again = false;

        window.fetchQueueContent = function() {
            if (fetching) {
                again = true; // something changed while the last answer was on its way: ask once more afterwards
                return;
            }
            fetching = true;
            const icon = document.getElementById('refresh-icon');
            if (icon) icon.classList.add('spinning');

            fetch(queueRefreshUrl, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(function(response) {
                    return response.text();
                })
                .then(function(html) {
                    const container = document.getElementById('queue-dynamic-content');
                    if (container) container.innerHTML = html;
                })
                .catch(function() {})
                .finally(function() {
                    fetching = false;
                    if (icon) icon.classList.remove('spinning');
                    if (again) {
                        again = false;
                        window.fetchQueueContent();
                    }
                });
        };

        // AJAX complete consultation handler (called by Complete button in partial)
        window.completeConsultation = function(formEl) {
            const btn = formEl.querySelector('button[type="submit"]');
            if (btn) {
                btn.disabled = true;
                btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Completing...';
            }

            fetch(formEl.action, {
                    method: 'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': formEl.querySelector('[name=_token]').value
                    }
                })
                .then(function(r) {
                    return r.json();
                })
                .then(function(data) {
                    if (data.success) {
                        window.fetchQueueContent();
                    }
                })
                .catch(function() {
                    // the answer was lost: draw the queue again to show what really happened
                    window.fetchQueueContent();
                });
            return false;
        };

        window.manualQueueRefresh = window.fetchQueueContent;

        window.removeEventListener('clinic:live-refresh', window.queueLiveHandler);
        window.queueLiveHandler = function(event) {
            if (event.detail && event.detail.module === 'queue' && document.getElementById('queue-dynamic-content')) {
                window.fetchQueueContent();
            }
        };
        window.addEventListener('clinic:live-refresh', window.queueLiveHandler);
    })();
</script>
@endsection