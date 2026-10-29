@extends('layouts.app')
@section('title')
Meds Only record
@endsection

@section('content')
<div class="container-fluid">
    @include('flash::message')
    <div class="d-flex flex-wrap justify-content-between gap-3 mb-4">
        <h1 class="fs-3 mb-0">Meds Only record</h1>
        <div class="d-flex gap-2">
            @unless(isRole('doctor'))
                <a class="btn btn-outline-primary" href="{{ getRouteByRole('meds-only.edit', [$record->id]) }}"><i class="fa-solid fa-pen-to-square me-2"></i>{{ __('messages.common.edit') }}</a>
            @endunless
            <a class="btn btn-secondary" href="{{ getRouteByRole('meds-only.index') }}">Back to Meds Only</a>
        </div>
    </div>

    <div class="card mb-4"><div class="card-body">
        <dl class="row mb-0">
            <dt class="col-sm-3">Date</dt><dd class="col-sm-9">{{ \Carbon\Carbon::parse($record->bill_date)->format('F j, Y') }} <span class="text-muted">{{ \Carbon\Carbon::parse($record->bill_date)->format('h:i A') }}</span></dd>
            <dt class="col-sm-3">Name</dt>
            <dd class="col-sm-9">
                {{ $record->patient_name }}
                @if($record->patient_id && $record->patient && canUseModule('patients') && ! isRole('doctor'))
                    <a class="ms-2" href="{{ getRouteByRole('patients.show', [$record->patient_id]) }}">Patient record</a>
                @elseif(! $record->patient_id)
                    <span class="badge bg-secondary ms-2">Not registered</span>
                @endif
            </dd>
            <dt class="col-sm-3">Patient type</dt><dd class="col-sm-9">{{ $record->patient_type_id ? (\App\Models\PatientType::find($record->patient_type_id)?->name ?? '—') : '—' }}</dd>
            <dt class="col-sm-3">Campus</dt><dd class="col-sm-9">{{ $record->campus ?: '—' }}</dd>
            <dt class="col-sm-3">College</dt><dd class="col-sm-9">{{ $record->college ?: '—' }}</dd>
            <dt class="col-sm-3">Course</dt><dd class="col-sm-9">{{ $record->course ?: '—' }}</dd>
            <dt class="col-sm-3">Year</dt><dd class="col-sm-9">{{ $record->year_level ?: '—' }}</dd>
            <dt class="col-sm-3">Recorded by</dt><dd class="col-sm-9">{{ $record->recorder?->full_name ?? 'N/A' }}</dd>
            <dt class="col-sm-3">Number</dt><dd class="col-sm-9">{{ $record->history_number }}</dd>
            <dt class="col-sm-3">Note</dt><dd class="col-sm-9">{{ $record->note ?: '—' }}</dd>
        </dl>
    </div></div>

    <div class="card"><div class="card-body">
        <h2 class="fs-4">Drugs given</h2>
        <div class="table-responsive">
            <table class="table table-bordered mb-0">
                <thead><tr><th>Medicine</th><th>Strength</th><th>Quantity</th></tr></thead>
                <tbody>
                    @forelse($record->dispenseItems->groupBy(fn ($item) => $item->medicine_id.'|'.mb_strtolower(trim((string) $item->dosage))) as $items)
                        <tr>
                            <td>{{ $items->first()->medicine?->name ?? 'Deleted medicine' }}</td>
                            <td>{{ trim((string) $items->first()->dosage) ?: '—' }}</td>
                            <td>{{ (int) $items->sum('quantity') }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="3" class="text-center text-muted">No drug lines.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div></div>
</div>
@endsection
