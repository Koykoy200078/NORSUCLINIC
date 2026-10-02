<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Accomplishment Report</title>
    <style>
        @page { margin: 28px 26px 34px 26px; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 7.5px; color: #000; }
        .acc-heading { text-align: center; margin-bottom: 8px; }
        .acc-clinic { font-size: 10px; font-weight: bold; }
        .acc-title { font-size: 13px; font-weight: bold; letter-spacing: 1px; margin: 2px 0; }
        .acc-period { font-size: 8.5px; }
        .acc-filters { font-size: 7px; color: #444; margin-top: 2px; }
        .acc-table { width: 100%; border-collapse: collapse; margin-bottom: 10px; }
        .acc-table th, .acc-table td { border: 0.6px solid #000; padding: 2px 3px; }
        .acc-table th { background: #e9e9e9; font-size: 7px; }
        .acc-table .item { text-align: left; width: 27%; }
        .acc-table .num { text-align: center; }
        .acc-table .zero { color: #999; }
        .acc-table tr.system td { background: #f3f3f3; font-weight: bold; }
        .acc-table tr.group td { font-style: italic; padding-left: 8px; }
        .acc-table tr.subtotal td { font-weight: bold; background: #fafafa; }
        .acc-table tr.grand td { font-weight: bold; background: #e9e9e9; }
        .acc-table .empty { text-align: center; color: #666; }
        .acc-table tr { page-break-inside: avoid; }
        .acc-note { margin: 4px 0 14px; font-size: 7px; }
        .acc-sign { width: 100%; margin-top: 18px; page-break-inside: avoid; }
        .acc-sign td { width: 50%; vertical-align: top; font-size: 8px; padding: 0 12px; }
        .acc-sign-name { font-weight: bold; margin-top: 16px; border-top: 0.6px solid #000; display: inline-block; min-width: 190px; padding-top: 2px; }
        .acc-sign-title { margin-top: 1px; }
    </style>
</head>
<body>
    @include('activity_logs.reports.accomplishment_heading', ['report' => $report])
    @include('activity_logs.reports.accomplishment_matrix', ['report' => $report, 'zeroAs' => '-'])
    <div class="acc-note">
        Consultations in this report: {{ $report['consultations'] }}.
        Unclassified consultations (no illness picked): {{ $report['unclassified'] }}.
        Generated {{ $report['generated_at']->format('M d, Y h:i A') }}.
    </div>
    @include('activity_logs.reports.accomplishment_signatures', ['report' => $report])
</body>
</html>
