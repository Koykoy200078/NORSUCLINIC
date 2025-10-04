@extends('layouts.app')
@section('title')
{{ __('messages.purchase_medicine.purchase_medicine') }}
@endsection
@section('header_toolbar')
<div class="container-fluid">
    <div class="d-flex flex-wrap align-items-center justify-content-between mb-7">
        <h1 class="mb-0 me-1">{{ __('messages.purchase_medicine.purchase_medicine') }}</h1>
        <a href="{{ 
            isRole('clinic_admin') ? route('medicine-purchase.index') : 
            (isRole('staff') ? route('staff.medicine-purchase.index') : 
            (isRole('doctor') ? route('doctors.medicine-purchase.index') : route('medicine-purchase.index'))) 
        }}"
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
            </div>
        </div>
        <div class="card">
            <div class="card-body">
                {{Form::hidden('uniqueId',2,['id'=>'purchaseUniqueId'])}}
                {{Form::hidden('associateMedicines',json_encode($medicineList),['class'=>'associatePurchaseMedicines'])}}
                {{ Form::open(['route' => isRole('clinic_admin') ? 'medicine-purchase.store' : (isRole('staff') ? 'staff.medicine-purchase.store' : (isRole('doctor') ? 'doctors.medicine-purchase.store' : 'medicine-purchase.store')),'data-turbo'=>'false','id'=>'purchaseMedicineFormId']) }}
                <div class="row">
                    @include('purchase-medicines.fields')
                </div>
                {{ Form::close() }}
            </div>
            @include('purchase-medicines.templates.templates')
        </div>
    </div>
</div>
@endsection