{{--
    The records of the MEDS ONLY report. The screen, the PDF and the Excel sheet all render this one table, so they always show the
    same rows. $rows are the arrays of App\Services\Reports\MedsOnlyReportBuilder::row().
--}}
<table class="inv-table">
    <thead>
        <tr>
            <th>Date</th>
            <th class="item">Name</th>
            <th>Type</th>
            <th>Campus</th>
            <th class="item">College</th>
            <th class="item">Course</th>
            <th>Year level</th>
            <th class="item">Drugs</th>
            <th>Recorded by</th>
            <th class="item">Note</th>
        </tr>
    </thead>
    <tbody>
        @forelse($rows as $row)
            <tr>
                <td>{{ $row['date'] }}</td>
                <td class="item">{{ $row['name'] }}</td>
                <td>{{ $row['type'] }}</td>
                <td>{{ $row['campus'] }}</td>
                <td class="item">{{ $row['college'] }}</td>
                <td class="item">{{ $row['course'] }}</td>
                <td>{{ $row['year_level'] }}</td>
                <td class="item">{{ $row['drugs'] }}</td>
                <td>{{ $row['recorded_by'] }}</td>
                <td class="item inv-notes">{{ $row['note'] }}</td>
            </tr>
        @empty
            <tr><td colspan="10" class="empty">No Meds Only record matches this view.</td></tr>
        @endforelse
    </tbody>
</table>
