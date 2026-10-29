{{-- The whole MEDICINES WITH NOTES report without page styling: the Excel sheet is rendered from this. --}}
<table>
    <tr><td colspan="11"><strong>{{ getSettingValue('clinic_name') ?: 'Clinic' }}</strong></td></tr>
    <tr><td colspan="11"><strong>MEDICINES WITH NOTES</strong></td></tr>
    <tr><td colspan="11">{{ ucfirst($report['period_label']) }}{{ $report['show'] === 'all' ? ' (every counted medicine)' : ' (a difference or a note)' }}</td></tr>
</table>

@include('activity_logs.reports.inventory_discrepancies_table', ['report' => $report])

<table>
    <tr><td></td></tr>
    <tr>
        <td>Prepared by:</td>
        <td colspan="10">Noted by:</td>
    </tr>
    <tr>
        <td><strong>{{ $report['prepared_by']['name'] }}</strong></td>
        <td colspan="10"><strong>{{ $report['noted_by']['name'] }}</strong></td>
    </tr>
    <tr>
        <td>{{ $report['prepared_by']['title'] }}</td>
        <td colspan="10">{{ $report['noted_by']['title'] }}</td>
    </tr>
</table>
