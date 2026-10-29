{{--
    The table of the MEDICINES WITH NOTES report. The screen, the PDF and the Excel sheet all render this one table, so they
    always show the same figures. $report comes from App\Services\Reports\InventoryDiscrepancyReportBuilder.
--}}
<table class="inv-table">
    <thead>
        <tr>
            <th class="item">Sheet</th>
            <th>Day counted</th>
            <th class="item">Medicine</th>
            <th>Strength</th>
            <th>Unit</th>
            <th class="num">System</th>
            <th class="num">Counted</th>
            <th class="num">Difference</th>
            <th class="item">Reason</th>
            <th class="item">Note</th>
            <th>Stock corrected</th>
        </tr>
    </thead>
    <tbody>
        @forelse($report['rows'] as $row)
            <tr>
                <td class="item">{{ $row['sheet'] }}</td>
                <td>{{ $row['counted_on'] }}</td>
                <td class="item">{{ $row['name'] }}</td>
                <td>{{ $row['strength'] }}</td>
                <td>{{ $row['unit'] }}</td>
                <td class="num">{{ $row['system'] }}</td>
                <td class="num">{{ $row['counted'] }}</td>
                <td class="num {{ $row['difference'] === 0 ? 'zero' : 'inv-check' }}">{{ $row['difference'] }}</td>
                <td class="item">{{ $row['reason'] }}</td>
                <td class="item inv-notes">{{ $row['note'] }}</td>
                <td>{{ $row['corrected'] }}</td>
            </tr>
        @empty
            <tr><td colspan="11" class="empty">No finalized count sheet has a medicine to report for this view.</td></tr>
        @endforelse
    </tbody>
</table>
