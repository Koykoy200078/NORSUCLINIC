{{-- Title block of the MEDS ONLY report: clinic, title and what it covers. --}}
<div class="inv-heading">
    <div class="inv-clinic">{{ getSettingValue('clinic_name') ?: 'Clinic' }}</div>
    <div class="inv-title">MEDS ONLY</div>
    <div class="inv-period">Patients who only came for medicine, {{ $report['period_label'] }}: {{ $report['records'] }} record{{ $report['records'] === 1 ? '' : 's' }}, {{ $report['units'] }} unit{{ $report['units'] === 1 ? '' : 's' }} given</div>
</div>
