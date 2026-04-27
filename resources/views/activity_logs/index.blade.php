@extends('layouts.app')

@section('title')
Report Generation
@endsection

@section('content')
<div class="container-fluid">
    @include('flash::message')
    
    <livewire:report-generation />
</div>

<style>
@media print {
    .nav-tabs, .btn, form, .card-toolbar {
        display: none !important;
    }
    .card {
        border: none !important;
        box-shadow: none !important;
    }
    .table {
        width: 100% !important;
    }
}
</style>
@endsection