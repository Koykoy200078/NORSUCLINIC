{{ Form::hidden('currency_symbol', getCurrentCurrency(), ['class' => 'currencySymbol']) }}
<!-- Name Field -->
<div class="form-group col-md-6 mb-5">
    {{ Form::label('name', __('Medicine Name').(':'), ['class' => 'form-label']) }}
    <span class="required"></span>
    {{ Form::text('name', null, ['class' => 'form-control','minlength' => 2, 'placeholder' =>  __('Medicine Name'), 'id' => 'medicineNameId']) }}
</div>

<!-- Category Field -->
<div class="form-group col-md-6 mb-5">
    {{ Form::label('category_id', __('messages.medicine.category').(':'), ['class' => 'form-label']) }}
    <span class="required"></span>
    {{ Form::select('category_id', $categories, (isset($medicine)) ? $medicine->category_id : null, ['class' => 'form-select', 'placeholder' => __('Select a category'), 'id' => 'medicineCategoryId']) }}
</div>

<!-- Quantity Field -->

{{ Form::hidden('quantity', isset($medicine) ? $medicine->quantity : 0, ['class' => 'form-control', 'placeholder' =>  __('messages.item_stock.quantity'), 'id' => 'quantityId']) }}
<!-- Available Quantity Field -->
{{ Form::hidden('available_quantity',isset($medicine) ? $medicine->available_quantity : 0, ['class' => 'form-control', 'placeholder' =>  __('messages.issued_item.available_quantity'), 'id' => 'AvailableQuantityId']) }}

<!-- Name Field -->
<div class="form-group col-md-6 mb-5">
    {{ Form::label('brand_id', __('messages.medicine.brand').(':'), ['class' => 'form-label']) }}
    <span class="required"></span>
    {{ Form::select('brand_id', $brands,  (isset($medicine)) ? $medicine->brand_id : null, ['class' => 'form-select', 'placeholder' => __('messages.common.select_brand'), 'id' => 'medicineBrandId']) }}
</div>

<!-- Salt Composition Field -->
<!-- <div class="form-group col-md-6 mb-5">
    {{ Form::label('salt_composition', __('messages.medicine.salt_composition').(':'), ['class' => 'form-label']) }}
    <span
        class="required"></span>
    {{ Form::text('salt_composition', null, ['class' => 'form-control','placeholder' =>  __('messages.medicine.salt_composition'),'required']) }}
</div> -->

<!-- Buying Price Field -->
<div class="form-group col-md-6 mb-5">
    {{ Form::label('buying_price', __('messages.medicine.buying_price').(':'), ['class' => 'form-label']) }}
    <span class="required"></span>
    {{ Form::text('buying_price', isset($medicine) ? $medicine->buying_price : '', ['class' => 'form-control','placeholder' =>  __('messages.medicine.buying_price')]) }}
</div>

<!-- Minimum Stock Alert Field -->
<div class="form-group col-md-6 mb-5">
    {{ Form::label('minimum_stock_alert', __('Minimum Stock Alert').(':'), ['class' => 'form-label']) }}
    <span class="text-muted ms-1" style="font-size: 0.85rem;">(Optional)</span>
    {{ Form::number('minimum_stock_alert', isset($medicine) ? $medicine->minimum_stock_alert : null, ['class' => 'form-control','placeholder' =>  'e.g., 10', 'min' => 0]) }}
    <small class="form-text text-muted">Alert when stock reaches or falls below this quantity</small>
</div>

<!-- Stock Alert Percentage Field -->
<div class="form-group col-md-6 mb-5">
    {{ Form::label('stock_alert_percentage', __('Stock Alert Percentage').(':'), ['class' => 'form-label']) }}
    <span class="text-muted ms-1" style="font-size: 0.85rem;">(Optional)</span>
    {{ Form::number('stock_alert_percentage', isset($medicine) ? $medicine->stock_alert_percentage : null, ['class' => 'form-control','placeholder' =>  'e.g., 20', 'min' => 0, 'max' => 100, 'step' => '0.01']) }}
    <small class="form-text text-muted">Alert when available stock falls below this percentage of total stock</small>
</div>

<!-- Selling Price Field -->
<!-- <div class="form-group col-md-6 mb-5">
    {{ Form::label('selling_price', __('messages.medicine.selling_price').(':'), ['class' => 'form-label']) }}
    <span class="required"></span>
    {{ Form::text('selling_price', isset($medicine) ? $medicine->selling_price : '', ['class' => 'form-control','placeholder' =>  __('messages.medicine.selling_price')]) }}
</div> -->

<!-- Effect Field -->
<!-- <div class="form-group col-md-6 mb-5">
    {{ Form::label('side_effects', __('messages.medicine.side_effects').(':'), ['class' => 'form-label']) }}
    {{ Form::textarea('side_effects', null, ['class' => 'form-control','placeholder' =>  __('messages.medicine.side_effects'), 'rows'=>4]) }}
</div> -->

<!-- Effect Field -->
<div class="form-group col-md-6 mb-5">
    {{ Form::label('description', __('messages.medicine.description').(':'), ['class' => 'form-label']) }}
    {{ Form::textarea('description', null, ['class' => 'form-control', 'placeholder' =>  __('messages.medicine.description'), 'rows'=>4]) }}
</div>

<!-- Submit Field -->
<div class="d-flex justify-content-end">
    {{ Form::submit(__('messages.common.save'), ['class' => 'btn btn-primary me-2', 'id' => 'medicineSave']) }}
    <a href="{{ isRole('clinic_admin') ? route('medicines.index') : (isRole('staff') ? route('staff.medicines.index') : route('doctors.medicines.index')) }}"
        class="btn btn-secondary">{{ __('messages.common.cancel') }}</a>
</div>