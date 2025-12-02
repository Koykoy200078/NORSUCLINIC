@extends('layouts.app')
@section('title')
{{ __('messages.medicine.medicine_generics')}}
@endsection
@section('header_toolbar')
<div class="container-fluid">
    <div class="d-flex flex-wrap align-items-center justify-content-between mb-7">
        <h1 class="mb-0 me-1">{{__('messages.medicine.medicine_generics_details')}}</h1>
        <div class="text-end mt-4 mt-md-0">
            <a class="btn btn-primary edit-btn"
                href="{{ 
                       isRole('clinic_admin') ? route('generics.edit', ['generic' => $generic->id]) : 
                       (isRole('staff') ? route('staff.generics.edit', ['generic' => $generic->id]) : 
                       (isRole('doctor') ? route('doctors.generics.edit', ['generic' => $generic->id]) : route('generics.edit', ['generic' => $generic->id]))) 
                   }}">{{ __('messages.common.edit') }}</a>
            <a href="{{ 
                    isRole('clinic_admin') ? route('generics.index') : 
                    (isRole('staff') ? route('staff.generics.index') : 
                    (isRole('doctor') ? route('doctors.generics.index') : route('generics.index'))) 
                }}"
                class="btn btn-outline-primary ms-2">{{ __('messages.common.back') }}</a>
        </div>
    </div>
</div>
@endsection
@section('content')
<div class="container-fluid">
    <div class="d-flex flex-column livewire-table">
        <div class="row">
            <div class="col-12">
                @include('flash::message')
            </div>
        </div>
        @include('generics.show_fields')
    </div>
</div>
@endsection