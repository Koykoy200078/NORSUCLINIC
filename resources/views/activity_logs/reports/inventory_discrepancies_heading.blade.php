{{-- Title block of the MEDICINES WITH NOTES report: clinic, title and what it covers. --}}
<div class="inv-heading">
    <div class="inv-clinic">{{ getSettingValue('clinic_name') ?: 'Clinic' }}</div>
    <div class="inv-title">MEDICINES WITH NOTES</div>
    <div class="inv-period">{{ ucfirst($report['period_label']) }}{{ $report['show'] === 'all' ? ' (every counted medicine)' : ' (a difference or a note)' }}</div>
</div>
