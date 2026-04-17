@extends('layouts.app')

@section('title')
Prescription Management
@endsection

@section('content')
<div class="container-fluid">
    @include('flash::message')

    <div class="d-flex flex-column">
        <livewire:prescription-table />
    </div>
</div>
@endsection