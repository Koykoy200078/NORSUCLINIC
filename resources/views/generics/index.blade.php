@extends('layouts.app')
@section('title')
{{ __('messages.medicine.medicine_generics') }}
@endsection
@section('css')
{{-- <link rel="stylesheet" href="{{ asset('assets/css/sub-header.css') }}">--}}
@endsection
@section('content')
<div class="container-fluid">
    {{Form::hidden('genericUrl',
            isRole('clinic_admin') ? route('generics.index') :
            (isRole('staff') ? route('staff.generics.index') :
            (isRole('doctor') ? route('doctors.generics.index') : route('generics.index'))),
        ['id'=>'indexGenericUrl'])}}
    {{ Form::hidden('medicine_generic', __('messages.medicine.medicine'). ' ' . __('messages.medicine.generic') , ['id' => 'medicineGeneric']) }}
    <div class="d-flex flex-column">
        @include('flash::message')
        <livewire:medicine-generic-table />
    </div>
</div>
@endsection
@section('scripts')
@endsection