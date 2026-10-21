@extends('layouts.app')
@section('title')
Medicine Inventory Tracking
@endsection
@section('content')
<div class="container-fluid">
    @include('flash::message')
    @if(!isRole('doctor') && canUseModule('inventory'))
        <a class="btn btn-light-primary mb-3" href="{{ getRouteByRole('stock-control.index') }}">Stock disposal and physical count</a>
    @endif
    @if(isRole('clinic_admin'))
        @can('manage_medicines')<div class="mb-3 text-end"><a href="{{ route('inventory-merge.index') }}" class="btn btn-light-primary">Merge duplicates</a></div>@endcan
    @endif
    <livewire:medicine-screen />
</div>

{{-- Modals & templates live outside the Livewire component to avoid
     DOMDocument multiple-root-element detection issues --}}
@include('medicines.show_modal')
@unless(isRole('doctor'))
@include('medicines.create_modal')
@include('medicine-availabilities.create_modal')
@endunless
@endsection
