{{-- MEDICINES WITH NOTES of the Medicine Inventory tab of Report Generation. $report comes from InventoryDiscrepancyReportBuilder. --}}
@php
    $query = array_filter(['year' => $report['year'], 'period' => $report['period'], 'show' => $report['show']], fn ($value) => $value !== null && $value !== '');
    $download = fn (string $format) => getRouteByRole('activity-logs.inventory-discrepancies', ['format' => $format] + $query);
@endphp

<style>
    @include('activity_logs.reports.inventory_sheet_styles')
</style>

<div class="inv-actions d-flex flex-wrap gap-2 align-items-center mb-4">
    <a href="{{ $download('pdf') }}" class="btn btn-sm btn-danger"><i class="fas fa-file-pdf"></i> Download PDF</a>
    <a href="{{ $download('xlsx') }}" class="btn btn-sm btn-success"><i class="fas fa-file-excel"></i> Download Excel</a>
    <a href="{{ $download('csv') }}" class="btn btn-sm btn-outline-primary"><i class="fas fa-file-csv"></i> Download CSV</a>
    <span class="text-muted fs-7 ms-2">
        {{ count($report['rows']) }} line{{ count($report['rows']) === 1 ? '' : 's' }} from {{ $report['sheets'] }} finalized sheet{{ $report['sheets'] === 1 ? '' : 's' }};
        {{ $report['differences'] }} with a difference (net {{ $report['net'] }}), {{ $report['corrected'] }} corrected in the stock.
    </span>
</div>

<div class="inv-sheet">
    @include('activity_logs.reports.inventory_discrepancies_heading', ['report' => $report])

    <div class="table-responsive">
        @include('activity_logs.reports.inventory_discrepancies_table', ['report' => $report])
    </div>

    @include('activity_logs.reports.inventory_discrepancies_notes', ['report' => $report])

    @include('activity_logs.reports.accomplishment_signatures', ['report' => $report])
</div>
