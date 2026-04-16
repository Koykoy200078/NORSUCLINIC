<div class="row">
    <div class="col-sm-12">
        <div class="table-responsive-sm medicineAvailabilityCreateTable">
            <div class="overflow-auto">
                <table class="table table-striped" id="prescriptionMedicalTbl">
                    <thead class="thead-dark">
                        <tr class="text-start text-muted fw-bolder fs-7 text-uppercase gs-0">
                            <th class="">{{ __('messages.medicines') }}<span class="required"></span></th>
                            <th class="">{{ __('messages.medicine_availability.dosage') }}</th>
                            <th class="">{{ __('messages.medicine_availability.manufacturing_date') }}<span class="required"></span></th>
                            <th class="">{{ __('messages.medicine_availability.expiry_date') }}<span class="required"></span></th>
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
                        @foreach($medicineAvailability->purchasedMedcines as $index => $purchasedMedicine)
                        @php
                        $uniqueId = $index + 1;
                        
                        /**
                         * Dynamic Date Format Detection
                         * 
                         * Analyzes existing expiry_date and manufacturing_date to determine their formats:
                         * 
                         * Switch Cases:
                         * - Case 1: Length=7 with 1 dash → 'Y-m' format (e.g., "2024-12")
                         * - Case 2: Length=10 with 2 dashes → 'Y-m-d' format (e.g., "2024-12-31")
                         * - Default: Fallback to 'Y-m-d' for safety
                         * 
                         * This ensures the format selector matches the stored date format
                         * when editing existing medicine availability records.
                         */
                        $expiryDateFormat = 'Y-m-d'; // Default format for expiry
                        $manufacturingDateFormat = 'Y-m-d'; // Default format for manufacturing
                        
                        if (!empty($purchasedMedicine->expiry_date)) {
                            // Determine expiry format by analyzing the date string structure
                            switch (true) {
                                case (strlen($purchasedMedicine->expiry_date) === 7 && substr_count($purchasedMedicine->expiry_date, '-') === 1):
                                    $expiryDateFormat = 'Y-m';
                                    break;
                                case (strlen($purchasedMedicine->expiry_date) === 10 && substr_count($purchasedMedicine->expiry_date, '-') === 2):
                                    $expiryDateFormat = 'Y-m-d';
                                    break;
                                default:
                                    $expiryDateFormat = 'Y-m-d';
                            }
                        }
                        
                        if (!empty($purchasedMedicine->manufacturing_date)) {
                            // Determine manufacturing format by analyzing the date string structure
                            switch (true) {
                                case (strlen($purchasedMedicine->manufacturing_date) === 7 && substr_count($purchasedMedicine->manufacturing_date, '-') === 1):
                                    $manufacturingDateFormat = 'Y-m';
                                    break;
                                case (strlen($purchasedMedicine->manufacturing_date) === 10 && substr_count($purchasedMedicine->manufacturing_date, '-') === 2):
                                    $manufacturingDateFormat = 'Y-m-d';
                                    break;
                                default:
                                    $manufacturingDateFormat = 'Y-m-d';
                            }
                        }
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
                                {{-- 
                                    Manufacturing Date Format Toggle Switch with Dynamic Detection
                                    
                                    Toggle switch state is determined by $manufacturingDateFormat
                                    which is auto-detected from existing manufacturing_date value.
                                --}}
                                @php
                                    $isManufacturingMonthOnly = ($manufacturingDateFormat === 'Y-m');
                                @endphp
                                <div class="d-flex gap-2 align-items-center">
                                    {{ Form::text('manufacturing_date[]', $purchasedMedicine->manufacturing_date, ['class' => 'form-control purchaseMedicineManufacturingDate', 'id' => 'manufacturing_date'.$uniqueId,'required','placeholder'=>__('messages.medicine_availability.manufacturing_date')]) }}
                                    <div class="form-check form-switch mb-0">
                                        <input class="form-check-input manufacturing-format-toggle" 
                                               type="checkbox" 
                                               role="switch" 
                                               id="manufacturing_format_toggle{{$uniqueId}}" 
                                               data-id="{{$uniqueId}}"
                                               {{ $isManufacturingMonthOnly ? 'checked' : '' }}
                                               title="{{ $isManufacturingMonthOnly ? 'Month Only (Y-M)' : 'Full Date (Y-M-D)' }}">
                                        <input type="hidden" 
                                               class="manufacturing-format-value" 
                                               name="manufacturing_format[]" 
                                               id="manufacturing_format{{$uniqueId}}" 
                                               value="{{ $manufacturingDateFormat }}">
                                    </div>
                                </div>
                            </td>
                            <td>
                                {{-- 
                                    Format Toggle Switch with Dynamic Detection
                                    
                                    Toggle switch state is determined by $dateFormat
                                    which is auto-detected from existing expiry_date value.
                                    
                                    Benefits:
                                    - Visual toggle interface
                                    - Maintains data integrity when editing
                                    - Shows current format clearly
                                    - Easy to change format
                                --}}
                                @php
                                    $isExpiryMonthOnly = ($expiryDateFormat === 'Y-m');
                                @endphp
                                <div class="d-flex gap-2 align-items-center">
                                    {{ Form::text('expiry_date[]', $purchasedMedicine->expiry_date, ['class' => 'form-control purchaseMedicineExpiryDate', 'id' => 'expiry_date'.$uniqueId,'required','placeholder'=>__('messages.medicine_availability.expiry_date')]) }}
                                    <div class="form-check form-switch mb-0">
                                        <input class="form-check-input expiry-format-toggle" 
                                               type="checkbox" 
                                               role="switch" 
                                               id="expiry_format_toggle{{$uniqueId}}" 
                                               data-id="{{$uniqueId}}"
                                               {{ $isExpiryMonthOnly ? 'checked' : '' }}
                                               title="{{ $isExpiryMonthOnly ? 'Month Only (Y-M)' : 'Full Date (Y-M-D)' }}">
                                        <input type="hidden" 
                                               class="expiry-format-value" 
                                               name="expiry_format[]" 
                                               id="expiry_format{{$uniqueId}}" 
                                               value="{{ $expiryDateFormat }}">
                                    </div>
                                </div>
                            </td>
                            <td>
                                {{ Form::number('quantity[]', $purchasedMedicine->quantity, ['class' => 'form-control purchase-quantity' ,'id'=>'quantity'.$uniqueId,'required','min'=>'1']) }}
                            </td>

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
                            isRole('clinic_admin') ? route('stock-in.index') : 
                            (isRole('staff') ? route('staff.stock-in.index') : 
                            (isRole('doctor') ? route('doctors.stock-in.index') : route('stock-in.index'))) 
                        !!}" class="btn btn-secondary">{!! __('messages.common.cancel') !!}</a>
                </div>

            </div>
        </div>
    </div>
</div>