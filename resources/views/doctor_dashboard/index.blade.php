@extends('layouts.app')
@section('title')
{{__('messages.dashboard')}}
@endsection
@section('content')
<div class="container-fluid">
    <div class="d-flex flex-column">
        <div class="row">
            <div class="col-xl-12">
                <livewire:doctor-dashboard-table />
            </div>
            <div class="col-xl-12">
                <livewire:doctor-dashboard-sidebar-table />
            </div>
        </div>
    </div>
</div>
@include('doctor_dashboard.templates.templates')
@endsection