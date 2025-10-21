@extends('layouts.app')
@section('title')
{{ __('messages.purchase_medicine.purchase_medicine')}}
@endsection
@section('header_toolbar')
<div class="container-fluid">
    <div class="d-flex flex-wrap align-items-center justify-content-between mb-7">
        @include('layouts.errors')
        <h1 class="mb-0 me-1">{{__('messages.purchase_medicine.purchase_medicine_details')}}</h1>
        <div class="text-end mt-4 mt-md-0">
            <a href="{{ 
                isRole('clinic_admin') ? route('medicine-purchase.edit', $medicinePurchase->id) : 
                (isRole('staff') ? route('staff.medicine-purchase.edit', $medicinePurchase->id) : 
                (isRole('doctor') ? route('doctors.medicine-purchase.edit', $medicinePurchase->id) : route('medicine-purchase.edit', $medicinePurchase->id))) 
            }}" class="btn btn-primary">
                <i class="fas fa-edit me-2"></i>{{ __('messages.common.edit') }}
            </a>
            <a href="{{ 
                    isRole('clinic_admin') ? route('medicine-purchase.index') : 
                    (isRole('staff') ? route('staff.medicine-purchase.index') : 
                    (isRole('doctor') ? route('doctors.medicine-purchase.index') : route('medicine-purchase.index'))) 
                }}" class="btn btn-outline-primary ms-2">
                {{ __('messages.common.back') }}
            </a>
        </div>
    </div>
</div>
@endsection

@section('content')
<div class="container-fluid">
    <div class="d-flex flex-column">
        <div class="row">
            <div class="col-12">
                @include('flash::message')
            </div>
        </div>
        @include('purchase-medicines.show_fields')
    </div>
</div>
@endsection