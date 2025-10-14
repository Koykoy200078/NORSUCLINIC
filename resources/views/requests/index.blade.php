@extends('layouts.app')
@section('title')
@if(request('patient_id'))
Patient Data
@else
{{ (isRole('clinic_admin') || isRole('staff')) ? __('messages.request.patient_data') : __('messages.request.request') }}
@endif
@endsection
@section('content')
<div class="container-fluid">
    @include('flash::message')

    @if(request('patient_id'))
    @php
    $patient = \App\Models\Patient::whereHas('user', function($q) {
    $q->where('id', request('patient_id'));
    })->with('user')->first();
    @endphp

    @if($patient)
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb">
            <li class="breadcrumb-item">
                <a href="{{ isRole('clinic_admin') ? route('patients.index') : 
                               (isRole('staff') ? route('staff.patients.index') : 
                               (isRole('doctor') ? route('doctors.patients.index') : '#')) }}">
                    <i class="fas fa-hospital-user"></i> {{ __('messages.patients') }}
                </a>
            </li>
            <li class="breadcrumb-item active" aria-current="page">
                {{ $patient->user->first_name }} {{ $patient->user->last_name }} - Consultation Forms
            </li>
        </ol>
    </nav>
    @endif
    @endif

    <div class="d-flex flex-column">
        <livewire:request-document-table :patientId="request('patient_id')" />
    </div>
</div>
@endsection
