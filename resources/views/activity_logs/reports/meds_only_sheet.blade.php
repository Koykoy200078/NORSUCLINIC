{{-- The whole MEDS ONLY report without page styling: the Excel sheet is rendered from this. --}}
<table>
    <tr><td colspan="10"><strong>{{ getSettingValue('clinic_name') ?: 'Clinic' }}</strong></td></tr>
    <tr><td colspan="10"><strong>MEDS ONLY</strong></td></tr>
    <tr><td colspan="10">Patients who only came for medicine, {{ $report['period_label'] }}: {{ $report['records'] }} record{{ $report['records'] === 1 ? '' : 's' }}, {{ $report['units'] }} unit{{ $report['units'] === 1 ? '' : 's' }} given</td></tr>
</table>

@include('activity_logs.reports.meds_only_table', ['rows' => $report['rows']])

<table><tr><td></td></tr></table>

@include('activity_logs.reports.meds_only_totals', ['medicines' => $report['medicines']])

<table>
    <tr><td></td></tr>
    <tr>
        <td>Prepared by:</td>
        <td colspan="9">Noted by:</td>
    </tr>
    <tr>
        <td><strong>{{ $report['prepared_by']['name'] }}</strong></td>
        <td colspan="9"><strong>{{ $report['noted_by']['name'] }}</strong></td>
    </tr>
    <tr>
        <td>{{ $report['prepared_by']['title'] }}</td>
        <td colspan="9">{{ $report['noted_by']['title'] }}</td>
    </tr>
</table>
