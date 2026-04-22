@extends('layouts.app')
@section('title')
{{ __('messages.patients') }}
@endsection
@section('content')
<div class="container-fluid">
    @include('flash::message')
    <div class="d-flex flex-column">
        <livewire:patient-table :module="request()->query('module')" />
    </div>
</div>
@endsection