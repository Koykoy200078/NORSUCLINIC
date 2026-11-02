{{ html()->hidden('medicine_bill', isset($medicineBill) ? $medicineBill->id : null)->attributes(['id' => 'medicineBillId']) }}

<div>
    <div class="row">
        <div class="form-group col-md-3 mb-5">
            {{ html()->label()->for('patient_id')->text(__('Patient Name') . ':')->attributes(['class' => 'form-label']) }}
            <span class="required"></span>
            {{ html()->select('patient_id', $patients, isset($medicineBill) ? $medicineBill->patient_id : null)->placeholder(__('messages.medicine_bills.select_patient'))->attributes(['class' => 'form-select w-80', 'required', 'id' => 'prescriptionPatientId']) }}
        </div>
        <div class="col-lg-3 col-md-4 col-sm-12 mb-5">
            {{ html()->label()->for('bill_date')->text(__('messages.medicine_bills.bill_date') . ':')->attributes(['class' => 'form-label']) }}
            <span class="required"></span>
            {{ html()->text('bill_date', null)->attributes(['class' => getLogInUser()->thememode ? 'bg-light form-control medicine_bill_date' : 'bg-white form-control medicine_bill_date', 'id' => 'medicine_bill_date', 'autocomplete' => 'off']) }}
        </div>
    </div>

    <div class="mb-md-0 mb-5 w-full">
        <label class="fw-bold text-muted py-3">{{ __('Notes') }}</label>
        {{ html()->textarea('note')->text(html()->value('note', null))->forgetAttribute('id')->attributes(['cols' => 50])->attributes(['class' => 'form-control w-100', 'rows' => 2, 'placeholder'=> __('Notes (Optional)')]) }}
    </div>
</div>

