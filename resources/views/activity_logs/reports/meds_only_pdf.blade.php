<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Meds Only</title>
    <style>
        @page { margin: 28px 26px 34px 26px; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 7.5px; color: #000; }
        .inv-heading { text-align: center; margin-bottom: 8px; }
        .inv-clinic { font-size: 10px; font-weight: bold; }
        .inv-title { font-size: 13px; font-weight: bold; letter-spacing: 1px; margin: 2px 0; }
        .inv-period { font-size: 8.5px; }
        .inv-table { width: 100%; border-collapse: collapse; margin-bottom: 8px; }
        .inv-table th, .inv-table td { border: 0.6px solid #000; padding: 2px 3px; }
        .inv-table th { background: #e9e9e9; font-size: 7px; text-align: center; }
        .inv-table .item { text-align: left; }
        .inv-table .num { text-align: center; }
        .inv-table .inv-notes { font-size: 6.5px; color: #444; }
        .inv-table .empty { text-align: center; color: #666; }
        .inv-table tr { page-break-inside: avoid; }
        .inv-note { margin: 4px 0 14px; font-size: 7px; }
        .inv-sub { font-size: 8px; font-weight: bold; margin: 8px 0 3px; }
        .acc-sign { width: 100%; margin-top: 18px; page-break-inside: avoid; }
        .acc-sign td { width: 50%; vertical-align: top; font-size: 8px; padding: 0 12px; }
        .acc-sign-name { font-weight: bold; margin-top: 16px; border-top: 0.6px solid #000; display: inline-block; min-width: 190px; padding-top: 2px; }
        .acc-sign-title { margin-top: 1px; }
    </style>
</head>
<body>
    @include('activity_logs.reports.meds_only_heading', ['report' => $report])
    <div class="inv-sub">Records</div>
    @include('activity_logs.reports.meds_only_table', ['rows' => $report['rows']])
    <div class="inv-sub">Medicine given</div>
    @include('activity_logs.reports.meds_only_totals', ['medicines' => $report['medicines']])
    <div class="inv-note">A Meds Only record is a person who came for medicine only (no consultation). Dates are the days of the records. Generated {{ $report['generated_at']->format('M d, Y h:i A') }}.</div>
    @include('activity_logs.reports.accomplishment_signatures', ['report' => $report])
</body>
</html>
