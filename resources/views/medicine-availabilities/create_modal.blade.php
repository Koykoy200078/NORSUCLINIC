@php
$modalMedicines = ['medicines' => \App\Models\Medicine::all()->pluck('name', 'id')->toArray()];
$rawMedicineList = \App\Models\Medicine::all()->pluck('name', 'id')->toArray();
$modalMedicineList = [];
foreach ($rawMedicineList as $id => $name) {
$modalMedicineList[] = ['key' => $id, 'value' => $name];
}
@endphp
<div class="modal fade" id="add_stock_in_modal" tabindex="-1" role="dialog" aria-labelledby="addStockInModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addStockInModalLabel">{{ __('messages.medicine_availability.new_stock_in') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-danger d-none" id="stockInErrorsBox"></div>
                {{ Form::hidden('uniqueId', 2, ['id' => 'purchaseUniqueId']) }}
                {{ Form::hidden('associateMedicines', json_encode($modalMedicineList), ['class' => 'associatePurchaseMedicines']) }}
                {{ Form::open(['route' => isRole('clinic_admin') ? 'stock-in.store' : (isRole('staff') ? 'staff.stock-in.store' : (isRole('doctor') ? 'doctors.stock-in.store' : 'stock-in.store')), 'data-turbo' => 'false', 'id' => 'purchaseMedicineFormId']) }}
                <div class="row">
                    @include('medicine-availabilities.fields', ['medicines' => $modalMedicines, 'hideFormButtons' => true])
                </div>
                <div class="modal-footer px-0 mt-3">
                    {{ Form::submit(__('messages.common.save'), ['class' => 'btn btn-primary', 'id' => 'stockInSaveBtn']) }}
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('messages.common.cancel') }}</button>
                </div>
                {{ Form::close() }}
            </div>
        </div>
    </div>
</div>
@include('medicine-availabilities.templates.templates')