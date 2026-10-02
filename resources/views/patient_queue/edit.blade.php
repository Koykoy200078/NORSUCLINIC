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
            {{ Form::model($patientQueue, ['route' => [isRole('clinic_admin') ? 'patient-queue.update' : 'staff.patient-queue.update', $patientQueue], 'method' => 'PUT']) }}

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
                    {{ Form::label('room_number', __('Room Number').':', ['class' => 'form-label']) }}
                    {{ Form::text('room_number', null, ['class' => 'form-control', 'placeholder' => 'e.g., Room 101']) }}
                </div>

                <!-- Status -->
                <div class="col-md-6 mb-3">
                    {{ Form::label('status', __('Status').':', ['class' => 'form-label required']) }}
                    {{ Form::select('status', [
                            'waiting' => 'Waiting',
                            'in_progress' => 'In Progress',
                            'completed' => 'Completed',
                            'cancelled' => 'Cancelled'
                        ], null, ['class' => 'form-select', 'required']) }}
                </div>
            </div>

            <div class="row">
                <!-- Priority Checkbox -->
                <div class="col-md-6 mb-3">
                    <div class="form-check form-switch">
                        {{ Form::checkbox('is_priority', 1, null, ['class' => 'form-check-input', 'id' => 'isPriority']) }}
                        {{ Form::label('is_priority', __('Mark as Priority'), ['class' => 'form-check-label']) }}
                        <small class="d-block text-muted">Check this if patient needs immediate attention</small>
                    </div>
                </div>
            </div>

            <div class="row">
                <!-- Notes -->
                <div class="col-md-12 mb-3">
                    {{ Form::label('notes', __('Notes').':', ['class' => 'form-label']) }}
                    {{ Form::textarea('notes', null, ['class' => 'form-control', 'rows' => 3, 'placeholder' => 'Any special notes or instructions...']) }}
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
                <a href="{{ route('staff.patient-queue.index') }}" class="btn btn-secondary me-2">
                    {{ __('messages.common.cancel') }}
                </a>
                {{ Form::submit(__('Update'), ['class' => 'btn btn-primary']) }}
            </div>

            {{ Form::close() }}
        </div>
    </div>
</div>
@endsection