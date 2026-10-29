@extends('layouts.app')
@section('title', 'Quarterly count sheets')
@section('content')
<div class="container-fluid">
    <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-4">
        <div>
            <h1 class="fs-3 mb-1">Quarterly count sheets</h1>
            <p class="text-muted mb-0">Type the final remaining count of each medicine for a quarter, or for everything on the day you counted, with the reason of every difference. Finalized sheets feed the "Medicines with notes" report; the shelf stock changes only when you confirm a correction from a finalized sheet.</p>
        </div>
        <a href="{{ getRouteByRole('medicines.index') }}" class="btn btn-secondary">Back to inventory</a>
    </div>

    @if(session('success'))<div class="alert alert-success" role="status">{{ session('success') }}</div>@endif
    @if($errors->any())
        <div class="alert alert-danger" role="alert"><ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
    @endif

    @if($canWrite)
    <section class="card mb-4" aria-labelledby="new-sheet-title"><div class="card-body">
        <h2 class="fs-4" id="new-sheet-title">New count sheet</h2>
        <form method="post" action="{{ getRouteByRole('inventory-counts.store') }}" id="new-count-sheet">
            @csrf
            <div class="row g-3">
                <div class="col-md-3">
                    <label class="form-label" for="quarter">Which count</label>
                    <select class="form-select" id="quarter" name="quarter">
                        <option value="" @selected(old('quarter') === '')>Everything, counted on the day</option>
                        @foreach([1 => 'Q1 (end of March)', 2 => 'Q2 (end of June)', 3 => 'Q3 (end of September)', 4 => 'Q4 (end of December)'] as $number => $name)
                            <option value="{{ $number }}" @selected((string) old('quarter', '1') === (string) $number)>{{ $name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label" for="year">Year (for a quarter)</label>
                    <select class="form-select" id="year" name="year">
                        @foreach($years as $year)<option value="{{ $year }}" @selected((int) old('year', now()->year) === $year)>{{ $year }}</option>@endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label" for="counted_on">Day you counted</label>
                    <input class="form-control" type="date" id="counted_on" name="counted_on" value="{{ old('counted_on', now()->toDateString()) }}" max="{{ now()->toDateString() }}" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label" for="scope">Medicines</label>
                    <select class="form-select" id="scope" name="scope">
                        <option value="all" @selected(old('scope', 'all') === 'all')>All medicines</option>
                        <option value="selected" @selected(old('scope') === 'selected')>Only the ones I choose below</option>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label" for="medicine_ids">Chosen medicines (for "only the ones I choose")</label>
                    <select class="form-select" id="medicine_ids" name="medicine_ids[]" multiple size="6">
                        @foreach($medicines as $medicine)
                            <option value="{{ $medicine->id }}" @selected(in_array($medicine->id, (array) old('medicine_ids', [])))>{{ $medicine->name }} · {{ $medicine->dosage }} · {{ $medicine->uom }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label" for="notes">Note for the sheet (optional)</label>
                    <textarea class="form-control" id="notes" name="notes" rows="3" maxlength="1000">{{ old('notes') }}</textarea>
                </div>
            </div>
            <p class="text-muted mt-3 mb-3">A quarter sheet starts from what the stock ledger says at the end of that quarter (the figure of the yearly report); a count of everything starts from the shelf today.</p>
            <button class="btn btn-primary" type="submit">Make the sheet</button>
        </form>
    </div></section>
    @endif

    <section class="card"><div class="card-body">
        <h2 class="fs-4">Sheets</h2>
        @if($sheets->isEmpty())
            <p class="mb-0">No count sheet has been made yet.</p>
        @else
            <div class="table-responsive"><table class="table table-bordered align-middle mb-0">
                <thead><tr><th>Sheet</th><th>Day counted</th><th>Status</th><th>Medicines</th><th>Counted</th><th>With a difference</th><th>Made by</th><th></th></tr></thead>
                <tbody>
                @foreach($sheets as $sheet)
                    <tr>
                        <td><a href="{{ getRouteByRole('inventory-counts.show', ['count' => $sheet->id]) }}">{{ $sheet->label() }}</a></td>
                        <td>{{ $sheet->counted_on?->toDateString() }}</td>
                        <td><span class="badge {{ $sheet->isFinalized() ? 'bg-success' : 'bg-warning text-dark' }}">{{ $sheet->isFinalized() ? 'Finalized' : 'Draft' }}</span></td>
                        <td>{{ $sheet->lines_count }}</td>
                        <td>{{ $sheet->counted_count }}</td>
                        <td>{{ $sheet->difference_count }}</td>
                        <td>{{ $sheet->creator?->full_name ?? 'N/A' }}</td>
                        <td><a class="btn btn-sm btn-outline-primary" href="{{ getRouteByRole('inventory-counts.show', ['count' => $sheet->id]) }}">Open</a></td>
                    </tr>
                @endforeach
                </tbody>
            </table></div>
        @endif
    </div></section>
</div>
@endsection
