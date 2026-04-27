@php
// Check if consultation form has incomplete assessment or plan (for doctors only)
$needsAttention = false;
if (isset($row) && isRole('doctor') && $row->document_type === 'consultation_form') {
// Check if assessment or plan is truly empty (null or empty string after trimming)
$assessmentEmpty = is_null($row->assessment) || trim($row->assessment) === '';
$planEmpty = is_null($row->plan) || trim($row->plan) === '';
$needsAttention = $assessmentEmpty || $planEmpty;
}

$queryParams = array_filter([
'module' => request()->query('module'),
'patient_id' => request()->query('patient_id'),
], fn($value) => $value !== null && $value !== '');

$showUrl = isRole('clinic_admin') ? route('document-issuances.show', $id) :
(isRole('staff') ? route('staff.document-issuances.show', $id) :
(isRole('doctor') ? route('doctors.document-issuances.show', $id) : route('document-issuances.show', $id)));

$editUrl = isRole('clinic_admin') ? route('document-issuances.edit', $id) :
(isRole('staff') ? route('staff.document-issuances.edit', $id) :
(isRole('doctor') ? route('doctors.document-issuances.edit', $id) : route('document-issuances.edit', $id)));

$exportPdfUrl = isRole('clinic_admin') ? route('document-issuances.export-pdf', ['document_issuance' => $id]) :
(isRole('staff') ? route('staff.document-issuances.export-pdf', ['document_issuance' => $id]) :
(isRole('doctor') ? route('doctors.document-issuances.export-pdf', ['document_issuance' => $id]) : route('document-issuances.export-pdf', ['document_issuance' => $id])));

if (!empty($queryParams)) {
$queryString = http_build_query($queryParams);
$showUrl .= '?' . $queryString;
$editUrl .= '?' . $queryString;
}
@endphp

<div class="d-flex justify-content-center align-items-center gap-1">
    <a href="{{ $showUrl }}"
        class="btn px-1 text-primary fs-3"
        data-bs-toggle="tooltip"
        data-bs-original-title="{{ __('messages.common.view') }}">
        <i class="fas fa-eye"></i>
    </a>

    <a href="{{ $editUrl }}"
        class="btn px-1 {{ $needsAttention ? 'text-warning' : 'text-primary' }} fs-3 {{ $needsAttention ? 'pulse-animation' : '' }} position-relative"
        data-bs-toggle="tooltip"
        data-bs-original-title="{{ $needsAttention ? 'Complete Assessment/Plan Required' : __('messages.common.edit') }}">
        <i class="fas fa-pencil"></i>
        @if($needsAttention)
        <i class="fas fa-exclamation-circle text-danger" style="font-size: 0.5em; position: absolute; top: 5px; right: 0;"></i>
        @endif
    </a>

    <a href="{{ $exportPdfUrl }}"
        target="_blank"
        class="btn px-1 text-success fs-3"
        data-bs-toggle="tooltip"
        data-bs-original-title="Export PDF">
        <i class="fas fa-file-pdf"></i>
    </a>

    @if(isset($row) && ($row->document_type == 'excuse_slip' || $row->document_type == 'medical_certificate'))
    <a href="{{ $exportPdfUrl }}{{ str_contains($exportPdfUrl, '?') ? '&' : '?' }}action=print"
        target="_blank"
        class="btn px-1 text-info fs-3"
        data-bs-toggle="tooltip"
        data-bs-original-title="Direct Print (Fast)">
        <i class="fas fa-print"></i>
    </a>
    @endif
</div>

@if($needsAttention)
<style>
    @keyframes pulse {

        0%,
        100% {
            opacity: 1;
        }

        50% {
            opacity: 0.6;
        }
    }

    .pulse-animation {
        animation: pulse 2s ease-in-out infinite;
    }
</style>
@endif