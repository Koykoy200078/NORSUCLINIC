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
                        @foreach($medicinePurchase->purchasedMedcines as $index => $purchasedMedicine)
                        @php
                        $uniqueId = $index + 1;
                        // Detect date format
                        $dateFormat = 'Y-m-d';
                        if (strlen($purchasedMedicine->expiry_date) === 7 && substr_count($purchasedMedicine->expiry_date, '-') === 1) {
                        $dateFormat = 'Y-m';
                        }
                        // Calculate purchase price from amount and quantity
                        $purchasePrice = ($purchasedMedicine->quantity > 0) ? ($purchasedMedicine->amount / $purchasedMedicine->quantity) : 0.00;
                        @endphp
                        <tr>
                            <td>
                                {{ Form::hidden('purchased_medicine_id[]', $purchasedMedicine->id, ['id' => 'purchased_medicine_id'.$uniqueId]) }}
                                {{ Form::select('medicine[]', $medicines['medicines'], $purchasedMedicine->medicine_id, ['class' => 'form-select purchaseMedicineId','placeholder'=>__('messages.medicine_bills.select_medicine'),'id'=>'medicineChooseId'.$uniqueId,'data-control'=>'select2','data-id'=>$uniqueId,'required']) }}
                            </td>
                            <td>
                                {{ Form::text('dosage[]', $purchasedMedicine->dosage, ['class' => 'form-control', 'id' => 'dosage'.$uniqueId,'placeholder'=>'e.g. 500mg, 200mg']) }}
                            </td>
                            <td>
                                {{ Form::text('manufacturing_date[]', $purchasedMedicine->manufacturing_date, ['class' => 'form-control purchaseMedicineManufacturingDate', 'id' => 'manufacturing_date'.$uniqueId,'required','placeholder'=>__('messages.purchase_medicine.manufacturing_date')]) }}
                            </td>
                            <td>
                                {{ Form::text('expiry_date[]', $purchasedMedicine->expiry_date, ['class' => 'form-control purchaseMedicineExpiryDate', 'id' => 'expiry_date'.$uniqueId,'placeholder'=>__('messages.purchase_medicine.expiry_date')]) }}
                            </td>
                            <td>
                                <select class="form-select expiry-format-selector" data-id="{{$uniqueId}}" id="expiry_format{{$uniqueId}}">
                                    <option value="Y-m-d" {{ $dateFormat === 'Y-m-d' ? 'selected' : '' }}>Full Date (Y-M-D)</option>
                                    <option value="Y-m" {{ $dateFormat === 'Y-m' ? 'selected' : '' }}>Month Only (Y-M)</option>
                                </select>
                            </td>
                            <td>
                                {{ Form::number('quantity[]', $purchasedMedicine->quantity, ['class' => 'form-control purchase-quantity' ,'id'=>'quantity'.$uniqueId,'required','min'=>'1']) }}
                            </td>

                            <!-- Hidden fields for purchase price, tax, and amount -->
                            {{ Form::hidden('purchase_price[]', number_format($purchasePrice, 2, '.', ''), ['class' => 'purchase-price', 'id' => 'purchase_price'.$uniqueId]) }}
                            {{ Form::hidden('tax_medicine[]', $purchasedMedicine->tax ?? 0, ['class' => 'purchase-tax', 'id'=>'tax'.$uniqueId]) }}
                            {{ Form::hidden('amount[]', $purchasedMedicine->amount, ['class' => 'purchase-amount','id'=>'amount'.$uniqueId]) }}

                            <td class="text-center">
                                <a href="javascript:void(0)" title="{{__('messages.common.delete')}}"
                                    class="delete-purchase-medicine-item btn px-1 text-danger fs-3 pe-0">
                                    <i class="fa-solid fa-trash"></i>
                                </a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
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