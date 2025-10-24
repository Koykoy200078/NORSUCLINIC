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

    @if(isRole('doctor'))
    @php
    // Count incomplete consultation forms (missing assessment or plan)
    $incompleteCount = \App\Models\RequestDocuments::where('document_type', 'consultation_form')
    ->where(function($query) {
    $query->whereNull('assessment')
    ->orWhere('assessment', '')
    ->orWhereNull('plan')
    ->orWhere('plan', '');
    })
    ->count();
    @endphp

    @if($incompleteCount > 0)
    <div class="alert alert-warning alert-dismissible fade show d-flex align-items-center" role="alert">
        <i class="fas fa-exclamation-triangle fa-2x me-3"></i>
        <div>
            <strong>Attention Required!</strong><br>
            There {{ $incompleteCount === 1 ? 'is' : 'are' }} <strong>{{ $incompleteCount }}</strong> consultation form{{ $incompleteCount === 1 ? '' : 's' }} with incomplete Assessment or Plan fields that need{{ $incompleteCount === 1 ? 's' : '' }} your attention.
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif
    @endif

    <div class="d-flex flex-column">
        <livewire:request-document-table :patientId="request('patient_id')" />
    </div>
</div>
@endsection