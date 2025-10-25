<div class="row">
    <div class="col-sm-12">
        <div class="table-responsive-sm medicinePurchaseCreateTable">
            <div class="overflow-auto">
                <table class="table table-striped" id="prescriptionMedicalTbl">
                    <thead class="thead-dark">
                        <tr class="text-start text-muted fw-bolder fs-7 text-uppercase gs-0">
                            <th class="">{{ __('messages.medicines') }}<span class="required"></span></th>
                            <th class="">{{ __('messages.purchase_medicine.dosage') }}</th>
                            <th class="">{{ __('messages.purchase_medicine.manufacturing_date') }}<span class="required"></span></th>
                            <th class="">{{ __('messages.purchase_medicine.expiry_date') }}</th>
                            <th class="">Date Format</th>
                            <th class="">{{ __('messages.medicine.quantity') }}<span class="required"></span></th>
                            <th class="table__add-btn-heading text-center form-label fw-bolder text-gray-700 mb-3">
                                <a href="javascript:void(0)" type="button"
                                    class="btn btn-primary text-star add-medicine-btn-purchase">
                                    {{ __('messages.common.add') }}
                                </a>
                            </th>
                        </tr>
                    </thead>
                    <tbody class="prescription-medicine-container">
                        <tr>
                            <td>
                                {{ Form::select('medicine[]', $medicines['medicines'], null,['class' => 'form-select purchaseMedicineId','placeholder'=>__('messages.medicine_bills.select_medicine'),'id'=>'medicineChooseId1','data-control'=>'select2','data-id'=>1,'required']) }}
                            </td>
                            <td>
                                {{ Form::text('dosage[]', null, ['class' => 'form-control', 'id' => 'dosage1','placeholder'=>'e.g. 500mg, 200mg']) }}
                            </td>
                            <td>
                                {{ Form::text('manufacturing_date[]', null, ['class' => 'form-control purchaseMedicineManufacturingDate', 'id' => 'manufacturing_date1','required','placeholder'=>__('messages.purchase_medicine.manufacturing_date')]) }}
                            </td>
                            <td>
                                {{ Form::text('expiry_date[]', null, ['class' => 'form-control purchaseMedicineExpiryDate', 'id' => 'expiry_date1','placeholder'=>__('messages.purchase_medicine.expiry_date')]) }}
                            </td>
                            <td>
                                <select class="form-select expiry-format-selector" data-id="1" id="expiry_format1" name="expiry_format[]">
                                    <option value="Y-m-d" selected>Full Date (Y-M-D)</option>
                                    <option value="Y-m">Month Only (Y-M)</option>
                                </select>
                            </td>
                            <td>
                                {{ Form::number('quantity[]', 0, ['class' => 'form-control purchase-quantity' ,'id'=>'quantity1','required']) }}
                            </td>

                            <!-- Hidden fields for purchase price, tax, and amount -->
                            {{ Form::hidden('purchase_price[]', '0.00', ['class' => 'purchase-price', 'id' => 'purchase_price1']) }}
                            {{ Form::hidden('tax_medicine[]', 0, ['class' => 'purchase-tax', 'id'=>'tax1']) }}
                            {{ Form::hidden('amount[]','0.00', ['class' => 'purchase-amount','id'=>'amount1']) }}

                            <td class="text-center">
                                <a href="javascript:void(0)" title="{{__('messages.common.delete')}}"
                                    class="delete-purchase-medicine-item btn px-1 text-danger fs-3 pe-0">
                                    <i class="fa-solid fa-trash"></i>
                                </a>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Parent-level purchase medicine fields (hidden - populated by JavaScript) -->
            {{ Form::hidden('total', 0, ['id' => 'total']) }}
            {{ Form::hidden('tax', 0, ['id' => 'purchaseTaxId']) }}
            {{ Form::hidden('discount', 0, ['class' => 'purchase-discount', 'id' => 'discountAmount']) }}
            {{ Form::hidden('net_amount', 0, ['id' => 'netAmount']) }}
            {{ Form::hidden('payment_type', 2, ['id' => 'paymentMode']) }}
            {{ Form::hidden('payment_note', '', ['id' => 'paymentNote']) }}
            {{ Form::hidden('note', '', ['id' => 'purchaseNote']) }}

            <div class="row mt-5 justify-content-between">

                <div class="float-end mt-5">
                    {!! Form::submit(__('messages.common.save'), ['class' => 'btn btn-primary me-2','saveBtnPurchaseMedicne' ]) !!}
                    <a href="{!! 
                            isRole('clinic_admin') ? route('medicine-purchase.index') : 
                            (isRole('staff') ? route('staff.medicine-purchase.index') : 
                            (isRole('doctor') ? route('doctors.medicine-purchase.index') : route('medicine-purchase.index'))) 
                        !!}" class="btn btn-secondary">{!! __('messages.common.cancel') !!}</a>
                </div>

            </div>
        </div>
    </div>
</div>