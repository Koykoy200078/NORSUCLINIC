@extends('layouts.app')
@section('title')
{{__('messages.request.request')}}
@endsection
@section('content')
<div class="container-fluid">
    @include('flash::message')
    <div class="d-flex flex-column">
        <div class="mb-3">
            <a href="{{ route('request-documents.create') }}" class="btn btn-primary">
                Create New Request
            </a>
        </div>
        <livewire:request-document-table />
        This is Index of request
    </div>
</div>
@endsection