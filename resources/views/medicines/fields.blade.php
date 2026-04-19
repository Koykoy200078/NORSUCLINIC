{{ Form::hidden('currency_symbol', getCurrentCurrency(), ['class' => 'currencySymbol']) }}

@php
$prefillExpiryDate = isset($medicine) ? $medicine->earliest_expiry_date : null;
@endphp

{{ Form::hidden('quantity', isset($medicine) ? $medicine->quantity : 0, ['id' => 'quantityId']) }}
{{ Form::hidden('available_quantity', isset($medicine) ? $medicine->available_quantity : 0, ['id' => 'AvailableQuantityId']) }}

<div class="form-group col-md-6 mb-5">
    {{ Form::label('generic_name', 'Generic Name:', ['class' => 'form-label']) }}
    <span class="required"></span>
    {{ Form::text('generic_name', old('generic_name', isset($medicine) ? $medicine->generic_name : null), ['class' => 'form-control', 'placeholder' => 'e.g., Paracetamol', 'required']) }}
</div>

<div class="form-group col-md-6 mb-5">
    {{ Form::label('brand_name', 'Brand Name:', ['class' => 'form-label']) }}
    {{ Form::text('brand_name', old('brand_name', isset($medicine) ? $medicine->brand_name : null), ['class' => 'form-control', 'placeholder' => 'e.g., Biogesic']) }}
</div>

<div class="form-group col-md-6 mb-5">
    {{ Form::label('category', __('messages.medicine.category').(':'), ['class' => 'form-label']) }}
    <span class="required"></span>
    {{ Form::text('category', old('category', isset($medicine) ? ($medicine->category ?? $medicine->category_name) : null), ['class' => 'form-control', 'placeholder' => 'e.g., Analgesic', 'required']) }}
</div>

<div class="form-group col-md-3 mb-5">
    {{ Form::label('dosage', 'Dosage:', ['class' => 'form-label']) }}
    <span class="required"></span>
    {{ Form::text('dosage', old('dosage', isset($medicine) ? $medicine->dosage : null), ['class' => 'form-control', 'placeholder' => 'e.g., 500 mg', 'required']) }}
</div>

<div class="form-group col-md-3 mb-5">
    {{ Form::label('uom', 'Unit of Measure:', ['class' => 'form-label']) }}
    <span class="required"></span>
    {{ Form::text('uom', old('uom', isset($medicine) ? $medicine->uom : null), ['class' => 'form-control', 'placeholder' => 'tablet, capsule, vial', 'required']) }}
</div>

<div class="form-group col-md-4 mb-5">
    {{ Form::label('sku', 'SKU:', ['class' => 'form-label']) }}
    {{ Form::text('sku', old('sku', isset($medicine) ? $medicine->sku : null), ['class' => 'form-control', 'placeholder' => 'Optional stock code']) }}
</div>

<div class="form-group col-md-4 mb-5">
    {{ Form::label('reorder_level', 'Reorder Level:', ['class' => 'form-label']) }}
    {{ Form::number('reorder_level', old('reorder_level', isset($medicine) ? $medicine->reorder_level : null), ['class' => 'form-control', 'placeholder' => 'e.g., 10', 'min' => 0]) }}
</div>

<div class="form-group col-md-4 mb-5">
    {{ Form::label('stock_alert_percentage', __('Stock Alert Percentage').(':'), ['class' => 'form-label']) }}
    {{ Form::number('stock_alert_percentage', old('stock_alert_percentage', isset($medicine) ? $medicine->stock_alert_percentage : null), ['class' => 'form-control', 'placeholder' => 'e.g., 20', 'min' => 0, 'max' => 100, 'step' => '0.01']) }}
</div>

<div class="form-group col-md-12 mb-5">
    {{ Form::label('description', __('messages.medicine.description').(':'), ['class' => 'form-label']) }}
    {{ Form::textarea('description', old('description', isset($medicine) ? $medicine->description : null), ['class' => 'form-control', 'placeholder' => __('messages.medicine.description'), 'rows' => 3]) }}
</div>

<div class="col-12 mb-5">
    <div class="border rounded p-4">
        <h5 class="mb-4">Initial / Additional Batch Stock</h5>
        <div class="row">
            <div class="form-group col-md-4 mb-4">
                {{ Form::label('initial_stock_quantity', 'Stock Quantity:', ['class' => 'form-label']) }}
                {{ Form::number('initial_stock_quantity', old('initial_stock_quantity', 0), ['class' => 'form-control', 'min' => 0, 'placeholder' => '0']) }}
                <small class="text-muted">Set to zero if no stock movement is needed.</small>
            </div>
            <div class="form-group col-md-4 mb-4">
                {{ Form::label('batch_number', 'Batch Number:', ['class' => 'form-label']) }}
                {{ Form::text('batch_number', old('batch_number'), ['class' => 'form-control', 'placeholder' => 'Required if stock quantity > 0']) }}
            </div>
            <div class="form-group col-md-4 mb-4">
                {{ Form::label('unit_cost', 'Unit Cost:', ['class' => 'form-label']) }}
                {{ Form::number('unit_cost', old('unit_cost'), ['class' => 'form-control', 'step' => '0.01', 'min' => 0, 'placeholder' => 'Optional']) }}
            </div>
            <div class="form-group col-md-4 mb-4">
                {{ Form::label('manufacturing_date', 'Manufacturing Date:', ['class' => 'form-label']) }}
                {{ Form::date('manufacturing_date', old('manufacturing_date'), ['class' => 'form-control']) }}
            </div>
            <div class="form-group col-md-4 mb-4">
                {{ Form::label('expiration_date', 'Expiration Date:', ['class' => 'form-label']) }}
                {{ Form::date('expiration_date', old('expiration_date', $prefillExpiryDate), ['class' => 'form-control']) }}
            </div>
            <div class="form-group col-md-4 mb-4">
                {{ Form::label('supplier_name', 'Supplier Name:', ['class' => 'form-label']) }}
                {{ Form::text('supplier_name', old('supplier_name'), ['class' => 'form-control', 'placeholder' => 'Optional']) }}
            </div>
        </div>
    </div>
</div>

<!-- Submit Field -->
<div class="d-flex justify-content-end">
    {{ Form::submit(__('messages.common.save'), ['class' => 'btn btn-primary me-2', 'id' => 'medicineSave']) }}
    <a href="{{ isRole('clinic_admin') ? route('medicine-inventory.index') : (isRole('staff') ? route('staff.medicine-inventory.index') : route('doctors.medicine-inventory.index')) }}"
        class="btn btn-secondary">{{ __('messages.common.cancel') }}</a>
</div>