@extends('layouts.app')
@section('title')
{{ __('messages.medicine.medicine_generics') }}
@endsection
@section('css')
{{-- <link rel="stylesheet" href="{{ asset('assets/css/sub-header.css') }}">--}}
@endsection
@section('content')
<div class="container-fluid">
    @if(isRole('clinic_admin'))
        @can('manage_medicines')<div class="mb-3 text-end"><a href="{{ route('inventory-merge.index', ['kind' => 'generic']) }}" class="btn btn-outline-primary">Merge duplicates</a></div>@endcan
    @endif
    {{Form::hidden('genericUrl',
            getRouteByRole('generics.index'),
        ['id'=>'indexGenericUrl'])}}
    {{ Form::hidden('medicine_generic', __('messages.medicine.medicine'). ' ' . __('messages.medicine.generic') , ['id' => 'medicineGeneric']) }}
    <div class="d-flex flex-column">
        @include('flash::message')
        <livewire:medicine-generic-table />
    </div>
</div>
@endsection
@section('page_js')
@endsection
