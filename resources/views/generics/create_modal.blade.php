<div class="modal fade" id="add_generic_modal" tabindex="-1" role="dialog" aria-labelledby="addGenericModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addGenericModalLabel">{{ __('messages.medicine_generics') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            {{ html()->form('POST', url()->current())->attribute('accept-charset', 'UTF-8')->attributes(['id' => 'addGenericForm'])->open() }}
            <div class="modal-body">
                <div class="alert alert-danger d-none" id="genericErrorsBox"></div>
                <div class="form-group mb-4">
                    {{ html()->label()->for('name')->text(__('messages.common.name') . ':')->attributes(['class' => 'form-label']) }}
                    <span class="required"></span>
                    {{ html()->text('name', null)->attributes(['class' => 'form-control', 'placeholder' => __('messages.common.name'), 'required', 'id' => 'genericName']) }}
                </div>
                <div class="modal-footer p-0 pt-3">
                    {{ html()->button(__('messages.common.save'), 'button')->attributes(['type' => 'submit', 'class' => 'btn btn-primary', 'id' => 'genericSaveBtn']) }}
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('messages.common.cancel') }}</button>
                </div>
            </div>
            {{ html()->closeModelForm() }}
        </div>
    </div>
</div>