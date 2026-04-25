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
        <div>
            <h1 class="mb-0 me-1">
                <i class="fa-solid {{ $isConsultationModule ? 'fa-notes-medical' : 'fa-file-medical' }} me-2" style="color:#2563a8;"></i>
                {{ $isConsultationModule ? 'Consultation Management' : 'Certificate Issuance' }}
            </h1>
            <p class="text-muted mb-0 mt-1" style="font-size:.88rem;">
                @if($isConsultationModule)
                Record walk-in visits, schedule consultations, and manage patient consultation history.
                @else
                Issue and manage medical certificates for patients.
                @endif
            </p>
        </div>
        <div class="text-end mt-4 mt-md-0">
            @if(!request('patient_id'))
            @php $createRoute = getRouteByRole('document-issuances.create'); @endphp
            <div class="d-flex gap-2">
                <a href="{{ $createRoute . ($isConsultationModule ? '?document_type=consultation_form&module=consultation' : '?document_type=medical_certificate&module=certificate') }}" class="btn btn-primary d-inline-flex align-items-center gap-2">
                    <i class="fa-solid fa-plus"></i>
                    {{ $isConsultationModule ? 'Record Walk-in / Schedule Visit' : 'Create Medical Certificate' }}
                </a>
                @if(!$isConsultationModule)
                <a href="{{ $createRoute . '?document_type=excuse_slip&module=certificate' }}" class="btn btn-outline-primary d-inline-flex align-items-center gap-2">
                    <i class="fa-solid fa-file-signature"></i>
                    Create Excuse Slip
                </a>
                @endif
            </div>
            @endif
        </div>
    </div>
</div>
@endsection

@section('content')
<div class="container-fluid">
    @include('flash::message')

    {{-- Patient-scoped breadcrumb & quick actions --}}
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
                <a href="{{ getRouteByRole('patients.index') }}">
                    <i class="fas fa-hospital-user"></i> {{ __('messages.patients') }}
                </a>
            </li>
            <li class="breadcrumb-item">
                <a href="{{ getRouteByRole('patients.showMyHistory', ['patient' => $patient->id]) }}">
                    {{ $patient->user->first_name }} {{ $patient->user->last_name }}
                </a>
            </li>
            <li class="breadcrumb-item active" aria-current="page">
                {{ $isConsultationModule ? 'Consultation Management' : 'Certificate Issuance' }}
            </li>
        </ol>
    </nav>
    <div class="mb-3 d-flex gap-2 flex-wrap">
        @if(!$isConsultationModule)
        <a href="{{ getRouteByRole('document-issuances.create', ['user_id' => $patient->user_id, 'document_type' => 'medical_certificate', 'module' => 'certificate']) }}" class="btn btn-success d-inline-flex align-items-center gap-2">
            <i class="fa-solid fa-file-medical"></i> Create Medical Certificate
        </a>
        @else
        <a href="{{ getRouteByRole('document-issuances.create', ['user_id' => $patient->user_id, 'document_type' => 'consultation_form', 'module' => 'consultation']) }}" class="btn btn-primary d-inline-flex align-items-center gap-2">
            <i class="fa-solid fa-notes-medical"></i> Record Walk-in / Schedule Visit
        </a>
        <a href="{{ getRouteByRole('prescriptions.create', ['patientId' => $patient->id]) }}" class="btn btn-info text-white d-inline-flex align-items-center gap-2">
            <i class="fa-solid fa-file-prescription"></i> Create Prescription
        </a>
        @endif
    </div>
    @endif
    @endif

    {{-- Doctor: scope incomplete-form alert to own consultations only --}}
    @if($isConsultationModule && isRole('doctor'))
    @php
    $doctorUserId = getLogInUser()->id;
    $incompleteCount = $doctorUserId
        ? \App\Models\DocumentIssuance::where('document_type', 'consultation_form')
            ->where('document_creator_id', $doctorUserId)
            ->where(function($query) {
                $query->whereRaw("TRIM(COALESCE(assessment, '')) = ''")
                      ->orWhereRaw("TRIM(COALESCE(plan, '')) = ''");
            })
            ->count()
        : 0;
    @endphp

    @if($incompleteCount > 0)
    <div class="alert alert-warning alert-dismissible fade show d-flex align-items-center gap-3" role="alert">
        <i class="fas fa-exclamation-triangle fa-2x flex-shrink-0"></i>
        <div>
            <strong>Attention Required!</strong><br>
            You have <strong>{{ $incompleteCount }}</strong> consultation form{{ $incompleteCount === 1 ? '' : 's' }}
            with incomplete <em>Assessment</em> or <em>Plan</em> that need{{ $incompleteCount === 1 ? 's' : '' }} your attention.
        </div>
        <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif
    @endif

    <div class="d-flex flex-column">
        <livewire:document-issuance-table :patientId="request('patient_id')" :module="$documentModule" />
    </div>
</div>
@endsection