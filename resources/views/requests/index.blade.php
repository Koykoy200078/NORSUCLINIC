@extends('layouts.app')
@section('title')
{{ __('messages.request.request') }}
@endsection
@section('content')
<div class="container-fluid">
    @include('flash::message')
    <div class="d-flex flex-column">
        <livewire:request-document-table />
    </div>
</div>
@endsection