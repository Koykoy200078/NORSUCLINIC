@extends('layouts.app')
@section('title')
{{ __('messages.prescription.new_prescription') }}
@endsection
@section('header_toolbar')
<div class="container-fluid">
    <div class="d-flex flex-wrap align-items-center justify-content-between mb-7">
        <h1 class="mb-0 me-1">@yield('title')</h1>
        @php
        $backRoute = isRole('doctor')
        ? 'doctors.prescriptions.index'
        : (isRole('staff') ? 'staff.prescriptions.index' : (isRole('patient') ? 'patients.dashboard' : 'prescriptions.index'));
        @endphp
        <a href="{{ route($backRoute) }}"
            class="btn btn-outline-primary mt-3">{{ __('messages.common.back') }}</a>
    </div>
</div>
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
{{-- <script src="{{mix('assets/js/prescriptions/create-edit.js')}}"></script>--}}