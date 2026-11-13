<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Yearly Medicine Inventory</title>
    <style>
        @include('activity_logs.reports.inventory_sheet_pdf_styles')
        .inv-table .inv-blank { background: #f3f3f3; }
        .inv-table .inv-expired { background: #fff1c9; font-weight: bold; }
    </style>
</head>
<body>
    @include('activity_logs.reports.inventory_yearly_heading', ['report' => $report])
    @include('activity_logs.reports.inventory_yearly_table', ['report' => $report])
    @include('activity_logs.reports.inventory_yearly_notes', ['report' => $report])
    @include('activity_logs.reports.accomplishment_signatures', ['report' => $report])
</body>
</html>
