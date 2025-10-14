@extends('layouts.app')
@section('title')
{{ __('messages.medicine.new_medicine') }}
@endsection
@section('header_toolbar')
<div class="container-fluid">
    <div class="d-flex flex-wrap align-items-center justify-content-between mb-7">
        <h1 class="mb-0 me-1">{{ __('messages.medicine.new_medicine') }}</h1>
        <a href="{{ isRole('clinic_admin') ? route('medicines.index') : (isRole('staff') ? route('staff.medicines.index') : route('doctors.medicines.index')) }}"
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
            <div class="card-body">
                {{ Form::open(['route' => 
                    isRole('clinic_admin') ? 'medicines.store' : 
                    (isRole('staff') ? 'staff.medicines.store' : 
                    (isRole('doctor') ? 'doctors.medicines.store' : 'medicines.store')), 
                    'id' => 'createMedicine']) }}
                <div class="row">
                    @include('medicines.fields')
                </div>
                {{ Form::close() }}
            </div>
        </div>
    </div>
</div>
@endsection
@section('scripts')
{{-- assets/js/custom/input_price_format.js --}}
{{-- assets/js/medicines/new.js --}}
@endsection
