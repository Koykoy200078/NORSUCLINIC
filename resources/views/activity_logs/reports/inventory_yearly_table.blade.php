{{--
    The table of the YEARLY MEDICINE INVENTORY. The screen, the PDF and the Excel sheet all render this one table, so
    they always show the same figures. $report comes from App\Services\Reports\YearlyInventoryReportBuilder.
--}}
@php
    $span = 7 + count($report['quarters']);
@endphp
<table class="inv-table">
    <thead>
        <tr>
            <th class="item">Medicine</th>
            <th class="item">Generic</th>
            <th>Strength</th>
            <th>Unit</th>
            <th class="num">Left from last year</th>
            <th class="num">Additional</th>
            @foreach($report['quarters'] as $quarter)
                <th class="num" title="{{ $quarter['name'] }}">{{ $quarter['label'] }} remaining{{ $quarter['to_date'] ? ' (to date)' : '' }}</th>
            @endforeach
            <th class="item">Notes</th>
        </tr>
    </thead>
    <tbody>
        @forelse($report['rows'] as $row)
            <tr>
                <td class="item">{{ $row['name'] }}</td>
                <td class="item">{{ $row['generic'] }}</td>
                <td>{{ $row['strength'] }}</td>
                <td>{{ $row['unit'] }}</td>
                <td class="num {{ $row['left'] === 0 ? 'zero' : '' }}">{{ $row['left'] }}</td>
                <td class="num {{ $row['additional'] === 0 ? 'zero' : '' }}">{{ $row['additional'] }}</td>
                @foreach($report['quarters'] as $quarter)
                    @php($cell = $row['quarters'][$quarter['key']])
                    @if($cell['remaining'] === null)
                        <td class="inv-blank"></td>
                    @else
                        <td class="num {{ $cell['expired'] > 0 ? 'inv-expired' : ($cell['remaining'] === 0 ? 'zero' : '') }}" @if($cell['expired'] > 0) title="{{ $cell['expired'] }} expired" @endif>{{ $cell['remaining'] }}</td>
                    @endif
                @endforeach
                <td class="item inv-notes {{ $row['consistent'] ? '' : 'inv-check' }}">{{ $row['notes'] }}</td>
            </tr>
        @empty
            <tr><td colspan="{{ $span }}" class="empty">No stock or stock movement was recorded in {{ $report['year'] }}.</td></tr>
        @endforelse
    </tbody>
</table>
