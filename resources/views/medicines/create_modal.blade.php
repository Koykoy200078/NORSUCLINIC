<div class="modal fade" id="add_medicine_modal" tabindex="-1" role="dialog" aria-labelledby="addMedicineModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addMedicineModalLabel">{{ __('messages.medicine.new_medicine') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            {{ Form::open(['id' => 'addMedicineForm']) }}
            <div class="modal-body">
                <div class="alert alert-danger d-none" id="medicineCreateErrorsBox"></div>
                <div class="row">
                    <div class="form-group col-md-6 mb-4">
                        {{ Form::label('generic_name', 'Generic Name:', ['class' => 'form-label']) }}
                        <span class="required"></span>
                        {{ Form::text('generic_name', null, ['class' => 'form-control', 'placeholder' => 'e.g., Paracetamol', 'required']) }}
                    </div>
                    <div class="form-group col-md-6 mb-4">
                        {{ Form::label('brand_name', 'Brand Name:', ['class' => 'form-label']) }}
                        {{ Form::text('brand_name', null, ['class' => 'form-control', 'placeholder' => 'e.g., Biogesic']) }}
                    </div>
                    <div class="form-group col-md-6 mb-4">
                        {{ Form::label('category', __('messages.medicine.category') . ':', ['class' => 'form-label']) }}
                        <span class="required"></span>
                        {{ Form::text('category', null, ['class' => 'form-control', 'placeholder' => 'e.g., Analgesic', 'required']) }}
                    </div>
                    <div class="form-group col-md-6 mb-4">
                        {{ Form::label('dosage', 'Dosage:', ['class' => 'form-label']) }}
                        <span class="required"></span>
                        {{ Form::text('dosage', null, ['class' => 'form-control', 'placeholder' => 'e.g., 500 mg', 'required']) }}
                    </div>
                    <div class="form-group col-md-6 mb-4">
                        {{ Form::label('uom', 'Unit of Measure:', ['class' => 'form-label']) }}
                        <span class="required"></span>
                        {{ Form::text('uom', null, ['class' => 'form-control', 'placeholder' => 'tablet, capsule, vial', 'required']) }}
                    </div>
                    <div class="form-group col-md-6 mb-4">
                        {{ Form::label('sku', 'SKU:', ['class' => 'form-label']) }}
                        {{ Form::text('sku', null, ['class' => 'form-control', 'placeholder' => 'Optional stock code']) }}
                    </div>
                    <div class="form-group col-md-6 mb-4">
                        {{ Form::label('reorder_level', 'Reorder Level:', ['class' => 'form-label']) }}
                        {{ Form::number('reorder_level', null, ['class' => 'form-control', 'placeholder' => 'e.g., 10', 'min' => 0]) }}
                    </div>
                    <div class="form-group col-md-6 mb-4">
                        {{ Form::label('initial_stock_quantity', 'Initial Stock Quantity:', ['class' => 'form-label']) }}
                        {{ Form::number('initial_stock_quantity', 0, ['class' => 'form-control', 'min' => 0]) }}
                    </div>
                    <div class="form-group col-md-6 mb-4">
                        {{ Form::label('batch_number', 'Batch Number:', ['class' => 'form-label']) }}
                        {{ Form::text('batch_number', null, ['class' => 'form-control', 'placeholder' => 'Required when stock > 0']) }}
                    </div>
                    <div class="form-group col-md-6 mb-4">
                        {{ Form::label('expiration_date', 'Expiration Date:', ['class' => 'form-label']) }}
                        {{ Form::date('expiration_date', null, ['class' => 'form-control']) }}
                    </div>
                    <div class="form-group col-md-6 mb-4">
                        {{ Form::label('description', __('messages.medicine.description') . ':', ['class' => 'form-label']) }}
                        {{ Form::textarea('description', null, ['class' => 'form-control', 'placeholder' => __('messages.medicine.description'), 'rows' => 3]) }}
                    </div>
                </div>
                {{ Form::hidden('quantity', 0, ['id' => 'createMedicineQuantity']) }}
                {{ Form::hidden('available_quantity', 0, ['id' => 'createMedicineAvailableQuantity']) }}
            </div>
            <div class="modal-footer">
                {{ Form::button(__('messages.common.save'), ['type' => 'submit', 'class' => 'btn btn-primary', 'id' => 'medicineSaveModalBtn']) }}
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('messages.common.cancel') }}</button>
            </div>
            {{ Form::close() }}
        </div>
    </div>
</div>