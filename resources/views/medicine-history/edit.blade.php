@extends('layouts.app')
@section('title')
Edit Dispense Record
@endsection
@section('header_toolbar')
<div class="container-fluid">
    <div class="d-flex flex-wrap align-items-center justify-content-between mb-7">
        <h1 class="mb-0 me-1"></h1>
        <a href="{{ getRouteByRole('medicine-dispensing.index') }}"
            class="btn btn-outline-primary mt-3">{{ __('messages.common.back') }}</a>
    </div>
</div>
@endsection
@section('content')
<div class="container-fluid">
    <div class="d-flex flex-column">
        <div class="row">
            <div class="col-12">
                @include('layouts.errors')
                @include('flash::message')
                <div class="alert alert-danger d-none hide" id="validationErrorsBox"></div>
            </div>
        </div>
        <div class="card">
            <div class="card-body">
                {{html()->hidden('uniqueId', count($medicineBill->dispenseItems)+1)->attributes(['id'=>'medicineUniqueId'])}}
                {{html()->hidden('associateMedicines', json_encode($medicineList))->forgetAttribute('id')->attributes(['class'=>'associatePurchaseMedicines'])}}
                {{html()->hidden('medicineCategories', json_encode($medicineCategoriesList))->attributes(['id'=>'showMedicineCategoriesMedicineBill'])}}

                {{ html()->modelForm($medicineBill, 'patch', route(getRouteNameByRole('dispense-records.update'), [$medicineBill->id]))->attribute('accept-charset', 'UTF-8')->attributes(['id' => 'MedicinebillForm'])->open() }}
                <div class="row">
                    @include('medicine-history.medicine-table')
                </div>
                {{ html()->closeModelForm() }}
            </div>
            @include('medicine-history.templates.templates')
        </div>
    </div>
    {{-- </div>
    @include('bills.templates.templates')
    {{html()->hidden('billSaveUrl', route('bills.update', $bill->id))->attributes(['id'=>'editBillSaveUrl', 'class'=>'billSaveUrl'])}}
    {{html()->hidden('billUrl', route('bills.index'))->attributes(['id'=>'editBillUrl', 'class'=>'billUrl'])}}
    {{html()->hidden('associateMedicines', json_encode($associateMedicines))->attributes(['id'=>'editBillAssociateMedicines', 'class'=>'associateMedicines'])}}
    {{html()->hidden('uniqueId', $bill->billItems->count() + 1)->attributes(['id'=>'editBillUniqueId', 'class'=>'uniqueId'])}}
    {{html()->hidden('billDate', $bill->bill_date->format('Y-m-d h:i A'))->attributes(['id'=>'editBillDate', 'class'=>'billDate'])}}
    {{html()->hidden('patientAdmissionDetailUrl', url('patient-admission-details'))->attributes(['id'=>'editBillPatientAdmissionDetailUrl', 'class'=>'patientAdmissionDetailUrl'])}}
    {{html()->hidden('patientAdmissionId', $bill->patient_admission_id)->attributes(['id'=>'editBillPatientAdmissionId', 'class'=>'patientAdmissionId'])}}
    {{html()->hidden('billId', $bill->id)->attributes(['id'=>'editBillId', 'class'=>'billId'])}}
    {{html()->hidden('isEdit', true)->attributes(['id'=>'editBillIsEdit', 'class'=>'isEdit'])}} --}}
    @endsection
    {{-- assets/js/moment.min.js --}}
    {{-- assets/js/bills/edit.js --}}
    {{-- assets/js/bills/new.js --}}