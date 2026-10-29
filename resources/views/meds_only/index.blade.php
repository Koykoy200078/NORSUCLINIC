@extends('layouts.app')
@section('title')
Meds Only
@endsection

@section('content')
<div class="container-fluid">
    @include('flash::message')
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
        <div>
            <h1 class="fs-3 mb-1"><i class="fas fa-hand-holding-medical me-2"></i>Meds Only</h1>
            <p class="text-muted mb-0">Patients who only come for medicine and need no consultation: the date, who, course, year, college and the drugs given. The stock goes out like any dispense and the records show in Report Generation.</p>
        </div>
        @unless(isRole('doctor'))
            <a class="btn btn-primary" href="{{ getRouteByRole('meds-only.create') }}"><i class="fas fa-plus me-2"></i>Add Meds Only record</a>
        @endunless
    </div>
    <div class="d-flex flex-column">
        <livewire:meds-only-table />
    </div>
</div>
@endsection
