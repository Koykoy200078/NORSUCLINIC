@extends('layouts.app')
@section('title')
Medicine Dispensing Management
@endsection

@section('content')
<div class="container-fluid">
    @include('flash::message')
    @include('layouts.errors')
    @if ($errors->any())
    <div class="alert alert-danger">
        @foreach ($errors->all() as $error)
        <div>{{ $error }}</div>
        @endforeach
    </div>
    @endif

    @include('medicine-history.create_modal')
    <livewire:medicine-dispensing-screen />
</div>
@endsection