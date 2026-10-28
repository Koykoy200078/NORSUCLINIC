@extends('layouts.app')
@section('title', 'Merge duplicate inventory records')
@section('content')
<div class="container-fluid">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
        <h1 class="fs-3 mb-0">Merge duplicate inventory records</h1>
        <a href="{{ route('medicine-inventory.index') }}" class="btn btn-secondary">Back to inventory</a>
    </div>
    @include('flash::message')
    @include('layouts.errors')
    @if(session('success'))
        <div class="alert alert-success" role="status">{{ session('success') }}</div>
    @endif
    <nav class="nav nav-pills gap-2 mb-4" aria-label="Record type">
        @foreach(['medicine' => 'Medicines', 'category' => 'Categories', 'generic' => 'Generics'] as $key => $label)
            <a class="nav-link {{ $kind === $key ? 'active' : '' }}" href="{{ route('inventory-merge.index', ['kind' => $key]) }}" @if($kind === $key) aria-current="page" @endif>{{ $label }}</a>
        @endforeach
    </nav>
    @unless($ready)
        <div class="alert alert-info">Inventory merging will be available after the clinic database update is applied.</div>
    @else
    @if($previewError)
        <div class="alert alert-warning" role="alert">{{ $previewError }}</div>
    @endif
    <div class="card mb-4"><div class="card-body">
        <p>The retained record keeps its name and details. Linked history moves to it, and the duplicate leaves active lists. Review the preview before confirming.</p>
        @if($kind === 'medicine')
            <p>Medicines must have the same generic ingredient and unit. Strengths, expiry dates, quantities and ledger dates stay separate in their original batches.</p>
        @elseif($kind === 'generic')
            <p>Only merge generic labels that refer to the same ingredient. Medicines using the duplicate will receive the retained generic label.</p>
        @endif
        <form action="{{ route('inventory-merge.index') }}" method="get">
            <input type="hidden" name="kind" value="{{ $kind }}">
            <div class="row g-3">
            @foreach(['source_id' => 'Duplicate to archive', 'target_id' => 'Record to keep'] as $field => $label)
                <div class="col-md-6">
                    <label for="{{ $field }}" class="form-label">{{ $label }}</label>
                    <select name="{{ $field }}" id="{{ $field }}" class="form-select" required>
                        <option value="">Select a {{ $kind }}</option>
                        @foreach($records as $record)
                            <option value="{{ $record->id }}" @selected((string) request($field) === (string) $record->id)>{{ $record->name }} (#{{ $record->id }})@if($kind === 'medicine') — {{ $record->generic_name }} · {{ $record->dosage }} · {{ $record->uom }}@endif</option>
                        @endforeach
                    </select>
                </div>
            @endforeach
            </div>
            <button type="submit" class="btn btn-primary mt-3">Preview merge</button>
        </form>
    </div></div>
    @if($preview)
        <section class="card mb-4" aria-labelledby="merge-preview-title"><div class="card-body">
            <h2 id="merge-preview-title" class="fs-4">Merge preview</h2>
            <p><strong>{{ $preview['source']['name'] }} (#{{ $preview['source']['id'] }})</strong> will be archived into <strong>{{ $preview['target']['name'] }} (#{{ $preview['target']['id'] }})</strong>.</p>
            @foreach($preview['blockers'] as $blocker)
                <div class="alert alert-warning" role="alert">{{ $blocker }}</div>
            @endforeach
            <div class="table-responsive"><table class="table table-bordered">
                <thead><tr><th scope="col">History to move</th><th scope="col">Records</th></tr></thead>
                <tbody>@foreach($preview['counts'] as $count)<tr><td>{{ $count['label'] }}</td><td>{{ $count['count'] }}</td></tr>@endforeach</tbody>
            </table></div>
            @if($kind === 'medicine')
                <div class="table-responsive"><table class="table table-bordered">
                    <thead><tr><th>Medicine details</th><th>Duplicate to archive</th><th>Record to keep</th></tr></thead>
                    <tbody>
                    @foreach(['ingredient' => 'Generic ingredient', 'dosage' => 'Default strength', 'uom' => 'Unit', 'sku' => 'SKU'] as $field => $label)
                        <tr><th scope="row">{{ $label }}</th><td>{{ $preview['source'][$field] ?? 'Not recorded' }}</td><td>{{ $preview['target'][$field] ?? 'Not recorded' }}</td></tr>
                    @endforeach
                    <tr><th scope="row">Usable stock today</th><td>{{ $preview['stock']['source']['usable'] }}</td><td>{{ $preview['stock']['target']['usable'] }}</td></tr>
                    <tr><th scope="row">Total stock, including expired</th><td>{{ $preview['stock']['source']['total'] }}</td><td>{{ $preview['stock']['target']['total'] }}</td></tr>
                    </tbody>
                </table></div>
                <p>After merging: <strong>{{ $preview['stock']['source']['usable'] + $preview['stock']['target']['usable'] }} usable units</strong>, {{ $preview['stock']['source']['total'] + $preview['stock']['target']['total'] }} units in total.</p>
                <p>Old aggregate-only stock is converted to opening batches on confirmation. An unset batch or line strength keeps the duplicate medicine's saved strength, or “N/A” when unknown. Ledger entries keep their batch links and dates; the yearly report combines both histories under the retained medicine.</p>
                <div class="table-responsive"><table class="table table-bordered">
                    <caption>Incoming batches. A colliding number gets a distinct reference; the original remains in the archive audit.</caption>
                    <thead><tr><th>Batch now</th><th>Batch after merge</th><th>Strength</th><th>Units</th><th>Expiry</th></tr></thead>
                    <tbody>@foreach($preview['batches'] as $batch)<tr><td>{{ $batch['before'] }}</td><td>{{ $batch['after'] }}</td><td>{{ $batch['dosage'] }}</td><td>{{ $batch['quantity'] }}</td><td>{{ $batch['expiry'] === '2099-12-31' ? 'Not recorded' : ($batch['expiry'] ?? 'Not recorded') }}</td></tr>@endforeach</tbody>
                </table></div>
            @endif
            @if($preview['token'])
                <form action="{{ route('inventory-merge.store') }}" method="post">
                    @csrf
                    <input type="hidden" name="kind" value="{{ $kind }}">
                    <input type="hidden" name="source_id" value="{{ $preview['source']['id'] }}">
                    <input type="hidden" name="target_id" value="{{ $preview['target']['id'] }}">
                    <input type="hidden" name="preview_token" value="{{ $preview['token'] }}">
                    <label class="form-check mb-3"><input class="form-check-input" type="checkbox" name="confirmed" value="1" required><span class="form-check-label">I reviewed these records and want to merge them and archive the duplicate.</span></label>
                    <p class="text-muted">This preview is valid for 15 minutes. If either record or its history changes, preview it again.</p>
                    <button type="submit" class="btn btn-warning">Merge and archive duplicate</button>
                </form>
            @endif
        </div></section>
    @endif
    @endunless
    @if($history->isNotEmpty())
        <section class="card"><div class="card-body">
            <h2 class="fs-4">Recent merges</h2>
            <div class="table-responsive"><table class="table"><thead><tr><th>Archived duplicate</th><th>Retained record</th><th>By</th><th>When</th></tr></thead><tbody>
                @foreach($history as $entry)<tr><td>{{ $entry->properties['source']['name'] }} (#{{ $entry->properties['source']['id'] }})</td><td>{{ $entry->properties['target']['name'] }} (#{{ $entry->properties['target']['id'] }})</td><td>{{ $entry->user_name }}</td><td>{{ $entry->created_at->format('Y-m-d H:i') }}</td></tr>@endforeach
            </tbody></table></div>
        </div></section>
    @endif
</div>
@endsection
