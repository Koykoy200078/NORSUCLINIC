@extends('layouts.app')
@section('title')
{{ __('messages.medicine.medicines') }}
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
@include('categories.modal')
@include('categories.edit_modal')
@include('categories.templates.templates')
@include('generics.create_modal')
@include('medicine-history.create_modal')
@endsection