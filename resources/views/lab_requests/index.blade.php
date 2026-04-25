@extends('layouts.app')

@section('title')
Laboratory & Medical Request Management
@endsection

@section('header_toolbar')
<div class="container-fluid">
    <div class="d-flex flex-wrap align-items-center justify-content-between mb-7">
        <div>
            <h1 class="mb-0 me-1">
                <i class="fa-solid fa-flask me-2" style="color:#2563a8;"></i>
                Laboratory & Medical Requests
            </h1>
            <p class="text-muted mb-0 mt-1" style="font-size:.88rem;">
                Create, track, and manage laboratory and medical requests for patients.
            </p>
        </div>
        <div class="text-end mt-4 mt-md-0 d-flex gap-2">
            @if(auth()->user()->can('manage_request_documents') || isRole('patient'))
            @php $createRoute = getRouteByRole('lab-requests.create'); @endphp
            <a href="{{ $createRoute }}" class="btn btn-primary d-inline-flex align-items-center gap-2">
                <i class="fa-solid fa-plus"></i>
                New Lab Request
            </a>
            @endif
        </div>
    </div>
</div>
@endsection

@section('content')
<div class="container-fluid">
    @include('flash::message')

    {{-- Patient-scoped breadcrumb --}}
    @if(request('patient_id'))
    @php
        $scopedPatient = \App\Models\Patient::whereHas('user', function($q) {
            $q->where('id', request('patient_id'));
        })->with('user')->first();
    @endphp
    @if($scopedPatient)
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb">
            <li class="breadcrumb-item">
                <a href="{{ getRouteByRole('patients.index') }}">
                    <i class="fas fa-hospital-user"></i> Patients
                </a>
            </li>
            <li class="breadcrumb-item">
                <a href="{{ getRouteByRole('patients.showMyHistory', ['patient' => $scopedPatient->id]) }}">
                    {{ $scopedPatient->user->first_name }} {{ $scopedPatient->user->last_name }}
                </a>
            </li>
            <li class="breadcrumb-item active" aria-current="page">Lab Requests</li>
        </ol>
    </nav>
    <div class="mb-3">
        <a href="{{ getRouteByRole('lab-requests.create', ['user_id' => $scopedPatient->user_id]) }}"
           class="btn btn-primary d-inline-flex align-items-center gap-2">
            <i class="fa-solid fa-flask"></i> New Lab Request for this Patient
        </a>
    </div>
    @endif
    @endif

    {{-- Status filter tabs --}}
    @php
        $activeStatus = request('status', '');
        $statuses = array_merge(['all' => 'All'], \App\Models\LabRequest::statusLabels());
    @endphp
    <div class="mb-3 d-flex flex-wrap gap-1 align-items-center">
        @foreach($statuses as $key => $label)
        @php
            $isActive = ($key === 'all' && $activeStatus === '') || $activeStatus === $key;
            $badgeClass = match($key) {
                'pending'    => 'warning text-dark',
                'collected'  => 'info text-dark',
                'processing' => 'primary',
                'completed'  => 'success',
                'cancelled'  => 'danger',
                'referred'   => 'secondary',
                'rejected'   => 'dark',
                default      => 'light text-dark',
            };
        @endphp
        <a href="{{ request()->fullUrlWithQuery(['status' => $key === 'all' ? '' : $key]) }}"
           class="btn btn-sm {{ $isActive ? 'btn-'.$badgeClass.' active fw-bold' : 'btn-outline-secondary' }}">
            {{ $label }}
        </a>
        @endforeach
    </div>

    <div class="d-flex flex-column">
        <livewire:lab-request-table :patientId="request('patient_id')" :status="request('status', '')" />
    </div>
</div>
@endsection
