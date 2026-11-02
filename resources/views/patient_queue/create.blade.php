@extends('layouts.app')
@section('title')
{{ __('Add Patient to Queue') }}
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
            {{ html()->form('POST', route(getRouteNameByRole('patient-queue.store')))->attribute('accept-charset', 'UTF-8')->attributes(['id' => 'createQueueForm'])->open() }}

            <div class="row">
                <!-- Patient Selection -->
                <div class="col-md-6 mb-3">
                    {{ html()->label()->for('patient_id')->text(__('Select Patient').':')->attributes(['class' => 'form-label required']) }}
                    {{ html()->select('patient_id', $patients->pluck('user.full_name', 'id')->prepend('Select Patient', ''), '')->attributes(['class' => 'form-select', 'required', 'id' => 'patientId']) }}
                </div>

                <!-- Room Number -->
                <div class="col-md-6 mb-3">
                    {{ html()->label()->for('room_number')->text(__('Room Number').':')->attributes(['class' => 'form-label']) }}
                    {{ html()->text('room_number', null)->attributes(['class' => 'form-control', 'placeholder' => 'e.g., Room 101']) }}
                </div>
            </div>

            <div class="row">
                <!-- Priority Checkbox -->
                <div class="col-md-6 mb-3">
                    <div class="form-check form-switch">
                        {{ html()->checkbox('is_priority', false, 1)->attributes(['class' => 'form-check-input', 'id' => 'isPriority']) }}
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
                <a href="{{ getRouteByRole('patient-queue.index') }}" class="btn btn-secondary me-2">
                    {{ __('messages.common.cancel') }}
                </a>
                {{ html()->input('submit', null, __('Add to Queue'))->attributes(['class' => 'btn btn-primary']) }}
            </div>

            {{ html()->closeModelForm() }}
        </div>
    </div>
</div>
@endsection

@section('page_js')
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
                // Escaped: the option text is a user-entered name and is inserted with .html() below.
                const patientName = $('<div>').text($(this).find('option:selected').text()).html();
                
                // Fetch patient consultation form info via AJAX
                $.ajax({
                    url: @json(route('patient.latest-consultation', ['patient' => '__PATIENT__'])).replace('__PATIENT__', patientId),
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
