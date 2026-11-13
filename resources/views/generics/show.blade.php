@extends('layouts.app')
@section('title')
{{ __('messages.medicine.medicine_generics')}}
@endsection
@section('header_toolbar')
<x-page-toolbar :title="__('messages.medicine.medicine_generics_details')">
    <div class="text-end mt-4 mt-md-0">
        @unless(isRole('doctor'))
<a class="btn btn-primary edit-btn"
            href="{{
                   getRouteByRole('generics.edit', ['generic' => $generic->id])
               }}">{{ __('messages.common.edit') }}</a>
@endunless
        <a href="{{
                getRouteByRole('generics.index')
            }}"
            class="btn btn-outline-primary ms-2">{{ __('messages.common.back') }}</a>
    </div>
</x-page-toolbar>
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