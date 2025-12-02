<div class="row">
    <div class="col-md-6">
        <div class="form-group mb-5">
            {!! Form::label('name', __('messages.medicine.generic'). ' Name' . ':', ['class' => 'form-label']) !!}
            <span class="required"></span>
            {!! Form::text('name', null, ['id'=>'genericName','class' => 'form-control','placeholder' => __('messages.medicine.generic'),'required']) !!}
        </div>
    </div>
    <div class="d-flex justify-content-end">
        {{ Form::submit(__('messages.common.save'), ['class' => 'btn btn-primary me-2', 'id' => 'genericSave']) }}
        <a href="{!! 
            isRole('clinic_admin') ? route('generics.index') : 
            (isRole('staff') ? route('staff.generics.index') : 
            (isRole('doctor') ? route('doctors.generics.index') : route('generics.index'))) 
        !!}"
            class="btn btn-secondary">{!! __('messages.common.cancel') !!}</a>
    </div>
</div>