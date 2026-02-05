@extends('layouts.app')
@section('title')
    {{__('messages.barangays')}}
@endsection
@section('content')
    <div class="container-fluid">
        @include('flash::message')
        <div class="d-flex flex-column">
            <livewire:barangay-table/>
        </div>
    </div>
    @include('barangays.create-modal')
    @include('barangays.edit-modal')
@endsection
