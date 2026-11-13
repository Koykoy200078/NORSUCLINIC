@extends('layouts.app')
@section('title')
Add Dispense Record
@endsection
@section('header_toolbar')
<x-page-toolbar>
    <a href="{{ getRouteByRole('medicine-dispensing.index') }}"
        class="btn btn-outline-primary mt-3">{{ __('messages.common.back') }}</a>
</x-page-toolbar>
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
@endsection
