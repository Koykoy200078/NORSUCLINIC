@extends('layouts.app')
@section('title')
@php
$documentModule = request('module', request('document_type') === 'medical_certificate' ? 'certificate' : 'consultation');
$isConsultationModule = $documentModule !== 'certificate';
@endphp
@if($isConsultationModule)
Consultation Management
@else
Certificate Issuance
@endif
@endsection

@section('header_toolbar')
<div class="container-fluid">
    <div class="d-flex flex-wrap align-items-center justify-content-between mb-7">
        <h1 class="mb-0 me-1">{{ $isConsultationModule ? 'Consultation Management' : 'Certificate Issuance' }}</h1>
        <div class="text-end mt-4 mt-md-0">
            @if(!request('patient_id'))
            @php
            $createRoute = isRole('clinic_admin') ? route('document-issuances.create') :
            (isRole('staff') ? route('staff.document-issuances.create') : route('doctors.document-issuances.create'));

            $createUrl = $createRoute . ($isConsultationModule
            ? '?document_type=consultation_form&module=consultation'
            : '?document_type=medical_certificate&module=certificate');
            @endphp
            <a href="{{ $createUrl }}" class="btn btn-primary">
                <i class="fa-solid fa-plus"></i>
                {{ $isConsultationModule ? 'Record Walk-in / Schedule Visit' : 'Create Medical Certificate' }}
            </a>
            @endif
        </div>
    </div>
</div>
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
                {{ $patient->user->first_name }} {{ $patient->user->last_name }} - {{ $isConsultationModule ? 'Consultation Management' : 'Certificate Issuance' }}
            </li>
        </ol>
    </nav>
    <div class="mb-3">
        @if(!$isConsultationModule)
        <a href="{{ 
            isRole('clinic_admin') ? route('document-issuances.create', ['user_id' => $patient->user_id, 'document_type' => 'medical_certificate', 'module' => 'certificate']) : 
            (isRole('staff') ? route('staff.document-issuances.create', ['user_id' => $patient->user_id, 'document_type' => 'medical_certificate', 'module' => 'certificate']) : 
            (isRole('doctor') ? route('doctors.document-issuances.create', ['user_id' => $patient->user_id, 'document_type' => 'medical_certificate', 'module' => 'certificate']) : '#'))
        }}" class="btn btn-success me-2">
            <i class="fa-solid fa-file-medical"></i> Create Medical Certificate
        </a>
        @else
        <a href="{{ 
            isRole('clinic_admin') ? route('document-issuances.create', ['user_id' => $patient->user_id, 'document_type' => 'consultation_form', 'module' => 'consultation']) : 
            (isRole('staff') ? route('staff.document-issuances.create', ['user_id' => $patient->user_id, 'document_type' => 'consultation_form', 'module' => 'consultation']) : 
            (isRole('doctor') ? route('doctors.document-issuances.create', ['user_id' => $patient->user_id, 'document_type' => 'consultation_form', 'module' => 'consultation']) : '#'))
        }}" class="btn btn-primary">
            <i class="fa-solid fa-notes-medical"></i> Record Walk-in / Schedule Visit
        </a>
        @endif
    </div>
    @endif
    @endif

    @if($isConsultationModule && isRole('doctor'))
    @php
    // Count incomplete consultation forms (missing assessment or plan)
    $incompleteCount = \App\Models\DocumentIssuance::where('document_type', 'consultation_form')
    ->where(function($query) {
    $query->whereRaw("TRIM(COALESCE(assessment, '')) = ''")
    ->orWhereRaw("TRIM(COALESCE(plan, '')) = ''");
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
        <livewire:document-issuance-table :patientId="request('patient_id')" :module="$documentModule" />
    </div>
</div>
@endsection