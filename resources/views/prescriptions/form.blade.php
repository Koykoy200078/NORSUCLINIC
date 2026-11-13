@extends('layouts.app')
@section('title')
{{ isset($prescription) ? __('messages.prescription.edit_prescription') : __('messages.prescription.new_prescription') }}
@endsection
@section('header_toolbar')
<x-page-toolbar>
    @php
    $backRoute = isRole('patient') ? 'patients.dashboard' : getRouteNameByRole('prescriptions.index');
    @endphp
    <a href="{{ route($backRoute) }}"
        class="btn btn-outline-primary mt-3">{{ __('messages.common.back') }}</a>
</x-page-toolbar>
@endsection
@section('content')
<div class="container-fluid">
    <div class="d-flex flex-column">
        <div class="row">
            <div class="col-12">
                @include('flash::message')
                @include('layouts.errors')
                @include('prescriptions.form_v2')
            </div>
        </div>
    </div>
</div>
@endsection
