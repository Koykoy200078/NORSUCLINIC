@extends('layouts.app')
@section('title')
{{ __('messages.medicine.new_medicine') }}
@endsection
@section('header_toolbar')
<div class="container-fluid">
    <div class="d-flex flex-wrap align-items-center justify-content-between mb-7">
        <h1 class="mb-0 me-1">{{ __('messages.medicine.new_medicine') }}</h1>
        <a href="{{ getRouteByRole('medicine-inventory.index') }}"
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
                {{ html()->form('POST', route(getRouteNameByRole('medicines.store')))->attribute('accept-charset', 'UTF-8')->attributes(['id' => 'createMedicine'])->open() }}
                <div class="row">
                    @include('medicines.fields')
                </div>
                {{ html()->closeModelForm() }}
            </div>
        </div>
    </div>
</div>
@endsection
@section('page_js')
{{-- assets/js/medicines/new.js --}}
@endsection
