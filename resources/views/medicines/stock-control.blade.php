@extends('layouts.app')
@section('title', 'Stock disposal and physical count')
@section('content')
<div class="container-fluid">
    <div class="d-flex flex-wrap justify-content-between gap-3 mb-4"><h1 class="fs-3">Stock disposal and physical count</h1><a href="{{ getRouteByRole('medicines.index') }}" class="btn btn-secondary">Back to inventory</a></div>
    @if(session('success'))<div class="alert alert-success" role="status">{{ session('success') }}</div>@endif
    @if($errors->any())<div class="alert alert-danger" role="alert">{{ $errors->first() }}</div>@endif
    @unless($preview)
        <div class="card mb-4"><div class="card-body">
            <form method="get" action="{{ getRouteByRole('stock-control.index') }}">
                <label class="form-label" for="medicine_id">Medicine</label><select class="form-select" id="medicine_id" name="medicine_id" required><option value="">Select medicine</option>
                    @foreach($medicines as $option)<option value="{{ $option->id }}" @selected($medicine?->id === $option->id)>{{ $option->name }} · {{ $option->dosage }} · {{ $option->uom }} (#{{ $option->id }})</option>@endforeach
                </select><button class="btn btn-primary mt-3" type="submit">Load batches</button>
            </form>
        </div></div>
        @if($medicine)
            @if($countLine)
                @if($countBlocked)
                    <div class="alert alert-warning" role="alert"><strong>Count sheet {{ $countLine->sheet->label() }}:</strong> {{ $countBlocked }}</div>
                @else
                    <div class="alert alert-info" role="status"><strong>Count sheet {{ $countLine->sheet->label() }}:</strong> {{ $countLine->medicine_name }} was counted {{ $countLine->counted_quantity }}, system {{ $countLine->system_quantity }}@if($countLine->reasonLabel()) ({{ $countLine->reasonLabel() }})@endif.
                        Enter the units of each batch so that the batches together add up to {{ $countLine->counted_quantity }}; a batch you leave blank keeps its units.</div>
                @endif
            @endif
            <section class="card"><div class="card-body"><h2 class="fs-4">{{ $medicine->name }} — batch quantities</h2>
                @if($medicine->batches->isEmpty())
                    <p>No batches are recorded. Record identifiable stock batches before taking a per-batch count.</p>
                @else
                    <form method="post" action="{{ getRouteByRole('stock-control.preview') }}">
                        @csrf<input type="hidden" name="medicine_id" value="{{ $medicine->id }}">
                        @if($countLine && ! $countBlocked)
                            <input type="hidden" name="count_line_id" value="{{ $countLine->id }}"><input type="hidden" name="mode" value="count">
                            <p class="form-label">Operation: Physical count (enter units remaining)</p>
                        @else
                        <label for="mode" class="form-label">Operation</label><select name="mode" id="mode" class="form-select mb-3"><option value="count">Physical count (enter units remaining)</option><option value="expired">Dispose expired stock (enter units to remove)</option><option value="damaged">Dispose damaged stock (enter units to remove)</option></select>
                        @endif
                        <p>Fill only the batches being checked. Enter 0 for an empty shelf count; leave a batch blank to keep it unchanged. Expiry dates and strengths remain as recorded. A batch expires the day after its expiry date.</p>
                        <div class="table-responsive"><table class="table table-bordered"><thead><tr><th>Batch</th><th>Strength</th><th>Expiry</th><th>Recorded units</th><th>Count / disposal units</th></tr></thead><tbody>
                            @foreach($medicine->batches as $batch)<tr><td>{{ $batch->batch_number }}</td><td>{{ $batch->dosage }}</td><td>{{ $batch->expiration_date?->toDateString() === '2099-12-31' ? 'Not recorded' : $batch->expiration_date?->toDateString() }}</td><td>{{ $batch->quantity }}</td><td><label class="visually-hidden" for="batch-{{ $batch->id }}">Units for batch {{ $batch->batch_number }}</label><input class="form-control" id="batch-{{ $batch->id }}" name="entries[{{ $batch->id }}]" type="number" min="0" max="2147483647" step="1" inputmode="numeric" value="{{ $prefill[$batch->id] ?? '' }}"></td></tr>@endforeach
                        </tbody></table></div>
                        <label for="reason" class="form-label">Reason / count notes</label><textarea class="form-control mb-3" id="reason" name="reason" required minlength="3" maxlength="500">{{ $countLine && ! $countBlocked ? trim('Count sheet '.$countLine->sheet->label().': '.($countLine->reasonLabel() ?? '').' '.($countLine->note ?? '')) : '' }}</textarea>
                        <button type="submit" class="btn btn-primary">Preview stock changes</button>
                    </form>
                @endif
            </div></section>
        @endif
    @else
        <section class="card" aria-labelledby="stock-preview-title"><div class="card-body"><h2 class="fs-4" id="stock-preview-title">Stock change preview</h2>
            <p>{{ $medicine->name }} — {{ $preview['mode'] === 'count' ? 'Physical count' : ucfirst($preview['mode']).' disposal' }}</p><p>{{ $preview['reason'] }}</p>
            <div class="table-responsive"><table class="table table-bordered"><thead><tr><th>Batch</th><th>Recorded</th><th>After confirmation</th><th>Difference</th></tr></thead><tbody>@foreach($preview['changes'] as $change)<tr><td>{{ $change['batch'] }} · {{ $change['dosage'] }}</td><td>{{ $change['before'] }}</td><td>{{ $change['after'] }}</td><td>{{ $change['difference'] > 0 ? '+' : '' }}{{ $change['difference'] }}</td></tr>@endforeach</tbody></table></div>
            <p>This preview expires in 15 minutes. If stock changes, preview it again. Ledger entries and the yearly report will record these differences on confirmation.</p>
            <form method="post" action="{{ getRouteByRole('stock-control.store') }}">@csrf<input type="hidden" name="preview_token" value="{{ $preview['token'] }}"><label class="form-check mb-3"><input class="form-check-input" name="confirmed" type="checkbox" value="1" required><span class="form-check-label">I checked the batch quantities and reason.</span></label><button class="btn btn-warning" type="submit">Confirm stock changes</button></form>
            <a class="btn btn-secondary mt-3" href="{{ getRouteByRole('stock-control.index', ['medicine_id' => $medicine->id]) }}">Edit quantities</a>
        </div></section>
    @endunless
</div>
@endsection
