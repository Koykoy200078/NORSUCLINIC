@extends('layouts.app')
@section('title')
Add Dispense Record
@endsection
@section('header_toolbar')
<div class="container-fluid">
    <div class="d-flex flex-wrap align-items-center justify-content-between mb-7">
        <h1 class="mb-0 me-1">@yield('title')</h1>
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
            </div>
        </div>
        <div class="card">
            <div class="card-body">
                {{html()->hidden('uniqueId', 2)->attributes(['id'=>'medicineUniqueId'])}}
                {{html()->hidden('associateMedicines', json_encode($medicineList))->forgetAttribute('id')->attributes(['class'=>'associatePurchaseMedicines'])}}
                {{html()->hidden('medicineCategories', json_encode($medicineCategoriesList))->attributes(['id'=>'showMedicineCategoriesMedicineBill'])}}

                {{ html()->form('POST', route(getRouteNameByRole('dispense-records.store')))->attribute('accept-charset', 'UTF-8')->attributes(['id' => 'CreateMedicineBillForm'])->open() }}
                @include('medicine-history.medicine-table')
                {{ html()->closeModelForm() }}
            </div>
        </div>
    </div>
</div>
@include('medicine-history.templates.templates')
@include('medicine-history.add_patient_modal')
{{-- {{html()->hidden('billSaveUrl', route('bills.store'))->attributes(['id'=>'createBillSaveUrl', 'class'=>'billSaveUrl'])}}
{{html()->hidden('billUrl', route('bills.index'))->attributes(['id'=>'createBillUrl', 'class'=>'billUrl'])}}
{{html()->hidden('associateMedicines', json_encode($associateMedicines))->attributes(['id'=>'createBillAssociateMedicines', 'class'=>'associateMedicines'])}}
{{html()->hidden('uniqueId', 2)->attributes(['id'=>'createBillUniqueId', 'class'=>'uniqueId'])}}
{{html()->hidden('patientAdmissionDetailUrl', url('patient-admission-details'))->attributes(['id'=>'createBillPatientAdmissionDetailUrl', 'class'=>'patientAdmissionDetailUrl'])}}
{{html()->hidden('isCreate', true)->attributes(['id'=>'createBillIsCreate', 'class'=>'isCreate'])}}
{{html()->hidden('isEdit', false)->attributes(['id'=>'createBillIsEdit', 'class'=>'isEdit'])}} --}}
@endsection
{{-- assets/js/moment.min.js  --}}
{{-- assets/js/bills/new.js --}}
