<div class="row mb-5">
    {{ html()->hidden('patient_id', $prescription->patient_id)->forgetAttribute('id') }}
    @if(Auth::user() && Auth::user()->hasRole('Doctor'))
    <input type="hidden" name="doctor_id" value="{{ Auth::user()->owner_id }}">
    @else
    {{ html()->hidden('doctor_id', $prescription->doctor_id)->forgetAttribute('id') }}
    @endif
    <div class="col-md-3">
        <div class="form-group mb-5">
            {{ html()->label()->for('health_insurance')->text(__('messages.prescription.health_insurance').(':'))->attributes(['class' => 'form-label']) }}
            {{ html()->text('health_insurance', null)->attributes(['class' => 'form-control', 'placeholder' => __('messages.prescription.health_insurance')]) }}
        </div>
    </div>
    <div class="col-md-3">
        <div class="form-group mb-5">
            {{ html()->label()->for('low_income')->text(__('messages.prescription.low_income').(':'))->attributes(['class' => 'form-label']) }}
            {{ html()->text('low_income', null)->attributes(['class' => 'form-control', 'placeholder' => __('messages.prescription.low_income')]) }}
        </div>
    </div>
    <div class="col-md-3">
        <div class="form-group mb-5">
            {{ html()->label()->for('reference')->text(__('messages.prescription.reference').(':'))->attributes(['class' => 'form-label']) }}
            {{ html()->text('reference', null)->attributes(['class' => 'form-control', 'placeholder' => __('messages.prescription.reference')]) }}
        </div>
    </div>
    <div class="col-md-3">
        <div class="form-group mb-5">
            {{ html()->label()->for('is_active')->text(__('messages.web.status').(':'))->attributes(['class' => 'form-label']) }}
            <br>
            <div class="form-check form-check-solid form-switch fv-row">
                <input name="is_active" class="form-check-input is-active cursor-pointer" value="1"
                    type="checkbox" {{(isset($prescription) && ($prescription->is_active)) ? 'checked' : ''}}>
                <label class="form-check-label" for="allowmarketing"></label>
            </div>
        </div>
    </div>
</div>