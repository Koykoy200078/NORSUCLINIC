<div class="modal fade" id="add_medicine_modal" tabindex="-1" role="dialog" aria-labelledby="addMedicineModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addMedicineModalLabel">{{ __('messages.medicine.new_medicine') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            {{ html()->form('POST', url()->current())->attribute('accept-charset', 'UTF-8')->attributes(['id' => 'addMedicineForm'])->open() }}
            <div class="modal-body">
                <div class="alert alert-danger d-none" id="medicineCreateErrorsBox"></div>
                <div class="row">
                    <div class="form-group col-md-6 mb-4">
                        {{ html()->label()->for('generic_name')->text('Generic Name:')->attributes(['class' => 'form-label']) }}
                        <span class="required"></span>
                        {{ html()->text('generic_name', null)->attributes(['class' => 'form-control', 'placeholder' => 'e.g., Paracetamol', 'required']) }}
                    </div>
                    <div class="form-group col-md-6 mb-4">
                        {{ html()->label()->for('brand_name')->text('Brand Name:')->attributes(['class' => 'form-label']) }}
                        {{ html()->text('brand_name', null)->attributes(['class' => 'form-control', 'placeholder' => 'e.g., Biogesic']) }}
                    </div>
                    <div class="form-group col-md-6 mb-4">
                        {{ html()->label()->for('category')->text(__('messages.medicine.category') . ':')->attributes(['class' => 'form-label']) }}
                        <span class="required"></span>
                        {{ html()->text('category', null)->attributes(['class' => 'form-control', 'placeholder' => 'e.g., Analgesic', 'required']) }}
                    </div>
                    <div class="form-group col-md-6 mb-4">
                        {{ html()->label()->for('dosage')->text('Dosage:')->attributes(['class' => 'form-label']) }}
                        <span class="required"></span>
                        {{ html()->text('dosage', null)->attributes(['class' => 'form-control', 'placeholder' => 'e.g., 500 mg', 'required']) }}
                    </div>
                    <div class="form-group col-md-6 mb-4">
                        {{ html()->label()->for('uom')->text('Unit of Measure:')->attributes(['class' => 'form-label']) }}
                        <span class="required"></span>
                        {{ html()->text('uom', null)->attributes(['class' => 'form-control', 'placeholder' => 'tablet, capsule, vial', 'required']) }}
                    </div>
                    <div class="form-group col-md-6 mb-4">
                        {{ html()->label()->for('sku')->text('SKU:')->attributes(['class' => 'form-label']) }}
                        {{ html()->text('sku', null)->attributes(['class' => 'form-control', 'placeholder' => 'Optional stock code']) }}
                    </div>
                    <div class="form-group col-md-6 mb-4">
                        {{ html()->label()->for('reorder_level')->text('Reorder Level:')->attributes(['class' => 'form-label']) }}
                        {{ html()->number('reorder_level', null)->attributes(['class' => 'form-control', 'placeholder' => 'e.g., 10', 'min' => 0]) }}
                    </div>
                    <div class="form-group col-md-6 mb-4">
                        {{ html()->label()->for('initial_stock_quantity')->text('Initial Stock Quantity:')->attributes(['class' => 'form-label']) }}
                        {{ html()->number('initial_stock_quantity', 0)->attributes(['class' => 'form-control', 'min' => 0]) }}
                    </div>
                    <div class="form-group col-md-6 mb-4">
                        {{ html()->label()->for('batch_number')->text('Batch Number:')->attributes(['class' => 'form-label']) }}
                        {{ html()->text('batch_number', null)->attributes(['class' => 'form-control', 'placeholder' => 'Required when stock > 0']) }}
                    </div>
                    <div class="form-group col-md-6 mb-4">
                        {{ html()->label()->for('expiration_date')->text('Expiration Date:')->attributes(['class' => 'form-label']) }}
                        {{ html()->date('expiration_date', null)->attributes(['class' => 'form-control']) }}
                    </div>
                    <div class="form-group col-md-6 mb-4">
                        {{ html()->label()->for('description')->text(__('messages.medicine.description') . ':')->attributes(['class' => 'form-label']) }}
                        {{ html()->textarea('description')->text(html()->value('description', null))->attributes(['cols' => 50])->attributes(['class' => 'form-control', 'placeholder' => __('messages.medicine.description'), 'rows' => 3]) }}
                    </div>
                </div>
                {{ html()->hidden('quantity', 0)->attributes(['id' => 'createMedicineQuantity']) }}
                {{ html()->hidden('available_quantity', 0)->attributes(['id' => 'createMedicineAvailableQuantity']) }}
            </div>
            <div class="modal-footer">
                {{ html()->button(__('messages.common.save'), 'button')->attributes(['type' => 'submit', 'class' => 'btn btn-primary', 'id' => 'medicineSaveModalBtn']) }}
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('messages.common.cancel') }}</button>
            </div>
            {{ html()->closeModelForm() }}
        </div>
    </div>
</div>