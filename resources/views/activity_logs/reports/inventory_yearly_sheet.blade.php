{{-- The whole yearly inventory without page styling: the Excel sheet is rendered from this. --}}
@php $width = 7 + count($report['quarters']); @endphp
<table>
    <tr><td colspan="{{ $width }}"><strong>{{ getSettingValue('clinic_name') ?: 'Clinic' }}</strong></td></tr>
    <tr><td colspan="{{ $width }}"><strong>YEARLY MEDICINE INVENTORY</strong></td></tr>
    <tr><td colspan="{{ $width }}">for the year {{ $report['year'] }}</td></tr>
</table>

@include('activity_logs.reports.inventory_yearly_table', ['report' => $report])

<table>
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
