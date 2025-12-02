@extends('layouts.app')
@section('title')
{{ __('messages.medicine_availability.medicine_availability') }}
@endsection
@section('css')
    <link rel="stylesheet" href="{{ asset('css/flatpickr-month-select.css') }}">
@endsection
@section('header_toolbar')
<div class="container-fluid">
    <div class="d-flex flex-wrap align-items-center justify-content-between mb-7">
        <h1 class="mb-0 me-1">{{ __('messages.medicine_availability.medicine_availability') }}</h1>
        <a href="{{ 
            isRole('clinic_admin') ? route('medicine-availability.index') : 
            (isRole('staff') ? route('staff.medicine-availability.index') : 
            (isRole('doctor') ? route('doctors.medicine-availability.index') : route('medicine-availability.index'))) 
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
                {{ Form::open(['route' => isRole('clinic_admin') ? 'medicine-availability.store' : (isRole('staff') ? 'staff.medicine-availability.store' : (isRole('doctor') ? 'doctors.medicine-availability.store' : 'medicine-availability.store')),'data-turbo'=>'false','id'=>'purchaseMedicineFormId']) }}
                <div class="row">
                    @include('medicine-availabilities.fields')
                </div>
                {{ Form::close() }}
            </div>
            @include('medicine-availabilities.templates.templates')
        </div>
    </div>
</div>
@endsection