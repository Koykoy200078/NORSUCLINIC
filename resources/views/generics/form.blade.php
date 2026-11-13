@extends('layouts.app')
@section('title')
{{ __('messages.medicine.medicine_generics') }}
@endsection
@section('page_css')
@endsection
@section('header_toolbar')
<x-page-toolbar>
    <a href="{{
            getRouteByRole('generics.index')
        }}"
        class="btn btn-outline-primary">{{ __('messages.common.back') }}</a>
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
            {{-- One page for adding and editing (re-audit 2026-11-13, DRY): the edit form goes to the update route of the
                 panel it was opened in (it used to post to the admin route, which refuses a Staff (Nurse)). --}}
            {{html()->hidden('isEdit', isset($generic))->forgetAttribute('id')->attributes(['class'=>'isEdit'])}}

            <div class="card-body">
                @isset($generic)
                {{ html()->modelForm($generic, 'patch', route(getRouteNameByRole('generics.update'), [$generic->id]))->attribute('accept-charset', 'UTF-8')->attributes(['id' => 'editGenericForm'])->open() }}
                @else
                {{ html()->form('POST', route(getRouteNameByRole('generics.store')))->attribute('accept-charset', 'UTF-8')->attributes(['id' => 'createGenericForm'])->open() }}
                @endisset

                @include('generics.fields')

                {{ html()->closeModelForm() }}
            </div>
        </div>
    </div>
</div>
@endsection
@section('page_js')
{{-- assets/js/generics/create-edit.js--}}
@endsection
