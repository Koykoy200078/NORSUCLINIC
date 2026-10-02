{{-- ACCOMPLISHMENT REPORT tab of Report Generation. $accomplishment comes from AccomplishmentReportBuilder. --}}
@php
    $report = $accomplishment;
    $linkQuery = \Illuminate\Support\Arr::except($exportQuery, ['tab']);
    // The consultations of this report that still have no illness picked, ready to be opened and classified.
    $classifyLink = isRole('clinic_admin')
        ? route('activity-logs.index', array_merge(\Illuminate\Support\Arr::except($linkQuery, ['illness_system_id']), ['tab' => 'visits', 'illness_id' => 'none']))
        : (isRole('staff')
            ? route('staff.activity-logs.index', array_merge(\Illuminate\Support\Arr::except($linkQuery, ['illness_system_id']), ['tab' => 'visits', 'illness_id' => 'none']))
            : route('doctors.activity-logs.index', array_merge(\Illuminate\Support\Arr::except($linkQuery, ['illness_system_id']), ['tab' => 'visits', 'illness_id' => 'none'])));
    $accomplishmentLink = fn (string $format) => isRole('clinic_admin')
        ? route('activity-logs.accomplishment', ['format' => $format] + $linkQuery)
        : (isRole('staff')
            ? route('staff.activity-logs.accomplishment', ['format' => $format] + $linkQuery)
            : route('doctors.activity-logs.accomplishment', ['format' => $format] + $linkQuery));
@endphp

<style>
    .acc-sheet { background: #fff; border: 1px solid #e4e6ef; border-radius: .5rem; padding: 1.5rem; }
    .acc-heading { text-align: center; margin-bottom: 1rem; }
    .acc-clinic { font-weight: 600; }
    .acc-title { font-size: 1.35rem; font-weight: 700; letter-spacing: 1px; }
    .acc-period { color: #5e6278; }
    .acc-filters { color: #7e8299; font-size: .85rem; margin-top: .25rem; }
    .acc-table { width: 100%; border-collapse: collapse; margin-bottom: 1.5rem; font-size: .85rem; }
    .acc-table th, .acc-table td { border: 1px solid #d9d9e3; padding: .3rem .5rem; }
    .acc-table thead th { background: #f1f1f4; }
    .acc-table .item { text-align: left; min-width: 240px; }
    .acc-table .num { text-align: center; min-width: 56px; }
    .acc-table .zero { color: #b5b5c3; }
    .acc-table tr.system td { background: #f5f8fa; font-weight: 700; }
    .acc-table tr.group td { font-style: italic; padding-left: 1.25rem; color: #5e6278; }
    .acc-table tr.subtotal td { font-weight: 700; background: #fafafa; }
    .acc-table tr.grand td { font-weight: 700; background: #eef3f7; }
    .acc-table .empty { text-align: center; color: #7e8299; padding: 1rem; }
    .acc-sign { width: 100%; margin-top: 1.5rem; }
    .acc-sign td { width: 50%; vertical-align: top; padding: 0 1rem; }
    .acc-sign-name { font-weight: 700; margin-top: 2rem; border-top: 1px solid #000; display: inline-block; min-width: 240px; padding-top: .25rem; }
    @media print { .acc-actions { display: none; } .acc-sheet { border: 0; padding: 0; } }
</style>

<div class="acc-actions d-flex flex-wrap gap-2 align-items-center mb-4">
    <a href="{{ $accomplishmentLink('pdf') }}" class="btn btn-sm btn-danger"><i class="fas fa-file-pdf"></i> Download PDF</a>
    <a href="{{ $accomplishmentLink('xlsx') }}" class="btn btn-sm btn-success"><i class="fas fa-file-excel"></i> Download Excel</a>
    <a href="{{ $accomplishmentLink('csv') }}" class="btn btn-sm btn-outline-primary"><i class="fas fa-file-csv"></i> Download CSV</a>
    <span class="text-muted fs-7 ms-2">
        {{ $report['consultations'] }} consultation{{ $report['consultations'] === 1 ? '' : 's' }} in this report.
        <span class="{{ $report['unclassified'] > 0 ? 'text-warning' : '' }}">
            Unclassified consultations (no illness picked): {{ $report['unclassified'] }}.
        </span>
        @if($report['unclassified'] > 0)
            <a href="{{ $classifyLink }}" class="ms-1">Show them and classify</a>
        @endif
    </span>
</div>

<div class="acc-sheet">
    @include('activity_logs.reports.accomplishment_heading', ['report' => $report])

    <div class="table-responsive">
        @include('activity_logs.reports.accomplishment_matrix', ['report' => $report, 'zeroAs' => '-'])
    </div>

    <div class="text-muted fs-7">
        Consultations in this report: {{ $report['consultations'] }}.
        Unclassified consultations (no illness picked): {{ $report['unclassified'] }}.
        A consultation with two illnesses is counted in both rows, so the illness total can be higher than the number of consultations.
    </div>

    @include('activity_logs.reports.accomplishment_signatures', ['report' => $report])

    @if(blank($report['noted_by']['name']))
        <div class="acc-actions alert alert-warning mt-4 mb-0 py-2 fs-7">
            "Noted by" is empty because no University Physician is saved yet. The administrator can set it in Settings &gt; General.
        </div>
    @endif
</div>
