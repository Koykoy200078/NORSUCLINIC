{{-- The visits of the report when patients also came for medicine only (the owner's decision, 2026-11-14): one sentence for the
     screen, the PDF and the Excel sheet. Nothing when there is no Meds Only visit (the visits are then the consultations). --}}
@if(! empty($report['medicine_only']))
Visits in this report: {{ $report['visits'] }} ({{ $report['consultations'] }} consultation{{ $report['consultations'] === 1 ? '' : 's' }} + {{ $report['medicine_only']['total'] }} medicine only).
@endif
