{{--
    MEDS ONLY tab of Report Generation: the patients who only came for medicine. $reports is the page of records (the same query the CSV
    streams), $medsOnlyMedicines the units given of each medicine over all the records of the view, $exportQuery the filters of the screen.
--}}
@php
    $downloadQuery = \Illuminate\Support\Arr::except($exportQuery, ['tab']);
    $download = fn (string $format) => getRouteByRole('activity-logs.meds-only', ['format' => $format] + $downloadQuery);
    $rows = $reports->getCollection()->map(fn ($record) => \App\Services\Reports\MedsOnlyReportBuilder::row($record))->all();
@endphp

<style>
    .inv-sheet { background: #fff; border: 1px solid #e4e6ef; border-radius: .5rem; padding: 1.5rem; }
    .inv-table { width: 100%; border-collapse: collapse; margin-bottom: 1rem; font-size: .85rem; }
    .inv-table th, .inv-table td { border: 1px solid #d9d9e3; padding: .3rem .5rem; }
    .inv-table thead th { background: #f1f1f4; text-align: center; }
    .inv-table .item { text-align: left; }
    .inv-table .num { text-align: center; min-width: 64px; }
    .inv-table .inv-notes { font-size: .78rem; color: #7e8299; }
    .inv-table .empty { text-align: center; color: #7e8299; padding: 1rem; }
    .inv-sub { font-weight: 700; margin: 1rem 0 .5rem; }
    @media print { .inv-actions { display: none; } .inv-sheet { border: 0; padding: 0; } }
</style>

<div class="inv-actions d-flex flex-wrap gap-2 align-items-center mb-4">
    <a href="{{ $download('pdf') }}" class="btn btn-sm btn-danger"><i class="fas fa-file-pdf"></i> Download PDF</a>
    <a href="{{ $download('xlsx') }}" class="btn btn-sm btn-success"><i class="fas fa-file-excel"></i> Download Excel</a>
    <span class="text-muted fs-7 ms-2">
        {{ $reports->total() }} record{{ $reports->total() === 1 ? '' : 's' }} of people who only came for medicine;
        {{ array_sum(array_column($medsOnlyMedicines, 'units')) }} unit{{ array_sum(array_column($medsOnlyMedicines, 'units')) === 1 ? '' : 's' }} given in all of them (the CSV and the files hold every record, not only this page).
    </span>
</div>

<div class="inv-sheet">
    <div class="inv-sub mt-0">Records</div>
    <div class="table-responsive">
        @include('activity_logs.reports.meds_only_table', ['rows' => $rows])
    </div>

    <div class="inv-sub">Medicine given (all the records of this view)</div>
    <div class="table-responsive">
        @include('activity_logs.reports.meds_only_totals', ['medicines' => $medsOnlyMedicines])
    </div>
</div>
