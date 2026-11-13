{{-- YEARLY MEDICINE INVENTORY of the Medicine Inventory tab of Report Generation. $report comes from YearlyInventoryReportBuilder. --}}
@php
    $download = fn (string $format) => getRouteByRole('activity-logs.inventory-yearly', ['format' => $format, 'year' => $report['year']]);
@endphp

<style>
    @include('activity_logs.reports.inventory_sheet_styles')
    .inv-table .inv-blank { background: #fafafa; }
    .inv-table .inv-expired { background: #fff4dd; font-weight: 600; }
</style>

<div class="inv-actions d-flex flex-wrap gap-2 align-items-center mb-4">
    <a href="{{ $download('pdf') }}" class="btn btn-sm btn-danger"><i class="fas fa-file-pdf"></i> Download PDF</a>
    <a href="{{ $download('xlsx') }}" class="btn btn-sm btn-success"><i class="fas fa-file-excel"></i> Download Excel</a>
    <a href="{{ $download('csv') }}" class="btn btn-sm btn-outline-primary"><i class="fas fa-file-csv"></i> Download CSV</a>
    <span class="text-muted fs-7 ms-2">
        {{ count($report['rows']) }} medicine{{ count($report['rows']) === 1 ? '' : 's' }} with stock or stock movement in {{ $report['year'] }}.
        @if($report['inconsistent'] > 0)
            <span class="text-danger">{{ $report['inconsistent'] }} of them do not add up in the ledger: see the Notes column.</span>
        @endif
    </span>
</div>

<div class="inv-sheet">
    @include('activity_logs.reports.inventory_yearly_heading', ['report' => $report])

    <div class="table-responsive">
        @include('activity_logs.reports.inventory_yearly_table', ['report' => $report])
    </div>

    @include('activity_logs.reports.inventory_yearly_notes', ['report' => $report])

    @include('activity_logs.reports.accomplishment_signatures', ['report' => $report])
</div>
