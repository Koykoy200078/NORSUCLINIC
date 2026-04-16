<div class="modal fade" id="add_generic_modal" tabindex="-1" role="dialog" aria-labelledby="addGenericModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addGenericModalLabel">{{ __('messages.medicine_generics') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            {{ Form::open(['id' => 'addGenericForm']) }}
            <div class="modal-body">
                <div class="alert alert-danger d-none" id="genericErrorsBox"></div>
                <div class="form-group mb-4">
                    {{ Form::label('name', __('messages.common.name') . ':', ['class' => 'form-label']) }}
                    <span class="required"></span>
                    {{ Form::text('name', null, ['class' => 'form-control', 'placeholder' => __('messages.common.name'), 'required', 'id' => 'genericName']) }}
                </div>
                <div class="modal-footer p-0 pt-3">
                    {{ Form::button(__('messages.common.save'), ['type' => 'submit', 'class' => 'btn btn-primary', 'id' => 'genericSaveBtn']) }}
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('messages.common.cancel') }}</button>
                </div>
            </div>
            {{ Form::close() }}
        </div>
    </div>
</div>