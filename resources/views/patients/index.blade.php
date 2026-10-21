@extends('layouts.app')
@section('title')
{{ __('messages.patients') }}
@endsection
@section('content')
<div class="container-fluid">
    @include('flash::message')
    @if(isRole('clinic_admin') && canUseModule('patients'))
        <a class="btn btn-secondary mb-3 align-self-start" href="{{ route('patient-merge.index') }}">Merge duplicates</a>
    @endif
    <div class="d-flex flex-column">
        <livewire:patient-table :module="request()->query('module')" />
    </div>
</div>
@endsection
