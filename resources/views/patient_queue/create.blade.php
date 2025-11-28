@extends('layouts.app')
@section('title')
{{ __('Add Patient to Queue') }}
@endsection
@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-end mb-5">
        <h1>@yield('title')</h1>
        <a class="btn btn-outline-primary" href="{{ isRole('clinic_admin') ? route('patient-queue.index') : route('staff.patient-queue.index') }}">
            {{ __('messages.common.back') }}
        </a>
    </div>

    <div class="col-12">
        @include('layouts.errors')
    </div>

    <div class="card">
        <div class="card-body">
            {{ Form::open(['route' => isRole('clinic_admin') ? 'patient-queue.store' : 'staff.patient-queue.store', 'id' => 'createQueueForm']) }}

            <div class="row">
                <!-- Patient Selection -->
                <div class="col-md-6 mb-3">
                    {{ Form::label('patient_id', __('Select Patient').':', ['class' => 'form-label required']) }}
                    {{ Form::select('patient_id', $patients->pluck('user.full_name', 'id')->prepend('Select Patient', ''), null, ['class' => 'form-select', 'required', 'id' => 'patientId']) }}
                </div>

                <!-- Room Number -->
                <div class="col-md-6 mb-3">
                    {{ Form::label('room_number', __('Room Number').':', ['class' => 'form-label']) }}
                    {{ Form::text('room_number', null, ['class' => 'form-control', 'placeholder' => 'e.g., Room 101']) }}
                </div>
            </div>

            <div class="row">
                <!-- Priority Checkbox -->
                <div class="col-md-6 mb-3">
                    <div class="form-check form-switch">
                        {{ Form::checkbox('is_priority', 1, false, ['class' => 'form-check-input', 'id' => 'isPriority']) }}
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

            <!-- Patient Info Preview -->
            <div class="row" id="patientInfoPreview" style="display: none;">
                <div class="col-md-12 mb-3">
                    <div class="alert alert-info">
                        <h5>Patient Information:</h5>
                        <div id="patientDetails"></div>
                    </div>
                </div>
            </div>

            <div class="d-flex justify-content-end">
                <a href="{{ isRole('clinic_admin') ? route('patient-queue.index') : route('staff.patient-queue.index') }}" class="btn btn-secondary me-2">
                    {{ __('messages.common.cancel') }}
                </a>
                {{ Form::submit(__('Add to Queue'), ['class' => 'btn btn-primary']) }}
            </div>

            {{ Form::close() }}
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    $(document).ready(function() {
        // Initialize Select2 for better patient selection
        $('#patientId').select2({
            placeholder: 'Search and select patient...',
            allowClear: true
        });

        // Show patient info and check for consultation forms when selected
        $('#patientId').on('change', function() {
            const patientId = $(this).val();
            if (patientId) {
                $('#patientInfoPreview').show();
                const patientName = $(this).find('option:selected').text();
                
                // Fetch patient consultation form info via AJAX
                $.ajax({
                    url: '/api/patient/' + patientId + '/latest-consultation',
                    method: 'GET',
                    success: function(response) {
                        let consultationInfo = '';
                        if (response.success && response.consultation) {
                            const consultationDate = new Date(response.consultation.created_at).toLocaleDateString();
                            consultationInfo = `
                                <div class="alert alert-info mt-2">
                                    <strong><i class="fas fa-file-medical"></i> Latest Consultation Form:</strong>
                                    <br><small>Date: ${consultationDate}</small>
                                    <br><small>This consultation form will be automatically attached to the queue entry.</small>
                                </div>
                            `;
                        } else {
                            consultationInfo = `
                                <div class="alert alert-warning mt-2">
                                    <strong><i class="fas fa-exclamation-triangle"></i> No Consultation Form Found</strong>
                                    <br><small>This patient has no previous consultation forms.</small>
                                </div>
                            `;
                        }
                        
                        $('#patientDetails').html(`
                            <strong>Name:</strong> ${patientName}
                            ${consultationInfo}
                        `);
                    },
                    error: function() {
                        $('#patientDetails').html(`
                            <strong>Name:</strong> ${patientName}
                            <div class="alert alert-secondary mt-2">
                                <small>Could not load consultation form information.</small>
                            </div>
                        `);
                    }
                });
            } else {
                $('#patientInfoPreview').hide();
            }
        });
    });
</script>
@endsection