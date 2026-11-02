<div class="row gx-10 mb-5">
    <div class="col-md-12">
        <div class="form-group mb-5">
            {{ html()->label()->for('test')->text(__('messages.prescription.test').(':'))->attributes(['class' => 'form-label']) }}
            {{ html()->textarea('test')->text(html()->value('test', null))->attributes(['cols' => 50])->attributes(['class' => 'form-control', 'placeholder' => __('messages.prescription.test'), 'rows' => 5]) }}
        </div>
    </div>
    <div class="col-md-12">
        <div class="form-group mb-5">
            {{ html()->label()->for('advice')->text(__('messages.prescription.advice').(':'))->attributes(['class' => 'form-label']) }}
            {{ html()->textarea('advice')->text(html()->value('advice', null))->attributes(['cols' => 50])->attributes(['class' => 'form-control', 'placeholder' => __('messages.prescription.advice'), 'rows' => 5]) }}
        </div>
    </div>
    <div class="col-md-10">
        <div class="form-group row mb-5">
            {{ html()->label()->for('next_visit')->text(__('messages.prescription.next_visit').(':'))->attributes(['class' => 'form-label']) }}
            <div class="col-md-1 mt-3">
                {{ html()->number('next_visit_qty', null)->forgetAttribute('id')->attributes(['class' => 'form-control', 'placeholder'=>'1']) }}
            </div>
            <div class="col-md-11 mt-3">
                {{ html()->select('next_visit_time', \App\Models\Prescription::TIME_ARR, null)->attributes(['class' => 'form-select', 'required', 'id' => 'editPrescriptionTime']) }}
            </div>
        </div>
    </div>
</div>
<div class="float-end">
    {!! html()->input('submit', null, __('messages.common.save'))->attributes(['class' => 'btn btn-primary me-2', 'id' => 'btnSave']) !!}
    <a href="{{ route(isRole('patient') ? 'patients.dashboard' : getRouteNameByRole('prescriptions.index')) }}"
        class="btn btn-secondary">{!! __('messages.common.cancel') !!}</a>
</div>