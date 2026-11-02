@php
$categories = $categories ?? [];
$generics = $generics ?? [];
@endphp

<div id="add_new_medicine" class="modal fade" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <!-- Modal content-->
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title" id="exampleModalLabel">{{ __('messages.prescription.new_medicine') }}</h3>
                <button type="button" class="btn-close" data-bs-dismiss="modal"
                    aria-label="Close"></button>
            </div>
            {{ html()->form('POST', url()->current())->attribute('accept-charset', 'UTF-8')->attributes(['id' => 'createMedicineFromPrescription'])->open() }}
            @csrf
            <div class="modal-body">
                <div class="alert alert-danger d-none hide" id="medicinePrescriptionErrorBox"></div>
                <div class="row">
                    <!-- Name Field -->
                    <div class="form-group col-md-6 mb-5">
                        {{ html()->label()->for('name')->text(__('messages.medicine.medicine').(':'))->attributes(['class' => 'form-label']) }}
                        <span class="required"></span>
                        {{ html()->text('name', null)->attributes(['class' => 'form-control', 'minlength' => 2, 'id' => 'medicineNameId', 'placeholder'=>__('messages.medicine.medicine')]) }}
                    </div>

                    <!-- Category Field -->
                    <div class="form-group col-md-6 mb-5">
                        {{ html()->label()->for('category_id')->text(__('messages.medicine.category').(':'))->attributes(['class' => 'form-label']) }}
                        <span class="required"></span>
                        {{ html()->select('category_id', $categories, (isset($medicine)) ? $medicine->category_id : null)->placeholder(__('messages.common.select_category'))->attributes(['class' => 'form-select', 'id' => 'prescriptionMedicineCategoryId']) }}
                    </div>

                    <!-- Generic Field -->
                    <div class="form-group col-md-6 mb-5">
                        {{ html()->label()->for('generic_id')->text(__('messages.medicine.generic').(':'))->attributes(['class' => 'form-label']) }}
                        <span class="required"></span>
                        {{ html()->select('generic_id', $generics, (isset($medicine)) ? $medicine->generic_id : null)->placeholder(__('messages.common.select_generic'))->attributes(['class' => 'form-select', 'id' => 'prescriptionMedicineGenericId']) }}
                    </div>

                    <!-- Salt Composition Field -->
                    <div class="form-group col-md-6 mb-5">
                        {{ html()->label()->for('salt_composition')->text(__('messages.medicine.salt_composition').(':'))->attributes(['class' => 'form-label']) }}
                        <span
                            class="required"></span>
                        {{ html()->text('salt_composition', null)->attributes(['class' => 'form-control', 'required', 'placeholder'=>__('messages.medicine.salt_composition')]) }}
                    </div>

                    <!-- Buying Price Field -->
                    <div class="form-group col-md-6 mb-5">
                        {{ html()->label()->for('buying_price')->text(__('messages.medicine.buying_price').(':'))->attributes(['class' => 'form-label']) }}
                        <span class="required"></span>
                        {{ html()->text('buying_price', null)->attributes(['class' => 'form-control price-input', 'placeholder'=>__('messages.medicine.buying_price')]) }}
                    </div>

                    <!-- Selling Price Field -->
                    <div class="form-group col-md-6 mb-5">
                        {{ html()->label()->for('selling_price')->text(__('messages.medicine.selling_price').(':'))->attributes(['class' => 'form-label']) }}
                        <span class="required"></span>
                        {{ html()->text('selling_price', null)->attributes(['class' => 'form-control price-input', 'placeholder'=>__('messages.medicine.selling_price')]) }}
                    </div>

                    <!-- Effect Field -->
                    <div class="form-group col-md-12 mb-5">
                        {{ html()->label()->for('side_effects')->text(__('messages.medicine.side_effects').(':'))->attributes(['class' => 'form-label']) }}
                        {{ html()->textarea('side_effects')->text(html()->value('side_effects', null))->attributes(['cols' => 50])->attributes(['class' => 'form-control', 'rows'=>4, 'placeholder'=>__('messages.medicine.side_effects')]) }}
                    </div>

                    <!-- Effect Field -->
                    <div class="form-group col-md-12 mb-5">
                        {{ html()->label()->for('description')->text(__('messages.medicine.description').(':'))->attributes(['class' => 'form-label']) }}
                        {{ html()->textarea('description')->text(html()->value('description', null))->attributes(['cols' => 50])->attributes(['class' => 'form-control', 'rows'=>4, 'placeholder'=>__('messages.medicine.side_effects')]) }}
                    </div>
                </div>
            </div>
            <div class="modal-footer pt-0">
                {{ html()->button(__('messages.common.save'), 'button')->attributes(['type' => 'submit', 'class' => 'btn btn-primary m-0', 'id' => 'prescriptionMedicineSave', 'data-loading-text' => "<span class='spinner-border spinner-border-sm'></span> Processing..."]) }}
                <button type="button" class="btn btn-secondary"
                    data-bs-dismiss="modal">{{ __('messages.common.cancel') }}</button>
            </div>
            {{ html()->closeModelForm() }}
        </div>
    </div>
</div>