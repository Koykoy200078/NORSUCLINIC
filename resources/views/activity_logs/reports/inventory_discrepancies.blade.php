{{-- MEDICINES WITH NOTES of the Medicine Inventory tab of Report Generation. $report comes from InventoryDiscrepancyReportBuilder. --}}
@php
    $query = array_filter(['year' => $report['year'], 'period' => $report['period'], 'show' => $report['show']], fn ($value) => $value !== null && $value !== '');
    $download = fn (string $format) => getRouteByRole('activity-logs.inventory-discrepancies', ['format' => $format] + $query);
@endphp

<style>
    .inv-sheet { background: #fff; border: 1px solid #e4e6ef; border-radius: .5rem; padding: 1.5rem; }
    .inv-heading { text-align: center; margin-bottom: 1rem; }
    .inv-clinic { font-weight: 600; }
    .inv-title { font-size: 1.35rem; font-weight: 700; letter-spacing: 1px; }
    .inv-period { color: #5e6278; }
    .inv-table { width: 100%; border-collapse: collapse; margin-bottom: 1rem; font-size: .85rem; }
    .inv-table th, .inv-table td { border: 1px solid #d9d9e3; padding: .3rem .5rem; }
    .inv-table thead th { background: #f1f1f4; text-align: center; }
    .inv-table .item { text-align: left; }
    .inv-table .num { text-align: center; min-width: 64px; }
    .inv-table .zero { color: #b5b5c3; }
    .inv-table .inv-notes { font-size: .78rem; color: #7e8299; }
    .inv-table .inv-check { color: #f1416c; }
    .inv-table .empty { text-align: center; color: #7e8299; padding: 1rem; }
    .inv-note { color: #7e8299; font-size: .8rem; }
    .acc-sign { width: 100%; margin-top: 1.5rem; }
    .acc-sign td { width: 50%; vertical-align: top; padding: 0 1rem; }
    .acc-sign-name { font-weight: 700; margin-top: 2rem; border-top: 1px solid #000; display: inline-block; min-width: 240px; padding-top: .25rem; }
    @media print { .inv-actions { display: none; } .inv-sheet { border: 0; padding: 0; } }
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
