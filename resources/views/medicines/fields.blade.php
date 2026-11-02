@php
$prefillExpiryDate = isset($medicine) ? $medicine->earliest_expiry_date : null;
@endphp

{{ html()->hidden('quantity', isset($medicine) ? $medicine->quantity : 0)->attributes(['id' => 'quantityId']) }}
{{ html()->hidden('available_quantity', isset($medicine) ? $medicine->available_quantity : 0)->attributes(['id' => 'AvailableQuantityId']) }}

<div class="form-group col-md-6 mb-5">
    {{ html()->label()->for('generic_name')->text('Generic Name:')->attributes(['class' => 'form-label']) }}
    <span class="required"></span>
    {{ html()->text('generic_name', old('generic_name', isset($medicine) ? $medicine->generic_name : null))->attributes(['class' => 'form-control', 'placeholder' => 'e.g., Paracetamol', 'required']) }}
</div>

<div class="form-group col-md-6 mb-5">
    {{ html()->label()->for('brand_name')->text('Brand Name:')->attributes(['class' => 'form-label']) }}
    {{ html()->text('brand_name', old('brand_name', isset($medicine) ? $medicine->brand_name : null))->attributes(['class' => 'form-control', 'placeholder' => 'e.g., Biogesic']) }}
</div>

<div class="form-group col-md-6 mb-5">
    {{ html()->label()->for('category')->text(__('messages.medicine.category').(':'))->attributes(['class' => 'form-label']) }}
    <span class="required"></span>
    {{ html()->text('category', old('category', isset($medicine) ? ($medicine->category ?? $medicine->category_name) : null))->attributes(['class' => 'form-control', 'placeholder' => 'e.g., Analgesic', 'required']) }}
</div>

<div class="form-group col-md-3 mb-5">
    {{ html()->label()->for('dosage')->text('Dosage:')->attributes(['class' => 'form-label']) }}
    <span class="required"></span>
    {{ html()->text('dosage', old('dosage', isset($medicine) ? $medicine->dosage : null))->attributes(['class' => 'form-control', 'placeholder' => 'e.g., 500 mg', 'required']) }}
</div>

<div class="form-group col-md-3 mb-5">
    {{ html()->label()->for('uom')->text('Unit of Measure:')->attributes(['class' => 'form-label']) }}
    <span class="required"></span>
    {{ html()->text('uom', old('uom', isset($medicine) ? $medicine->uom : null))->attributes(['class' => 'form-control', 'placeholder' => 'tablet, capsule, vial', 'required']) }}
</div>

<div class="form-group col-md-4 mb-5">
    {{ html()->label()->for('sku')->text('SKU:')->attributes(['class' => 'form-label']) }}
    {{ html()->text('sku', old('sku', isset($medicine) ? $medicine->sku : null))->attributes(['class' => 'form-control', 'placeholder' => 'Optional stock code']) }}
</div>

<div class="form-group col-md-4 mb-5">
    {{ html()->label()->for('reorder_level')->text('Reorder Level:')->attributes(['class' => 'form-label']) }}
    {{ html()->number('reorder_level', old('reorder_level', isset($medicine) ? $medicine->reorder_level : null))->attributes(['class' => 'form-control', 'placeholder' => 'e.g., 10', 'min' => 0]) }}
</div>

<div class="form-group col-md-4 mb-5">
    {{ html()->label()->for('stock_alert_percentage')->text(__('Stock Alert Percentage').(':'))->attributes(['class' => 'form-label']) }}
    {{ html()->number('stock_alert_percentage', old('stock_alert_percentage', isset($medicine) ? $medicine->stock_alert_percentage : null))->attributes(['class' => 'form-control', 'placeholder' => 'e.g., 20', 'min' => 0, 'max' => 100, 'step' => '0.01']) }}
</div>

<div class="form-group col-md-12 mb-5">
    {{ html()->label()->for('description')->text(__('messages.medicine.description').(':'))->attributes(['class' => 'form-label']) }}
    {{ html()->textarea('description')->text(html()->value('description', old('description', isset($medicine) ? $medicine->description : null)))->attributes(['cols' => 50])->attributes(['class' => 'form-control', 'placeholder' => __('messages.medicine.description'), 'rows' => 3]) }}
</div>

<div class="col-12 mb-5">
    <div class="border rounded p-4">
        <h5 class="mb-4">Initial / Additional Batch Stock</h5>
        <div class="row">
            <div class="form-group col-md-4 mb-4">
                {{ html()->label()->for('initial_stock_quantity')->text('Stock Quantity:')->attributes(['class' => 'form-label']) }}
                {{ html()->number('initial_stock_quantity', old('initial_stock_quantity', 0))->attributes(['class' => 'form-control', 'min' => 0, 'placeholder' => '0']) }}
                <small class="text-muted">Set to zero if no stock movement is needed.</small>
            </div>
            <div class="form-group col-md-4 mb-4">
                {{ html()->label()->for('batch_number')->text('Batch Number:')->attributes(['class' => 'form-label']) }}
                {{ html()->text('batch_number', old('batch_number'))->attributes(['class' => 'form-control', 'placeholder' => 'Required if stock quantity > 0']) }}
            </div>
            <div class="form-group col-md-4 mb-4">
                {{ html()->label()->for('manufacturing_date')->text('Manufacturing Date:')->attributes(['class' => 'form-label']) }}
                {{ html()->date('manufacturing_date', old('manufacturing_date'))->attributes(['class' => 'form-control']) }}
            </div>
            <div class="form-group col-md-4 mb-4">
                {{ html()->label()->for('expiration_date')->text('Expiration Date:')->attributes(['class' => 'form-label']) }}
                {{ html()->date('expiration_date', old('expiration_date', $prefillExpiryDate))->attributes(['class' => 'form-control']) }}
            </div>
            <div class="form-group col-md-4 mb-4">
                {{ html()->label()->for('supplier_name')->text('Supplier Name:')->attributes(['class' => 'form-label']) }}
                {{ html()->text('supplier_name', old('supplier_name'))->attributes(['class' => 'form-control', 'placeholder' => 'Optional']) }}
            </div>
        </div>
    </div>
</div>

<!-- Submit Field -->
<div class="d-flex justify-content-end">
    {{ html()->input('submit', null, __('messages.common.save'))->attributes(['class' => 'btn btn-primary me-2', 'id' => 'medicineSave']) }}
    <a href="{{ getRouteByRole('medicine-inventory.index') }}"
        class="btn btn-secondary">{{ __('messages.common.cancel') }}</a>
</div>