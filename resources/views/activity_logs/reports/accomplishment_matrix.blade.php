{{--
    The tables of the ACCOMPLISHMENT REPORT: illness by body system, then the other services, one column per
    college, and (when there are such visits) the patients who only took medicine. Used as it is by the screen, the PDF
    and the Excel sheet (each supplies its own styling for .acc-table).
--}}
@php
    $zero = $zeroAs ?? '0';
    $columns = $report['columns'];
    $span = count($columns) + 2;
    $cells = function (array $counts) use ($columns) {
        return collect($columns)->map(fn ($column) => (int) ($counts[$column['key']] ?? 0))->all();
    };
@endphp

<table class="acc-table">
    <thead>
        <tr>
            <th class="item">ILLNESS / DIAGNOSIS</th>
            @foreach($columns as $column)
                <th class="num" title="{{ $column['name'] }}">{{ $column['label'] }}</th>
            @endforeach
            <th class="num">{{ $report['total_label'] ?? 'TOTAL' }}</th>
        </tr>
    </thead>
    <tbody>
        @forelse($report['illness_sections'] as $section)
            <tr class="system"><td colspan="{{ $span }}">{{ $section['name'] }}</td></tr>
            @foreach($section['groups'] as $group)
                @if($group['label'])
                    <tr class="group"><td colspan="{{ $span }}">{{ $group['label'] }}</td></tr>
                @endif
                @foreach($group['rows'] as $row)
                    <tr>
                        <td class="item">{{ $row['name'] }}</td>
                        @foreach($cells($row['counts']) as $count)
                            <td class="num {{ $count === 0 ? 'zero' : '' }}">{{ $count === 0 ? $zero : $count }}</td>
                        @endforeach
                        <td class="num total {{ $row['total'] === 0 ? 'zero' : '' }}">{{ $row['total'] === 0 ? $zero : $row['total'] }}</td>
                    </tr>
                    @foreach($row['others'] ?? [] as $other)
                        <tr class="other-name">
                            <td class="item other-name-label" style="padding-left: 22px; font-style: italic;">&ndash; {{ $other['name'] }}</td>
                            @foreach($cells($other['counts']) as $count)
                                <td class="num {{ $count === 0 ? 'zero' : '' }}">{{ $count === 0 ? $zero : $count }}</td>
                            @endforeach
                            <td class="num total">{{ $other['total'] }}</td>
                        </tr>
                    @endforeach
                @endforeach
            @endforeach
            <tr class="subtotal">
                <td class="item">Total {{ $section['name'] }}</td>
                @foreach($cells($section['counts']) as $count)
                    <td class="num">{{ $count }}</td>
                @endforeach
                <td class="num total">{{ $section['total'] }}</td>
            </tr>
        @empty
            <tr><td colspan="{{ $span }}" class="empty">No illness was picked on the consultations of this selection.</td></tr>
        @endforelse
        <tr class="grand">
            <td class="item">TOTAL</td>
            @foreach($cells($report['illness_total']['counts']) as $count)
                <td class="num">{{ $count }}</td>
            @endforeach
            <td class="num total">{{ $report['illness_total']['total'] }}</td>
        </tr>
    </tbody>
</table>

<table class="acc-table acc-services">
    <thead>
        <tr>
            <th class="item">OTHER SERVICES</th>
            @foreach($columns as $column)
                <th class="num" title="{{ $column['name'] }}">{{ $column['label'] }}</th>
            @endforeach
            <th class="num">{{ $report['total_label'] ?? 'TOTAL' }}</th>
        </tr>
    </thead>
    <tbody>
        @forelse($report['service_sections'] as $section)
            <tr class="system"><td colspan="{{ $span }}">{{ $section['name'] }}</td></tr>
            @foreach($section['rows'] as $row)
                <tr>
                    <td class="item">{{ $row['name'] }}</td>
                    @foreach($cells($row['counts']) as $count)
                        <td class="num {{ $count === 0 ? 'zero' : '' }}">{{ $count === 0 ? $zero : $count }}</td>
                    @endforeach
                    <td class="num total {{ $row['total'] === 0 ? 'zero' : '' }}">{{ $row['total'] === 0 ? $zero : $row['total'] }}</td>
                </tr>
                @foreach($row['others'] ?? [] as $other)
                    <tr class="other-name">
                        <td class="item other-name-label" style="padding-left: 22px; font-style: italic;">&ndash; {{ $other['name'] }}</td>
                        @foreach($cells($other['counts']) as $count)
                            <td class="num {{ $count === 0 ? 'zero' : '' }}">{{ $count === 0 ? $zero : $count }}</td>
                        @endforeach
                        <td class="num total">{{ $other['total'] }}</td>
                    </tr>
                @endforeach
            @endforeach
            <tr class="subtotal">
                <td class="item">Total {{ $section['name'] }}</td>
                @foreach($cells($section['counts']) as $count)
                    <td class="num">{{ $count }}</td>
                @endforeach
                <td class="num total">{{ $section['total'] }}</td>
            </tr>
        @empty
            <tr><td colspan="{{ $span }}" class="empty">No service for this selection.</td></tr>
        @endforelse
        <tr class="grand">
            <td class="item">TOTAL</td>
            @foreach($cells($report['service_total']['counts']) as $count)
                <td class="num">{{ $count }}</td>
            @endforeach
            <td class="num total">{{ $report['service_total']['total'] }}</td>
        </tr>
    </tbody>
</table>

@if(! empty($report['medicine_only']))
    <table class="acc-table acc-medicine-only">
        <thead>
            <tr>
                <th class="item">MEDICINE ONLY</th>
                @foreach($columns as $column)
                    <th class="num" title="{{ $column['name'] }}">{{ $column['label'] }}</th>
                @endforeach
                <th class="num">{{ $report['total_label'] ?? 'TOTAL' }}</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td class="item">{{ $report['medicine_only']['name'] }}</td>
                @foreach($cells($report['medicine_only']['counts']) as $count)
                    <td class="num {{ $count === 0 ? 'zero' : '' }}">{{ $count === 0 ? $zero : $count }}</td>
                @endforeach
                <td class="num total">{{ $report['medicine_only']['total'] }}</td>
            </tr>
            <tr><td colspan="{{ $span }}" class="empty" style="text-align: left;">Patients who only came for medicine, counted once per visit. They are not part of the consultation, illness or service totals above.</td></tr>
        </tbody>
    </table>
@endif
