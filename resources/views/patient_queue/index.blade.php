@extends('layouts.app')
@section('title')
{{ __('Patient Queue Management') }}
@endsection
@section('content')
<style>
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
        </div>
    </div>

    <!-- Dynamic queue content (refreshed via AJAX) -->
    <div id="queue-dynamic-content">
        @include('patient_queue.index_partial')
    </div>
</div>

<script>
    // The queue redraws itself when a patient is added, called, completed or removed: the real-time notice
    // ("clinic:live-refresh", resources/assets/js/realtime) and, only while the live connection is down, a refresh every minute.
    // The Refresh Queue button does the same on demand.
    (function() {
        const url = "{{ getRouteByRole('patient-queue.refresh') }}";
        let fetching = false;
        let again = false;

        window.pqFetchContent = function() {
            if (fetching) {
                again = true; // something changed while the last answer was on its way: ask once more afterwards
                return;
            }
            fetching = true;
            const icon = document.getElementById('pq-refresh-icon');
            if (icon) icon.classList.add('spinning');

            fetch(url, {
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
                })
                .catch(function() {})
                .finally(function() {
                    fetching = false;
                    if (icon) icon.classList.remove('spinning');
                    if (again) {
                        again = false;
                        window.pqFetchContent();
                    }
                });
        };

        window.pqManualRefresh = window.pqFetchContent;

        window.removeEventListener('clinic:live-refresh', window.pqLiveHandler);
        window.pqLiveHandler = function(event) {
            if (event.detail && event.detail.module === 'queue' && document.getElementById('queue-dynamic-content')) {
                window.pqFetchContent();
            }
        };
        window.addEventListener('clinic:live-refresh', window.pqLiveHandler);
    })();
</script>
@endsection