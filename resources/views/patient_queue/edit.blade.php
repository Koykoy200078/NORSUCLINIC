@extends('layouts.app')
@section('title')
{{ __('Edit Queue Entry') }}
@endsection
@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-end mb-5">
        <h1>@yield('title')</h1>
        <a class="btn btn-outline-primary" href="{{ getRouteByRole('patient-queue.index') }}">
            {{ __('messages.common.back') }}
        </a>
    </div>

    <div class="col-12">
        @include('layouts.errors')
    </div>

    <div class="card">
        <div class="card-body">
            {{ html()->modelForm($patientQueue, 'PUT', route(getRouteNameByRole('patient-queue.update'), [$patientQueue]))->attribute('accept-charset', 'UTF-8')->open() }}

            <div class="row">
                <!-- Patient Info (Read-only) -->
                <div class="col-md-12 mb-3">
                    <div class="alert alert-info">
                        <h5>Patient: {{ $patientQueue->patient?->user?->full_name ?? "Archived patient" }}</h5>
                        <p class="mb-0">ID: {{ $patientQueue->patient?->user?->university_id_number ?? $patientQueue->patient?->patient_unique_id ?? "N/A" }}</p>
                    </div>
                </div>
            </div>

            <div class="row">
                <!-- Room Number -->
                <div class="col-md-6 mb-3">
                    {{ html()->label()->for('room_number')->text(__('Room Number').':')->attributes(['class' => 'form-label']) }}
                    {{ html()->text('room_number', null)->attributes(['class' => 'form-control', 'placeholder' => 'e.g., Room 101']) }}
                </div>

                <!-- Status -->
                <div class="col-md-6 mb-3">
                    {{ html()->label()->for('status')->text(__('Status').':')->attributes(['class' => 'form-label required']) }}
                    {{ html()->select('status', [
                            'waiting' => 'Waiting',
                            'in_progress' => 'In Progress',
                            'completed' => 'Completed',
                            'cancelled' => 'Cancelled'
                        ], null)->attributes(['class' => 'form-select', 'required']) }}
                </div>
            </div>

            <div class="row">
                <!-- Priority Checkbox -->
                <div class="col-md-6 mb-3">
                    <div class="form-check form-switch">
                        {{ html()->checkbox('is_priority', null, 1)->attributes(['class' => 'form-check-input', 'id' => 'isPriority']) }}
                        {{ html()->label()->for('is_priority')->text(__('Mark as Priority'))->attributes(['class' => 'form-check-label']) }}
                        <small class="d-block text-muted">Check this if patient needs immediate attention</small>
                    </div>
                </div>
            </div>

            <div class="row">
                <!-- Notes -->
                <div class="col-md-12 mb-3">
                    {{ html()->label()->for('notes')->text(__('Notes').':')->attributes(['class' => 'form-label']) }}
                    {{ html()->textarea('notes')->text(html()->value('notes', null))->attributes(['cols' => 50])->attributes(['class' => 'form-control', 'rows' => 3, 'placeholder' => 'Any special notes or instructions...']) }}
                </div>
            </div>

            <!-- Queue Information -->
            <div class="row">
                <div class="col-md-12 mb-3">
                    <div class="card bg-light">
                        <div class="card-body">
                            <h6>Queue Information:</h6>
                            <p class="mb-1"><strong>Added By:</strong> {{ $patientQueue->addedBy?->full_name ?? 'N/A' }}</p>
                            <p class="mb-1"><strong>Added At:</strong> {{ $patientQueue->created_at->format('M d, Y h:i A') }}</p>
                            @if($patientQueue->called_at)
                            <p class="mb-1"><strong>Called At:</strong> {{ $patientQueue->called_at->format('M d, Y h:i A') }}</p>
                            @endif
                            @if($patientQueue->completed_at)
                            <p class="mb-0"><strong>Completed At:</strong> {{ $patientQueue->completed_at->format('M d, Y h:i A') }}</p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <div class="d-flex justify-content-end">
                <a href="{{ getRouteByRole('patient-queue.index') }}" class="btn btn-secondary me-2">
                    {{ __('messages.common.cancel') }}
                </a>
                {{ html()->input('submit', null, __('Update'))->attributes(['class' => 'btn btn-primary']) }}
            </div>

            {{ html()->closeModelForm() }}
        </div>
    </div>
</div>
@endsection