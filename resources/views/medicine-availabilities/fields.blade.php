<div class="row">
    <div class="col-sm-12">
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h5 class="mb-0">Medicine Availability</h5>
                    <button type="button" class="btn btn-primary btn-sm add-medicine-btn-purchase">
                        <i class="fas fa-plus me-1"></i>{{ __('messages.common.add') }}
                    </button>
                </div>
                
                <div class="table-responsive">
                    <table class="table table-bordered align-middle" id="prescriptionMedicalTbl">
                        <thead class="table-light">
                            <tr>
                                <th>{{ __('messages.medicines') }}<span class="text-danger">*</span></th>
                                <th>{{ __('messages.medicine_availability.dosage') }}</th>
                                <th>{{ __('messages.medicine_availability.manufacturing_date') }}<span class="text-danger">*</span></th>
                                <th>{{ __('messages.medicine_availability.expiry_date') }}<span class="text-danger">*</span></th>
                                <th>{{ __('messages.medicine.quantity') }}<span class="text-danger">*</span></th>
                                <th class="text-center">Action</th>
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
                                    @php
                                        /**
                                         * Manufacturing Date Format Toggle Switch
                                         * 
                                         * Toggle between two date formats:
                                         * - OFF (Full Date): Y-m-d format (e.g., 2024-12-31)
                                         * - ON (Month Only): Y-m format (e.g., 2024-12)
                                         * 
                                         * Default: Full Date (Y-m-d) for new entries
                                         */
                                        $defaultManufacturingFormat = 'Y-m-d';
                                        $isManufacturingMonthOnly = ($defaultManufacturingFormat === 'Y-m');
                                    @endphp
                                    <div class="d-flex gap-2 align-items-center">
                                        {{ Form::text('manufacturing_date[]', null, ['class' => 'form-control purchaseMedicineManufacturingDate', 'id' => 'manufacturing_date1','required','placeholder'=>__('messages.medicine_availability.manufacturing_date')]) }}
                                        <div class="form-check form-switch mb-0" title="{{ $isManufacturingMonthOnly ? 'Month Only (Y-M)' : 'Full Date (Y-M-D)' }}">
                                            <input class="form-check-input manufacturing-format-toggle" 
                                                   type="checkbox" 
                                                   role="switch" 
                                                   id="manufacturing_format_toggle1" 
                                                   data-id="1"
                                                   {{ $isManufacturingMonthOnly ? 'checked' : '' }}>
                                            <input type="hidden" 
                                                   class="manufacturing-format-value" 
                                                   name="manufacturing_format[]" 
                                                   id="manufacturing_format1" 
                                                   value="{{ $defaultManufacturingFormat }}">
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    @php
                                        /**
                                         * Expiry Date Format Toggle Switch
                                         * 
                                         * Toggle between two date formats:
                                         * - OFF (Full Date): Y-m-d format (e.g., 2024-12-31)
                                         * - ON (Month Only): Y-m format (e.g., 2024-12)
                                         * 
                                         * Default: Full Date (Y-m-d) for new entries
                                         */
                                        $defaultFormat = 'Y-m-d';
                                        $isMonthOnly = ($defaultFormat === 'Y-m');
                                    @endphp
                                    <div class="d-flex gap-2 align-items-center">
                                        {{ Form::text('expiry_date[]', null, ['class' => 'form-control purchaseMedicineExpiryDate', 'id' => 'expiry_date1','required','placeholder'=>__('messages.medicine_availability.expiry_date')]) }}
                                        <div class="form-check form-switch mb-0" title="{{ $isMonthOnly ? 'Month Only (Y-M)' : 'Full Date (Y-M-D)' }}">
                                            <input class="form-check-input expiry-format-toggle" 
                                                   type="checkbox" 
                                                   role="switch" 
                                                   id="expiry_format_toggle1" 
                                                   data-id="1"
                                                   {{ $isMonthOnly ? 'checked' : '' }}>
                                            <input type="hidden" 
                                                   class="expiry-format-value" 
                                                   name="expiry_format[]" 
                                                   id="expiry_format1" 
                                                   value="{{ $defaultFormat }}">
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    {{ Form::number('quantity[]', 0, ['class' => 'form-control purchase-quantity' ,'id'=>'quantity1','required','min'=>'0']) }}
                                </td>

                                <td class="text-center">
                                    <button type="button" 
                                            class="btn btn-sm btn-danger delete-medicine-availability-item" 
                                            title="{{__('messages.common.delete')}}">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="mt-3 text-end">
            <a href="{!! 
                    isRole('clinic_admin') ? route('medicine-availability.index') : 
                    (isRole('staff') ? route('staff.medicine-availability.index') : 
                    (isRole('doctor') ? route('doctors.medicine-availability.index') : route('medicine-availability.index'))) 
                !!}" class="btn btn-secondary me-2">
                {{__('messages.common.cancel')}}
            </a>
            {{ Form::submit(__('messages.common.save'), ['class' => 'btn btn-primary']) }}
        </div>
    </div>
</div>