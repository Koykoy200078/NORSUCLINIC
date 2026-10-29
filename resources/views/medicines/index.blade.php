@extends('layouts.app')
@section('title')
Medicine Inventory Tracking
@endsection
@section('content')
<div class="container-fluid">
    @include('flash::message')
    @php
        $canCountStock = ! isRole('doctor') && canUseModule('inventory');
        $canMergeDuplicates = isRole('clinic_admin') && auth()->user()->can('manage_medicines');
        $canViewCountSheets = canUseModule('inventory');
    @endphp
    @if($canCountStock || $canMergeDuplicates || $canViewCountSheets)
        <div class="d-flex flex-wrap justify-content-end gap-2 mb-3" id="inventory-tools">
            @if($canViewCountSheets)
                <a class="btn btn-outline-primary" href="{{ getRouteByRole('inventory-counts.index') }}"><i class="fas fa-list-check me-2"></i>Quarterly count sheets</a>
            @endif
            @if($canCountStock)
                <a class="btn btn-outline-primary" href="{{ getRouteByRole('stock-control.index') }}"><i class="fas fa-clipboard-check me-2"></i>Stock disposal and physical count</a>
            @endif
            @if($canMergeDuplicates)
                <a class="btn btn-outline-primary" href="{{ route('inventory-merge.index') }}"><i class="fas fa-code-merge me-2"></i>Merge duplicates</a>
            @endif
        </div>
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
