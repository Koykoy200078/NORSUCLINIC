<div class="row gx-10 mb-5">
    {{ html()->hidden('patient_id', $patientId ?? null)->forgetAttribute('id') }}

    @if (Auth::user()->hasRole('Doctor'))
    <input type="hidden" name="doctor_id" value="{{ Auth::user()->owner_id }}">
    @else
    <div class="col-md-6 mb-5">
        {{ html()->label()->for('doctor_id')->text(__('messages.doctors') . ':')->attributes(['class' => 'form-label required']) }}
        {{ html()->select('doctor_id', $doctors ?? [], null)->placeholder(__('messages.common.select_doctor'))->attributes(['class' => 'form-select', 'required']) }}
    </div>
    @endif
    <div class="col-md-3">
        <div class="form-group mb-5">
            {{ html()->label()->for('health_insurance')->text(__('messages.prescription.health_insurance') . ':')->attributes(['class' => 'form-label']) }}
            {{ html()->text('health_insurance', null)->attributes(['class' => 'form-control', 'placeholder' => __('messages.prescription.health_insurance')]) }}

        </div>
    </div>
    <div class="col-md-3">
        <div class="form-group mb-5">
            {{ html()->label()->for('low_income')->text(__('messages.prescription.low_income') . ':')->attributes(['class' => 'form-label']) }}
            {{ html()->text('low_income', null)->attributes(['class' => 'form-control', 'placeholder' => __('messages.prescription.low_income')]) }}
        </div>
    </div>
    <div class="col-md-3">
        <div class="form-group mb-5">
            {{ html()->label()->for('reference')->text(__('messages.prescription.reference') . ':')->attributes(['class' => 'form-label']) }}
            {{ html()->text('reference', null)->attributes(['class' => 'form-control', 'placeholder' => __('messages.prescription.reference')]) }}
        </div>
    </div>
    <div class="col-md-3">
        <div class="form-group mb-5">
            {{ html()->label()->for('status')->text(__('messages.web.status') . ':')->attributes(['class' => 'form-label']) }}
            <br>
            <div class="form-check form-check-solid form-switch fv-row">
                <input name="status" class="form-check-input is-active" value="1" type="checkbox" checked>
                <label class="form-check-label" for="allowmarketing"></label>
            </div>
        </div>
    </div>
</div>