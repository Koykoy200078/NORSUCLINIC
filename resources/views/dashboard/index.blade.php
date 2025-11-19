@extends('layouts.app')
@section('title')
{{ __('messages.dashboard') }}
@endsection
@section('content')
<div class="container-fluid">
    <div class="d-flex flex-column">
        <div class="row">
            <div class="col-xl-4">
                <livewire:dashboard />
            </div>

            <div class="col-xxl-12">
                <livewire:admin-dashBoard-table />
            </div>
        </div>
    </div>
</div>
@include('dashboard.templates.templates')
@endsection