@extends('layouts.app')
@section('title')
Medicine Inventory Tracking
@endsection
@section('content')
<div class="container-fluid">
    @include('flash::message')
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
