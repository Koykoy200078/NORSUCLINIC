@extends('layouts.app')
@section('title')
Edit Dispense Record
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
</div>
@endsection
