@extends('layouts.app')
@section('title')
@if(request('patient_id'))
Document Issuances
@else
Document & Certificate Issuance
@endif
@endsection

@section('header_toolbar')
<div class="container-fluid">
    <div class="d-flex flex-wrap align-items-center justify-content-between mb-7">
        <h1 class="mb-0 me-1">Document & Certificate Issuance</h1>
        <div class="text-end mt-4 mt-md-0">
            @if(!request('patient_id'))
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createDocumentModal">
                <i class="fa-solid fa-plus"></i> Create Document
            </button>
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
                {{ $patient->user->first_name }} {{ $patient->user->last_name }} - Document Issuances
            </li>
        </ol>
    </nav>
    <div class="mb-3">
        <a href="{{ 
            isRole('clinic_admin') ? route('document-issuances.create', ['user_id' => $patient->user_id, 'document_type' => 'medical_certificate']) : 
            (isRole('staff') ? route('staff.document-issuances.create', ['user_id' => $patient->user_id, 'document_type' => 'medical_certificate']) : 
            (isRole('doctor') ? route('doctors.document-issuances.create', ['user_id' => $patient->user_id, 'document_type' => 'medical_certificate']) : '#'))
        }}" class="btn btn-success me-2">
            <i class="fa-solid fa-file-medical"></i> Create Medical Certificate
        </a>
        <a href="{{ 
            isRole('clinic_admin') ? route('document-issuances.create', ['user_id' => $patient->user_id, 'document_type' => 'consultation_form']) : 
            (isRole('staff') ? route('staff.document-issuances.create', ['user_id' => $patient->user_id, 'document_type' => 'consultation_form']) : 
            (isRole('doctor') ? route('doctors.document-issuances.create', ['user_id' => $patient->user_id, 'document_type' => 'consultation_form']) : '#'))
        }}" class="btn btn-primary">
            <i class="fa-solid fa-notes-medical"></i> Create Consultation Form
        </a>
    </div>
    @endif
    @endif

    @if(isRole('doctor'))
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
        <livewire:document-issuance-table :patientId="request('patient_id')" />
    </div>
</div>

<!-- Create Document Modal -->
@if(!request('patient_id'))
<div class="modal fade" id="createDocumentModal" tabindex="-1" aria-labelledby="createDocumentModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="createDocumentModalLabel">Create New Document</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="createDocumentForm" method="GET" action="{{ isRole('clinic_admin') ? route('document-issuances.create') : (isRole('staff') ? route('staff.document-issuances.create') : route('doctors.document-issuances.create')) }}">
                    <div class="mb-3">
                        <label for="create_patient_id" class="form-label mb-1">Select Patient <span class="text-danger">*</span></label>
                        <select class="form-select form-select-solid" name="user_id" id="create_patient_id" data-control="select2" data-placeholder="Choose a patient" required>
                            <option value=""></option>
                            @php
                                $patients = \App\Models\User::where('type', \App\Models\User::PATIENT)
                                    ->whereHas('patient')
                                    ->orderBy('first_name')->get();
                            @endphp
                            @foreach($patients as $u)
                                <option value="{{ $u->id }}">{{ $u->first_name }} {{ $u->last_name }} ({{ $u->email }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-4">
                        <label for="document_type" class="form-label mb-1">Document Type <span class="text-danger">*</span></label>
                        <select class="form-select form-select-solid mt-1" name="document_type" id="document_type" required>
                            <option value="consultation_form">Consultation Form</option>
                            <option value="medical_certificate">Medical Certificate</option>
                        </select>
                    </div>
                    <div class="d-flex justify-content-end">
                        <button type="button" class="btn btn-secondary me-2" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Proceed</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endif
@endsection

@if(!request('patient_id'))
@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        if ($('#create_patient_id').length) {
            $('#create_patient_id').select2({
                dropdownParent: $('#createDocumentModal')
            });
        }
    });
</script>
@endpush
@endif