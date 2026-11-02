@php
$modalPatients = \App\Models\Patient::with('user:id,first_name,last_name')
->whereHas('user', fn($q) => $q->where('status', 1))
->get()->pluck('user.full_name', 'id')->sort();
$modalMedicineCategories = \App\Models\Category::where('is_active', 1)->pluck('name', 'id');
$modalMedicines = ['medicines' => \App\Models\Medicine::where('available_quantity', '>', 0)->pluck('name', 'id')->toArray()];
$rawMedicineList = \App\Models\Medicine::all()->pluck('name', 'id')->toArray();
$modalMedicineList = [];
foreach ($rawMedicineList as $id => $name) {
$modalMedicineList[] = ['key' => $id, 'value' => $name];
}
$rawCategoriesList = \App\Models\Category::where('is_active', 1)->pluck('name', 'id')->toArray();
$modalMedicineCategoriesList = [];
foreach ($rawCategoriesList as $id => $name) {
$modalMedicineCategoriesList[] = ['key' => $id, 'value' => $name];
}
@endphp
<div class="modal fade" id="add_dispense_modal" tabindex="-1" role="dialog" aria-labelledby="addDispenseModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-scrollable" role="document" style="max-width: 96vw; width: 96vw;">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addDispenseModalLabel">Add Dispense Record</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-danger d-none" id="dispenseErrorsBox"></div>
                {{ html()->hidden('uniqueId', 2)->attributes(['id' => 'medicineUniqueId']) }}
                {{ html()->hidden('associateMedicines', json_encode($modalMedicineList))->forgetAttribute('id')->attributes(['class' => 'associatePurchaseMedicines']) }}
                {{ html()->hidden('medicineCategories', json_encode($modalMedicineCategoriesList))->attributes(['id' => 'showMedicineCategoriesMedicineBill']) }}
                {{ html()->form('POST', route(getRouteNameByRole('dispense-records.store')))->attribute('accept-charset', 'UTF-8')->attributes(['id' => 'CreateMedicineBillForm'])->open() }}
                @include('medicine-history.medicine-table', [
                'patients' => $modalPatients,
                'medicines' => $modalMedicines,
                'medicineCategories' => $modalMedicineCategories,
                'hideFormButtons' => true,
                ])
                <div class="modal-footer px-0 mt-3">
                    {{ html()->input('submit', null, __('messages.common.save'))->attributes(['class' => 'btn btn-primary', 'id' => 'dispenseSaveBtn']) }}
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('messages.common.cancel') }}</button>
                </div>
                {{ html()->closeModelForm() }}
            </div>
        </div>
    </div>
</div>
@include('medicine-history.templates.templates')
@include('medicine-history.add_patient_modal')