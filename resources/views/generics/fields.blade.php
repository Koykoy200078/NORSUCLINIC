<div class="row">
    <div class="col-md-6">
        <div class="form-group mb-5">
            {!! html()->label()->for('name')->text(__('messages.medicine.generic'). ' Name' . ':')->attributes(['class' => 'form-label']) !!}
            <span class="required"></span>
            {!! html()->text('name', null)->attributes(['id'=>'genericName', 'class' => 'form-control', 'placeholder' => __('messages.medicine.generic'), 'required']) !!}
        </div>
    </div>
    <div class="d-flex justify-content-end">
        {{ html()->input('submit', null, __('messages.common.save'))->attributes(['class' => 'btn btn-primary me-2', 'id' => 'genericSave']) }}
        <a href="{!! 
            getRouteByRole('generics.index')
        !!}"
            class="btn btn-secondary">{!! __('messages.common.cancel') !!}</a>
    </div>
</div>