<div class="row mt-4">
    <div class="col-sm-12">
        <div class="table-responsive-sm medicinePurchaseCreateTable">
            <div class="overflow-auto">
                <table class="table table-striped" id="prescriptionMedicalTbl">
                    <thead class="thead-dark">
                        <tr class="text-start text-muted fw-bolder fs-7 text-uppercase gs-0">
                            <th class="">{{ __('messages.medicine_categories') }}<span class="required"></span></th>
                            <th class="">{{ __('messages.medicines') }}<span class="required"></span></th>
                            <th class="">{{ __('messages.medicine.dosage') }}<span class="required"></span></th>
                            {{-- <th class="">{{ __('lot no.') }}<span class="required"></span></th> --}}
                            <th class="">{{ __('Expiry Date') }}</th>
                            {{-- <th class="">{{ __('Purchase Price') }}<span class="required"></span></th> --}}
                            <th class="">{{ __('messages.medicine.quantity') }}<span class="required"></span></th>
                            <th class="table__add-btn-heading text-center form-label fw-bolder text-gray-700 mb-3">
                                <a href="javascript:void(0)" type="button"
                                    class="btn btn-primary text-star add-medicine-btn-medicine-bill">
                                    {{ __('messages.common.add') }}
                                </a>
                            </th>
                        </tr>
                    </thead>
                    <tbody class="medicine-bill-container">
                        @if (isset($medicineBill))

                        @foreach ($medicineBill->saleMedicine as $key => $saleMedicine)
                        <tr>
                            <td>
                                {{ html()->select('category_id[]', $medicineCategories, isset($saleMedicine->medicine->medicineCategory->id) ? $saleMedicine->medicine->medicineCategory->id : null)->placeholder(__('messages.common.select_category'))->forgetAttribute('id')->attributes(['class' => 'form-select  select2Selector medicineBillCategoriesId', 'required', 'data-id' => '1', 'data-control' => 'select2']) }}
                            </td>
                            <td>
                                {{ html()->select('medicine[]', $medicines['medicines'], isset($saleMedicine->medicine->id) ? $saleMedicine->medicine->id:null)->placeholder(__('messages.medicine_bills.select_medicine'))->forgetAttribute('id')->attributes(['class' => 'form-select medicinePurchaseId purchaseMedicineId', 'data-id' => 1, 'required']) }}
                            </td>
                            <td>
                                @php
                                $selectedDosage = old('dosage.' . $key, $saleMedicine->dosage ?? optional($saleMedicine->medicine)->dosage);
                                @endphp
                                {{ html()->select('dosage[]', !empty($selectedDosage) ? [$selectedDosage => $selectedDosage] : [], $selectedDosage)->placeholder('Select Dosage/Strength')->forgetAttribute('id')->attributes(['class' => 'form-select medicineBillDosage', 'data-selected-dosage' => $selectedDosage, 'required']) }}
                            </td>
                            <td>
                                @php
                                $existingExpiryDate = old('expiry_date.' . $key, !empty($saleMedicine->expires_at) ? \Carbon\Carbon::parse($saleMedicine->expires_at)->format('Y-m-d') : null);
                                @endphp
                                {{ html()->text('expiry_date[]', $existingExpiryDate)->attributes(['class' => 'form-control medicineBillExpiryDate', 'id' => 'expiry_date1', 'placeholder' => __('Expiry Date'), 'readonly']) }}
                            </td>

                            {{ html()->hidden('quantity1[]', $saleMedicine->sale_quantity)->attributes(['class' => 'previous-quantity', 'id' => 'previous-sale-qty' . $key + 1]) }}
                            <td>
                                {{ html()->number('quantity[]', $saleMedicine->sale_quantity)->attributes(['class' => 'form-control medicineBill-quantity', 'id' => 'quantity' . $key + 1, 'min' => 1, 'required']) }}
                            </td>
                            <td class="text-center">
                                <a href="javascript:void(0)" title="{{ __('messages.common.delete') }}"
                                    class="delete-medicine-bill-item btn px-1 text-danger fs-3 pe-0">
                                    <i class="fa-solid fa-trash"></i>
                                </a>
                            </td>
                        </tr>
                        @endforeach
                        @else
                        <tr>
                            <td>
                                {{ html()->select('category_id[]', $medicineCategories, null)->placeholder(__('messages.common.select_category'))->forgetAttribute('id')->attributes(['class' => 'form-select medicineBillCategoriesId select2Selector', 'required', 'data-id' => 1, 'data-control'=>'select2']) }}
                            </td>
                            <td>
                                {{ html()->select('medicine[]', [], null)->placeholder(__('messages.medicine_bills.select_medicine'))->attributes(['class' => 'form-select medicinePurchaseId purchaseMedicineId', 'id'=>'c1', 'data-control'=>'select2', 'data-id'=>1, 'required']) }}
                            </td>
                            <td>
                                {{ html()->select('dosage[]', [], null)->placeholder('Select Dosage/Strength')->forgetAttribute('id')->attributes(['class' => 'form-select medicineBillDosage', 'data-selected-dosage' => '', 'required']) }}
                            </td>
                            {{-- <td>
                        {{ html()->text('manufacturing_date[]', null)->attributes(['class' => 'form-control', 'id' => 'manufacturing_date1', 'required', 'placeholder'=>'Manufacturing Date']) }}
                            </td> --}}
                            <td>
                                {{ html()->text('expiry_date[]', null)->attributes(['class' => 'form-control medicineBillExpiryDate', 'id' => 'expiry_date1', 'placeholder' =>  __('Expiry Date'), 'readonly']) }}
                            </td>
                            {{-- <td>
                        {{ html()->number('purchase_price[]', '0.00')->attributes(['class' => 'form-control purchase-price', 'readonly', 'rows'=>1, 'id' => 'purchase_price1', 'required']) }}
                            </td> --}}
                            <td>
                                {{ html()->number('quantity[]', 1)->attributes(['class' => 'form-control medicineBill-quantity', 'id' => 'quantity1', 'min' => 1, 'required']) }}
                            </td>
                            <td class="text-center">

                                <a href="javascript:void(0)" title="{{ __('messages.common.delete') }}"
                                    class="delete-medicine-bill-item btn px-1 text-danger fs-3 pe-0">
                                    <i class="fa-solid fa-trash"></i>
                                </a>
                            </td>
                        </tr>
                        @endif
                    </tbody>
                </table>
            </div>
            @if (!($hideFormButtons ?? false))
            <div class="row mt-5 justify-content-between">

                <div class="float-end mt-5">
                    {!! html()->input('submit', null, __('messages.common.save'))->attributes(['class' => 'btn btn-primary me-2', 'saveBtnPurchaseMedicne']) !!}
                    <a href="{{ getRouteByRole('medicine-dispensing.index') }}" class="btn btn-secondary">{!! __('messages.common.cancel') !!}</a>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        document.querySelectorAll('.medicineBill-quantity').forEach(function(input) {
            if (Number(input.value) < 1) {
                input.value = 1;
            }
        });
    });
</script>