@extends('layouts.app')
@section('title')
Dispense Record Details
@endsection
@section('header_toolbar')
<div class="container-fluid">
    <div class="d-flex flex-wrap align-items-center justify-content-between mb-7">
        <!-- <h1 class="mb-0 me-1">{{__('messages.medicine_bills.medicine_bill_details')}}</h1> -->
        <h1 class="mb-0 me-1"></h1>
        <div class="text-end mt-4 mt-md-0">
            <a class="btn btn-primary edit-btn"
                href="{{ getRouteByRole('dispense-records.edit', ['medicine_history' => $medicineBill->id]) }}">{{ __('messages.common.edit') }}</a>
            <a href="{{ getRouteByRole('medicine-dispensing.index') }}"
                class="btn btn-outline-primary ms-2">{{ __('messages.common.back') }}</a>
        </div>
    </div>
</div>
@endsection
@section('content')
<div class="container-fluid">
    <div class="d-flex flex-column">
        <div class="card">
            <div class="card-body">
                @include('medicine-history.show_fields')
            </div>
        </div>
    </div>
</div>
@endsection