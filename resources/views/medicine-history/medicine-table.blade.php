{{ Form::hidden('medicine_bill', isset($medicineBill) ? $medicineBill->id : null, ['id' => 'medicineBillId']) }}

<div>
    <div class="row">
        <div class="form-group col-md-3 mb-5">
            {{ Form::label('patient_id', __('Patient Name') . ':', ['class' => 'form-label']) }}
            <span class="required"></span>
            {{ Form::select('patient_id', $patients, isset($medicineBill) ? $medicineBill->patient_id : null, ['class' => 'form-select w-80', 'required', 'id' => 'prescriptionPatientId', 'placeholder' => __('messages.medicine_bills.select_patient')]) }}
        </div>
        <div class="col-lg-3 col-md-4 col-sm-12 mb-5">
            {{ Form::label('bill_date', __('messages.medicine_bills.bill_date') . ':', ['class' => 'form-label']) }}
            <span class="required"></span>
            {{ Form::text('bill_date', null, ['class' => getLogInUser()->thememode ? 'bg-light form-control medicine_bill_date' : 'bg-white form-control medicine_bill_date', 'id' => 'medicine_bill_date', 'autocomplete' => 'off']) }}
        </div>
    </div>

    <div class="mb-md-0 mb-5 w-full">
        <label class="fw-bold text-muted py-3">{{ __('Notes') }}</label>
        {{ Form::textarea('note', null, ['class' => 'form-control w-100', 'rows' => 2, 'placeholder'=> __('Notes (Optional)')]) }}
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
                                {{ Form::select('category_id[]', $medicineCategories, isset($saleMedicine->medicine->medicineCategory->id) ? $saleMedicine->medicine->medicineCategory->id : null   , ['class' => 'form-select  select2Selector medicineBillCategoriesId', 'required', 'placeholder' => __('messages.common.select_category'), 'data-id' => '1', 'data-control' => 'select2']) }}
                            </td>
                            <td>
                                {{ Form::select('medicine[]', $medicines['medicines'], isset($saleMedicine->medicine->id) ? $saleMedicine->medicine->id:null, ['class' => 'form-select medicinePurchaseId purchaseMedicineId', 'placeholder' => __('messages.medicine_bills.select_medicine'), 'data-id' => 1, 'required']) }}
                            </td>
                            <td>
                                @php
                                $selectedDosage = old('dosage.' . $key, $saleMedicine->dosage ?? optional($saleMedicine->medicine)->dosage);
                                @endphp
                                {{ Form::select('dosage[]', !empty($selectedDosage) ? [$selectedDosage => $selectedDosage] : [], $selectedDosage, ['class' => 'form-select medicineBillDosage', 'placeholder' => 'Select Dosage/Strength', 'data-selected-dosage' => $selectedDosage, 'required']) }}
                            </td>
                            <td>
                                @php
                                $existingExpiryDate = old('expiry_date.' . $key, !empty($saleMedicine->expires_at) ? \Carbon\Carbon::parse($saleMedicine->expires_at)->format('Y-m-d') : null);
                                @endphp
                                {{ Form::text('expiry_date[]', $existingExpiryDate, ['class' => 'form-control medicineBillExpiryDate', 'id' => 'expiry_date1', 'placeholder' => __('Expiry Date'), 'readonly']) }}
                            </td>

                            {{ Form::hidden('quantity1[]', $saleMedicine->sale_quantity, ['class' => 'previous-quantity', 'id' => 'previous-sale-qty' . $key + 1]) }}
                            <td>
                                {{ Form::number('quantity[]', $saleMedicine->sale_quantity, ['class' => 'form-control medicineBill-quantity', 'id' => 'quantity' . $key + 1, 'min' => 1, 'required']) }}
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
                                {{ Form::select('category_id[]', $medicineCategories, null, ['class' => 'form-select medicineBillCategoriesId select2Selector','required','placeholder'=>__('messages.common.select_category'), 'data-id' => 1,'data-control'=>'select2']) }}
                            </td>
                            <td>
                                {{ Form::select('medicine[]', [], null,['class' => 'form-select medicinePurchaseId purchaseMedicineId','placeholder'=>__('messages.medicine_bills.select_medicine'),'id'=>'c1','data-control'=>'select2','data-id'=>1,'required']) }}
                            </td>
                            <td>
                                {{ Form::select('dosage[]', [], null, ['class' => 'form-select medicineBillDosage', 'placeholder' => 'Select Dosage/Strength', 'data-selected-dosage' => '', 'required']) }}
                            </td>
                            {{-- <td>
                        {{ Form::text('manufacturing_date[]', null, ['class' => 'form-control', 'id' => 'manufacturing_date1','required','placeholder'=>'Manufacturing Date']) }}
                            </td> --}}
                            <td>
                                {{ Form::text('expiry_date[]', null, ['class' => 'form-control medicineBillExpiryDate', 'id' => 'expiry_date1', 'placeholder' =>  __('Expiry Date'), 'readonly']) }}
                            </td>
                            {{-- <td>
                        {{ Form::number('purchase_price[]', '0.00', ['class' => 'form-control purchase-price', 'readonly', 'rows'=>1, 'id' => 'purchase_price1','required' ]) }}
                            </td> --}}
                            <td>
                                {{ Form::number('quantity[]', 1, ['class' => 'form-control medicineBill-quantity', 'id' => 'quantity1', 'min' => 1, 'required']) }}
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
                    {!! Form::submit(__('messages.common.save'), ['class' => 'btn btn-primary me-2', 'saveBtnPurchaseMedicne']) !!}
                    <a href="{{ isRole('clinic_admin') ? route('medicine-dispensing.index') : (isRole('staff') ? route('staff.medicine-dispensing.index') : route('doctors.medicine-dispensing.index')) }}" class="btn btn-secondary">{!! __('messages.common.cancel') !!}</a>
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