@extends('layouts.app')
@section('title')
{{ $record ? 'Edit Meds Only record' : 'Add Meds Only record' }}
@endsection

@php
    $oldLines = old('medicines');
    $config = [
        'medicinesUrl' => (parse_url(getRouteByRole('medicines.by.category'), PHP_URL_PATH) ?: getRouteByRole('medicines.by.category')).'?include_unavailable=1',
        'patientsUrl' => getRouteByRole('meds-only.patients'),
        'lines' => is_array($oldLines) ? array_values($oldLines) : $lines,
        'patient' => old('patient_id', $record?->patient_id)
            ? ['id' => (int) old('patient_id', $record?->patient_id), 'text' => $record?->patient?->user?->full_name ?? old('patient_name', $record?->patient_name)]
            : null,
    ];
    $date = old('bill_date', $record ? \Carbon\Carbon::parse($record->bill_date)->toDateString() : now()->toDateString());
    $pick = fn (string $field) => old($field, $record?->{$field});
@endphp

@section('content')
<div class="container-fluid">
    @include('flash::message')
    @if($errors->any())
        <div class="alert alert-danger" role="alert">{{ $errors->first() }}</div>
    @endif

    <div class="d-flex flex-wrap justify-content-between gap-3 mb-4">
        <h1 class="fs-3 mb-0">{{ $record ? 'Edit Meds Only record' : 'Add Meds Only record' }}</h1>
        <a class="btn btn-secondary" href="{{ getRouteByRole('meds-only.index') }}">Back to Meds Only</a>
    </div>

    <form method="post" id="meds-only-form" action="{{ $record ? getRouteByRole('meds-only.update', [$record->id]) : getRouteByRole('meds-only.store') }}">
        @csrf
        @if($record) @method('PUT') @endif

        <div class="card mb-4"><div class="card-body">
            <h2 class="fs-4 mb-3">Who</h2>
            <div class="row g-3">
                <div class="col-md-3">
                    <label class="form-label" for="bill_date">Date <span class="text-danger">*</span></label>
                    <input type="date" class="form-control" id="bill_date" name="bill_date" value="{{ $date }}" max="{{ now()->addDay()->toDateString() }}" required>
                </div>
                <div class="col-md-5">
                    <label class="form-label" for="patient_id">Registered patient</label>
                    <select class="form-select" id="patient_id" name="patient_id" data-placeholder="Search a registered patient (optional)"></select>
                    <div class="form-text">Choosing one fills in the college, course and year. Leave it empty for a person who is not registered.</div>
                </div>
                <div class="col-md-4" id="typed-name-box">
                    <label class="form-label" for="patient_name">Name (when not registered) <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="patient_name" name="patient_name" maxlength="191" value="{{ old('patient_name', $record && ! $record->patient_id ? $record->patient_name : '') }}" autocomplete="off">
                </div>
                <div class="col-md-3">
                    <label class="form-label" for="patient_type_id">Patient type</label>
                    <select class="form-select" id="patient_type_id" name="patient_type_id">
                        <option value="">—</option>
                        @foreach($patientTypes as $id => $name)<option value="{{ $id }}" @selected((string) $pick('patient_type_id') === (string) $id)>{{ $name }}</option>@endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label" for="campus_id">Campus</label>
                    <select class="form-select" id="campus_id" name="campus_id">
                        <option value="">—</option>
                        @foreach($campuses as $id => $name)<option value="{{ $id }}" @selected((string) $pick('campus_id') === (string) $id)>{{ $name }}</option>@endforeach
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label" for="college_id">College</label>
                    <select class="form-select" id="college_id" name="college_id">
                        <option value="">—</option>
                        @foreach($colleges as $id => $name)<option value="{{ $id }}" @selected((string) $pick('college_id') === (string) $id)>{{ $name }}</option>@endforeach
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label" for="course_id">Course</label>
                    <select class="form-select" id="course_id" name="course_id">
                        <option value="">—</option>
                        @foreach($courses as $id => $name)<option value="{{ $id }}" @selected((string) $pick('course_id') === (string) $id)>{{ $name }}</option>@endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label" for="year_level_id">Year</label>
                    <select class="form-select" id="year_level_id" name="year_level_id">
                        <option value="">—</option>
                        @foreach($yearLevels as $id => $name)<option value="{{ $id }}" @selected((string) $pick('year_level_id') === (string) $id)>{{ $name }}</option>@endforeach
                    </select>
                </div>
            </div>
        </div></div>

        <div class="card mb-4"><div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h2 class="fs-4 mb-0">Drugs given</h2>
                <button type="button" class="btn btn-outline-primary" id="add-medicine-row"><i class="fas fa-plus me-2"></i>Add drug</button>
            </div>
            <p class="text-muted small">One or many. Search by medicine name, generic, brand or category. Pick the strength, or leave "any strength" to take the batch that expires first. The same drug and strength can be listed once.</p>
            <div id="medicine-rows" class="d-flex flex-column gap-2"></div>
            <div class="alert alert-warning d-none mt-3" id="medicines-error" role="alert"></div>
        </div></div>

        <div class="card mb-4"><div class="card-body">
            <label class="form-label" for="note">Note</label>
            <textarea class="form-control" id="note" name="note" rows="2" maxlength="2000">{{ old('note', $record?->note) }}</textarea>
        </div></div>

        <div class="d-flex gap-2">
            <button type="submit" class="btn btn-primary">{{ $record ? 'Save changes' : 'Save record' }}</button>
            <a class="btn btn-secondary" href="{{ getRouteByRole('meds-only.index') }}">Cancel</a>
        </div>
    </form>

    <template id="medicine-row-template">
        <div class="meds-row border rounded p-2">
            <div class="row g-2 align-items-end">
                <div class="col-md-5"><label class="form-label small mb-1">Medicine</label>
                    <select class="form-select med-select" name="medicines[__INDEX__][medicine_id]" required></select></div>
                <div class="col-md-3"><label class="form-label small mb-1">Strength</label>
                    <select class="form-select med-dosage" name="medicines[__INDEX__][dosage]"></select></div>
                <div class="col-md-2"><label class="form-label small mb-1">Quantity</label>
                    <input type="number" min="1" class="form-control med-quantity" name="medicines[__INDEX__][quantity]" required></div>
                <div class="col-md-2 d-flex align-items-center gap-2"><span class="small text-muted med-stock"></span>
                    <button type="button" class="btn btn-sm btn-outline-danger ms-auto remove-row" aria-label="Remove drug"><i class="fas fa-trash"></i></button></div>
            </div>
        </div>
    </template>

    <script type="application/json" id="meds_only_config">{!! \Illuminate\Support\Js::encode($config) !!}</script>
</div>
@endsection
