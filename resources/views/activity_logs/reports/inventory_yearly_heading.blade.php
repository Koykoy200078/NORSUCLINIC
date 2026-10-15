{{-- Title block of the YEARLY MEDICINE INVENTORY: clinic, title and year. --}}
<div class="inv-heading">
    <div class="inv-clinic">{{ getSettingValue('clinic_name') ?: 'Clinic' }}</div>
    <div class="inv-title">YEARLY MEDICINE INVENTORY</div>
    <div class="inv-period">for the year {{ $report['year'] }}</div>
</div>
