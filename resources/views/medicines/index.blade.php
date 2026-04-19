@extends('layouts.app')
@section('title')
Medicine Inventory Tracking
@endsection
@section('content')
<div class="container-fluid">
    @include('flash::message')
    <livewire:medicine-screen />
</div>

{{-- Modals & templates live outside the Livewire component to avoid
     DOMDocument multiple-root-element detection issues --}}
@include('medicines.show_modal')
@include('medicines.create_modal')
@include('medicine-availabilities.create_modal')
@endsection