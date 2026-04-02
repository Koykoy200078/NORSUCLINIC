<div class="row gx-10 mb-5">
    {{ Form::hidden('appointment_id', null) }}
    {{ Form::hidden('patient_id', $patientId ?? null) }}

    @if (Auth::user()->hasRole('Doctor'))
    <input type="hidden" name="doctor_id" value="{{ Auth::user()->owner_id }}">
    @else
    <div class="col-md-6 mb-5">
        {{ Form::label('doctor_id', __('messages.doctors') . ':', ['class' => 'form-label required']) }}
        {{ Form::select('doctor_id', $doctors ?? [], null, ['class' => 'form-select', 'required', 'placeholder' => __('messages.common.select_doctor')]) }}
    </div>
    @endif
    <div class="col-md-3">
        <div class="form-group mb-5">
            {{ Form::label('health_insurance', __('messages.prescription.health_insurance') . ':', ['class' => 'form-label']) }}
            {{ Form::text('health_insurance', null, ['class' => 'form-control','placeholder' => __('messages.prescription.health_insurance') ]) }}

        </div>
    </div>
    <div class="col-md-3">
        <div class="form-group mb-5">
            {{ Form::label('low_income', __('messages.prescription.low_income') . ':', ['class' => 'form-label']) }}
            {{ Form::text('low_income', null, ['class' => 'form-control','placeholder' => __('messages.prescription.low_income')]) }}
        </div>
    </div>
    <div class="col-md-3">
        <div class="form-group mb-5">
            {{ Form::label('reference', __('messages.prescription.reference') . ':', ['class' => 'form-label']) }}
            {{ Form::text('reference', null, ['class' => 'form-control','placeholder' => __('messages.prescription.reference')]) }}
        </div>
    </div>
    <div class="col-md-3">
        <div class="form-group mb-5">
            {{ Form::label('status', __('messages.web.status') . ':', ['class' => 'form-label']) }}
            <br>
            <div class="form-check form-check-solid form-switch fv-row">
                <input name="status" class="form-check-input is-active" value="1" type="checkbox" checked>
                <label class="form-check-label" for="allowmarketing"></label>
            </div>
        </div>
    </div>
</div>