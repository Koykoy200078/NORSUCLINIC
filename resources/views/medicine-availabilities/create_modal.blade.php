@php
$modalMedicines = ['medicines' => \App\Models\Medicine::all()->pluck('name', 'id')->toArray()];
$rawMedicineList = \App\Models\Medicine::all()->pluck('name', 'id')->toArray();
$modalMedicineList = [];
foreach ($rawMedicineList as $id => $name) {
$modalMedicineList[] = ['key' => $id, 'value' => $name];
}
@endphp
<div class="modal fade" id="add_stock_in_modal" tabindex="-1" role="dialog" aria-labelledby="addStockInModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-scrollable" role="document" style="max-width: 96vw; width: 96vw;">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addStockInModalLabel">{{ __('messages.medicine_availability.new_stock_in') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-danger d-none" id="stockInErrorsBox"></div>
                {{ html()->hidden('uniqueId', 2)->attributes(['id' => 'purchaseUniqueId']) }}
                {{ html()->hidden('associateMedicines', json_encode($modalMedicineList))->forgetAttribute('id')->attributes(['class' => 'associatePurchaseMedicines']) }}
                {{ html()->form('POST', route(getRouteNameByRole('stock-in.store')))->attribute('accept-charset', 'UTF-8')->attributes(['id' => 'purchaseMedicineFormId'])->open() }}
                <div class="row">
                    @include('medicine-availabilities.fields', ['medicines' => $modalMedicines, 'hideFormButtons' => true])
                </div>
                <div class="modal-footer px-0 mt-3">
                    {{ html()->input('submit', null, __('messages.common.save'))->attributes(['class' => 'btn btn-primary', 'id' => 'stockInSaveBtn']) }}
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('messages.common.cancel') }}</button>
                </div>
                {{ html()->closeModelForm() }}
            </div>
        </div>
    </div>
</div>
@include('medicine-availabilities.templates.templates')