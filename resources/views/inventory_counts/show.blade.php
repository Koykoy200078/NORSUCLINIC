@extends('layouts.app')
@section('title', 'Count sheet ' . $count->label())
@section('content')
@php
    $self = getRouteByRole('inventory-counts.show', ['count' => $count->id]);
    $filterUrl = fn (string $value) => $self . ($value === 'all' ? '' : '?' . http_build_query(['filter' => $value, 'search' => $search ?: null]));
@endphp
<div class="container-fluid">
    <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-3">
        <div>
            <h1 class="fs-3 mb-1">Count sheet {{ $count->label() }}
                <span class="badge {{ $count->isFinalized() ? 'bg-success' : 'bg-warning text-dark' }} fs-6 align-middle">{{ $count->isFinalized() ? 'Finalized' : 'Draft' }}</span></h1>
            <p class="text-muted mb-0">
                Day counted {{ $count->counted_on?->toDateString() }} · made by {{ $count->creator?->full_name ?? 'N/A' }}
                @if($count->isFinalized()) · finalized {{ $count->finalized_at?->format('Y-m-d H:i') }} by {{ $count->finalizer?->full_name ?? 'N/A' }}@endif
                · {{ $totals['medicines'] }} medicines, {{ $totals['counted'] }} counted, {{ $totals['with_difference'] }} with a difference
            </p>
            @if($count->notes)<p class="mb-0 mt-2">{{ $count->notes }}</p>@endif
        </div>
        <a href="{{ getRouteByRole('inventory-counts.index') }}" class="btn btn-secondary">Back to count sheets</a>
    </div>

    @if(session('success'))<div class="alert alert-success" role="status">{{ session('success') }}</div>@endif
    @if($errors->any())
        <div class="alert alert-danger" role="alert"><ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
    @endif

    <div class="d-flex flex-wrap justify-content-between align-items-end gap-3 mb-3">
        <div class="d-flex flex-wrap align-items-end gap-2">
            @foreach(['all' => 'All', 'uncounted' => 'Not counted yet', 'difference' => 'With a difference'] as $value => $name)
                <a class="btn btn-sm {{ $filter === $value ? 'btn-primary' : 'btn-outline-primary' }}" href="{{ $filterUrl($value) }}">{{ $name }}</a>
            @endforeach
            <form method="get" action="{{ $self }}" class="d-flex gap-2 ms-2">
                @if($filter !== 'all')<input type="hidden" name="filter" value="{{ $filter }}">@endif
                <label class="visually-hidden" for="search">Search a medicine</label>
                <input class="form-control form-control-sm" id="search" name="search" type="search" value="{{ $search }}" placeholder="Search a medicine">
                <button class="btn btn-sm btn-outline-secondary" type="submit">Search</button>
            </form>
        </div>
        @if($editable)
        <div class="d-flex flex-wrap gap-2">
            <form method="post" action="{{ getRouteByRole('inventory-counts.refresh', ['count' => $count->id]) }}">@csrf
                @if($filter !== 'all')<input type="hidden" name="filter" value="{{ $filter }}">@endif
                <button class="btn btn-outline-primary" type="submit" title="Take the system amounts again (the counts you typed stay)">Refresh system amounts</button></form>
            <form method="post" action="{{ getRouteByRole('inventory-counts.finalize', ['count' => $count->id]) }}" onsubmit="return confirm('Finalize this count sheet? It cannot be changed afterwards.');">@csrf
                <button class="btn btn-success" type="submit">Finalize</button></form>
            <form method="post" action="{{ getRouteByRole('inventory-counts.destroy', ['count' => $count->id]) }}" onsubmit="return confirm('Delete this draft count sheet and everything typed on it?');">@csrf @method('DELETE')
                <button class="btn btn-outline-danger" type="submit">Delete sheet</button></form>
        </div>
        @endif
    </div>

    @if($lines->isEmpty())
        <div class="card"><div class="card-body mb-0">No medicine matches this view.</div></div>
    @else
    @if($editable)<form method="post" action="{{ $self }}" id="counts-form">@csrf @method('PUT')
        <input type="hidden" name="filter" value="{{ $filter }}"><input type="hidden" name="search" value="{{ $search }}">@endif
    <div class="card"><div class="card-body">
        <div class="table-responsive"><table class="table table-bordered align-middle">
            <thead><tr><th>Medicine</th><th>Strength · unit</th><th class="text-end">System</th><th style="width:9rem">Counted</th><th class="text-end">Difference</th><th style="width:14rem">Reason</th><th style="min-width:14rem">Note</th>@if($count->isFinalized() && $canWrite)<th>Stock</th>@endif</tr></thead>
            <tbody>
            @foreach($lines as $line)
                @php $field = fn (string $name) => 'rows[' . $line->medicine_id . '][' . $name . ']'; $dot = fn (string $name) => 'rows.' . $line->medicine_id . '.' . $name; @endphp
                <tr>
                    <td>{{ $line->medicine_name }}</td>
                    <td>{{ $line->strength }} · {{ $line->unit }}</td>
                    <td class="text-end">{{ $line->system_quantity }}</td>
                    @if($editable)
                        <td><label class="visually-hidden" for="counted-{{ $line->medicine_id }}">Counted units of {{ $line->medicine_name }}</label>
                            <input class="form-control" id="counted-{{ $line->medicine_id }}" name="{{ $field('counted') }}" type="number" min="0" max="2147483647" step="1" inputmode="numeric" value="{{ old($dot('counted'), $line->counted_quantity) }}"></td>
                    @else
                        <td>{{ $line->counted_quantity ?? '—' }}</td>
                    @endif
                    <td class="text-end">@if($line->difference === null)—@else<span class="{{ $line->difference < 0 ? 'text-danger' : ($line->difference > 0 ? 'text-warning' : '') }}">{{ $line->difference > 0 ? '+' : '' }}{{ $line->difference }}</span>@endif</td>
                    @if($editable)
                        <td><label class="visually-hidden" for="reason-{{ $line->medicine_id }}">Reason for {{ $line->medicine_name }}</label>
                            <select class="form-select" id="reason-{{ $line->medicine_id }}" name="{{ $field('reason') }}" style="min-width:11rem"><option value="">—</option>
                                @foreach(\App\Models\InventoryCountLine::REASONS as $key => $label)<option value="{{ $key }}" @selected(old($dot('reason'), $line->reason) === $key)>{{ $label }}</option>@endforeach
                            </select></td>
                        <td><label class="visually-hidden" for="note-{{ $line->medicine_id }}">Note for {{ $line->medicine_name }}</label>
                            <input class="form-control" id="note-{{ $line->medicine_id }}" name="{{ $field('note') }}" type="text" maxlength="500" value="{{ old($dot('note'), $line->note) }}"></td>
                    @else
                        <td>{{ $line->reasonLabel() ?? '—' }}</td>
                        <td>{{ $line->note }}</td>
                    @endif
                    @if($count->isFinalized() && $canWrite)
                        <td>
                            @if($line->adjusted_at)<span class="badge bg-info text-dark">Corrected {{ $line->adjusted_at->toDateString() }}</span>
                            @elseif(! $line->hasDifference())<span class="text-muted">—</span>
                            @elseif(array_key_exists($line->id, $corrections) && $corrections[$line->id] === null)
                                <a class="btn btn-sm btn-warning" href="{{ getRouteByRole('stock-control.index', ['medicine_id' => $line->medicine_id, 'count_line_id' => $line->id]) }}">Correct stock</a>
                            @else<small class="text-muted">{{ $corrections[$line->id] ?? '' }}</small>@endif
                        </td>
                    @endif
                </tr>
            @endforeach
            </tbody>
        </table></div>
        @if($editable)<button class="btn btn-primary" type="submit">Save counts</button>
        <span class="text-muted ms-2">Leave a count empty for a medicine you have not counted yet. A difference needs a reason before the sheet can be finalized.</span>@endif
    </div></div>
    @if($editable)</form>@endif
    @endif
</div>
@endsection
