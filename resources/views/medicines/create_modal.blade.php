@php
$modalGenerics = \App\Models\Generic::all()->pluck('name', 'id')->toArray();
$modalCategories = \App\Models\Category::all()->where('is_active', 1)->pluck('name', 'id')->toArray();
@endphp
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
                        {{ Form::label('generic_id', 'Generic Name:', ['class' => 'form-label']) }}
                        <span class="required"></span>
                        {{ Form::select('generic_id', $modalGenerics, null, ['class' => 'form-select', 'placeholder' => __('messages.common.select_generic'), 'id' => 'createMedicineGenericId']) }}
                    </div>
                    <div class="form-group col-md-6 mb-4">
                        {{ Form::label('name', 'Medicine Brand:', ['class' => 'form-label']) }}
                        <span class="required"></span>
                        {{ Form::text('name', null, ['class' => 'form-control', 'minlength' => 2, 'placeholder' => 'Medicine Brand', 'required', 'id' => 'createMedicineNameId']) }}
                    </div>
                    <div class="form-group col-md-6 mb-4">
                        {{ Form::label('category_id', __('messages.medicine.category') . ':', ['class' => 'form-label']) }}
                        <span class="required"></span>
                        {{ Form::select('category_id', $modalCategories, null, ['class' => 'form-select', 'placeholder' => 'Select a category', 'id' => 'createMedicineCategoryId']) }}
                    </div>
                    <div class="form-group col-md-6 mb-4">
                        {{ Form::label('minimum_stock_alert', 'Minimum Stock Alert:', ['class' => 'form-label']) }}
                        <span class="text-muted ms-1" style="font-size:0.85rem;">(Optional)</span>
                        {{ Form::number('minimum_stock_alert', null, ['class' => 'form-control', 'placeholder' => 'e.g., 10', 'min' => 0]) }}
                        <small class="form-text text-muted">Alert when stock reaches or falls below this quantity</small>
                    </div>
                    <div class="form-group col-md-6 mb-4">
                        {{ Form::label('stock_alert_percentage', 'Stock Alert Percentage:', ['class' => 'form-label']) }}
                        <span class="text-muted ms-1" style="font-size:0.85rem;">(Optional)</span>
                        {{ Form::number('stock_alert_percentage', null, ['class' => 'form-control', 'placeholder' => 'e.g., 20', 'min' => 0, 'max' => 100, 'step' => '0.01']) }}
                        <small class="form-text text-muted">Alert when available stock falls below this percentage of total stock</small>
                    </div>
                    <div class="form-group col-md-6 mb-4">
                        {{ Form::label('description', __('messages.medicine.description') . ':', ['class' => 'form-label']) }}
                        {{ Form::textarea('description', null, ['class' => 'form-control', 'placeholder' => __('messages.medicine.description'), 'rows' => 3]) }}
                    </div>
                </div>
                {{ Form::hidden('quantity', 0, ['id' => 'createMedicineQuantity']) }}
                {{ Form::hidden('available_quantity', 0, ['id' => 'createMedicineAvailableQuantity']) }}
                {{ Form::hidden('currency_symbol', getCurrentCurrency(), ['class' => 'currencySymbol']) }}
            </div>
            <div class="modal-footer">
                {{ Form::button(__('messages.common.save'), ['type' => 'submit', 'class' => 'btn btn-primary', 'id' => 'medicineSaveModalBtn']) }}
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('messages.common.cancel') }}</button>
            </div>
            {{ Form::close() }}
        </div>
    </div>
</div>