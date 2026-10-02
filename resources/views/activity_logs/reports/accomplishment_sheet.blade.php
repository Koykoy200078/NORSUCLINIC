{{-- The whole report without page styling: the Excel sheet is rendered from this. --}}
@php $width = count($report['columns']) + 2; @endphp
<table>
    <tr><td colspan="{{ $width }}"><strong>{{ getSettingValue('clinic_name') ?: 'Clinic' }}</strong></td></tr>
    <tr><td colspan="{{ $width }}"><strong>ACCOMPLISHMENT REPORT</strong></td></tr>
    <tr><td colspan="{{ $width }}">{{ $report['period'] }}, as per college/department</td></tr>
    @if(! empty($report['filter_summary']))
        <tr><td colspan="{{ $width }}">{{ implode(' | ', $report['filter_summary']) }}</td></tr>
    @endif
</table>

@include('activity_logs.reports.accomplishment_matrix', ['report' => $report])

<table>
    <tr><td colspan="{{ $width }}">Consultations in this report: {{ $report['consultations'] }}. Unclassified consultations (no illness picked): {{ $report['unclassified'] }}.</td></tr>
    <tr><td></td></tr>
    <tr>
        <td>Prepared by:</td>
        <td colspan="{{ $width - 1 }}">Noted by:</td>
    </tr>
    <tr>
        <td><strong>{{ $report['prepared_by']['name'] }}</strong></td>
        <td colspan="{{ $width - 1 }}"><strong>{{ $report['noted_by']['name'] }}</strong></td>
    </tr>
    <tr>
        <td>{{ $report['prepared_by']['title'] }}</td>
        <td colspan="{{ $width - 1 }}">{{ $report['noted_by']['title'] }}</td>
    </tr>
</table>
