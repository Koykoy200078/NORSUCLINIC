@extends('layouts.app')
@section('title')
{{ __('Staff Dashboard') }}
@endsection
@section('content')
<div class="container-fluid">
    <div class="d-flex flex-column">
        <div class="row">
            <div class="col-xl-12">
                <livewire:staff-dashboard />
            </div>

            <div class="col-xxl-12">
                <livewire:staff-dash-board-table />
            </div>
        </div>
    </div>
</div>
@include('dashboard.templates.templates')
@endsection