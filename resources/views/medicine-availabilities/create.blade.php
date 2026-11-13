@extends('layouts.app')
@section('title')
{{ __('messages.medicine_availability.medicine_availability') }}
@endsection
@section('css')
    <link rel="stylesheet" href="{{ asset('css/flatpickr-month-select.css') }}">
@endsection
@section('header_toolbar')
<x-page-toolbar :title="__('messages.medicine_availability.medicine_availability')">
    <a href="{{
        getRouteByRole('stock-in.index')
    }}"
        class="btn btn-outline-primary mt-3">{{ __('messages.common.back') }}</a>
</x-page-toolbar>
@endsection
@section('content')
<div class="container-fluid">
    <div class="d-flex flex-column">
        <div class="row">
            <div class="col-12">
                @include('layouts.errors')
            </div>
        </div>
        <div class="card">
            <div class="card-body">
                {{html()->hidden('uniqueId', 2)->attributes(['id'=>'purchaseUniqueId'])}}
                {{html()->hidden('associateMedicines', json_encode($medicineList))->forgetAttribute('id')->attributes(['class'=>'associatePurchaseMedicines'])}}
                {{ html()->form('POST', route(getRouteNameByRole('stock-in.store')))->attribute('accept-charset', 'UTF-8')->attributes(['id'=>'purchaseMedicineFormId'])->open() }}
                <div class="row">
                    @include('medicine-availabilities.fields')
                </div>
                {{ html()->closeModelForm() }}
            </div>
            @include('medicine-availabilities.templates.templates')
        </div>
    </div>
</div>
@endsection