@extends('layouts.app')
@section('title')
{{ __('messages.medicine.medicine_generics') }}
@endsection
@section('page_css')
{{-- <link rel="stylesheet" href="{{ asset('assets/css/int-tel/css/intlTelInput.css') }}">--}}
@endsection
@section('header_toolbar')
<div class="container-fluid">
    <div class="d-flex flex-wrap align-items-center justify-content-between mb-7">
        <h1 class="mb-0 me-1">@yield('title')</h1>
        <a href="{{ 
                isRole('clinic_admin') ? route('generics.index') : 
                (isRole('staff') ? route('staff.generics.index') : 
                (isRole('doctor') ? route('doctors.generics.index') : route('generics.index'))) 
            }}"
            class="btn btn-outline-primary">{{ __('messages.common.back') }}</a>
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
            {{Form::hidden('utilsScript',asset('assets/js/int-tel/js/utils.min.js'),['class'=>'utilsScript'])}}
            {{Form::hidden('isEdit',false,['class'=>'isEdit'])}}

            <div class="card-body">
                {{ Form::open(['route' => 
                        isRole('clinic_admin') ? 'generics.store' : 
                        (isRole('staff') ? 'staff.generics.store' : 
                        (isRole('doctor') ? 'doctors.generics.store' : 'generics.store')), 
                        'id' => 'createGenericForm']) }}

                @include('generics.fields')

                {{ Form::close() }}
            </div>
        </div>
    </div>
</div>
@endsection
@section('scripts')
{{-- assets/js/generics/create-edit.js--}}
@endsection