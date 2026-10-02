{{-- Prepared by (the signed-in user) and Noted by (the University Physician saved in Settings). --}}
<table class="acc-sign">
    <tr>
        <td>
            <div class="acc-sign-label">Prepared by:</div>
            <div class="acc-sign-name">{{ $report['prepared_by']['name'] ?: '______________________' }}</div>
            <div class="acc-sign-title">{{ $report['prepared_by']['title'] }}</div>
        </td>
        <td>
            <div class="acc-sign-label">Noted by:</div>
            <div class="acc-sign-name">{{ $report['noted_by']['name'] ?: '______________________' }}</div>
            <div class="acc-sign-title">{{ $report['noted_by']['title'] }}</div>
        </td>
    </tr>
</table>
