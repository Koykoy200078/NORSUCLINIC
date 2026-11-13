<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Medicines with notes</title>
    <style>
        @include('activity_logs.reports.inventory_sheet_pdf_styles')
    </style>
</head>
<body>
    @include('activity_logs.reports.inventory_discrepancies_heading', ['report' => $report])
    @include('activity_logs.reports.inventory_discrepancies_table', ['report' => $report])
    @include('activity_logs.reports.inventory_discrepancies_notes', ['report' => $report])
    @include('activity_logs.reports.accomplishment_signatures', ['report' => $report])
</body>
</html>
