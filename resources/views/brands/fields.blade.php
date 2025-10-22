<div class="row">
    <div class="col-md-6">
        <div class="form-group mb-5">
            {!! Form::label('name', __('messages.medicine.brand'). ' Name' . ':', ['class' => 'form-label']) !!}
            <span class="required"></span>
            {!! Form::text('name', null, ['id'=>'brandName','class' => 'form-control','placeholder' => __('messages.medicine.brand'),'required']) !!}
        </div>
    </div>
    <div class="d-flex justify-content-end">
        {{ Form::submit(__('messages.common.save'), ['class' => 'btn btn-primary me-2', 'id' => 'brandSave']) }}
        <a href="{!! 
            isRole('clinic_admin') ? route('brands.index') : 
            (isRole('staff') ? route('staff.brands.index') : 
            (isRole('doctor') ? route('doctors.brands.index') : route('brands.index'))) 
        !!}"
            class="btn btn-secondary">{!! __('messages.common.cancel') !!}</a>
    </div>
</div>