{{-- The units given of each medicine in the MEDS ONLY report (the records it covers). $medicines come from MedsOnlyReportBuilder::medicines(). --}}
<table class="inv-table">
    <thead>
        <tr>
            <th class="item">Medicine</th>
            <th>Strength</th>
            <th>Unit</th>
            <th class="num">Records</th>
            <th class="num">Units given</th>
        </tr>
    </thead>
    <tbody>
        @forelse($medicines as $medicine)
            <tr>
                <td class="item">{{ $medicine['name'] }}</td>
                <td>{{ $medicine['strength'] }}</td>
                <td>{{ $medicine['unit'] }}</td>
                <td class="num">{{ $medicine['records'] }}</td>
                <td class="num">{{ $medicine['units'] }}</td>
            </tr>
        @empty
            <tr><td colspan="5" class="empty">No medicine was given in this view.</td></tr>
        @endforelse
    </tbody>
</table>
