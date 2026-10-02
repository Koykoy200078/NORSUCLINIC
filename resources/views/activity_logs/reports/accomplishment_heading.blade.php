{{-- Title block of the ACCOMPLISHMENT REPORT: clinic, title, period and the filters that were applied. --}}
<div class="acc-heading">
    <div class="acc-clinic">{{ getSettingValue('clinic_name') ?: 'Clinic' }}</div>
    <div class="acc-title">ACCOMPLISHMENT REPORT</div>
    <div class="acc-period">{{ $report['period'] }}, as per college/department</div>
    @if(! empty($report['filter_summary']))
        <div class="acc-filters">{{ implode(' | ', $report['filter_summary']) }}</div>
    @endif
</div>
