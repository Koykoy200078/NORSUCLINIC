@php
// Check if consultation form has incomplete assessment or plan (for doctors only)
$needsAttention = false;
if (isset($row) && isRole('doctor') && $row->document_type === 'consultation_form') {
$needsAttention = $row->missingAssessmentOrPlan() !== [];
}

$queryParams = array_filter([
'module' => request()->query('module'),
'patient_id' => request()->query('patient_id'),
], fn($value) => $value !== null && $value !== '');

$showUrl = getRouteByRole('document-issuances.show', [$id]);

$editUrl = getRouteByRole('document-issuances.edit', [$id]);

$exportPdfUrl = getRouteByRole('document-issuances.export-pdf', ['document_issuance' => $id]);

if (!empty($queryParams)) {
$queryString = http_build_query($queryParams);
$showUrl .= '?' . $queryString;
$editUrl .= '?' . $queryString;
}
@endphp

<x-row-actions module="document_issuances">
    <a href="{{ $showUrl }}"
        class="dropdown-item px-1 text-primary fs-3"
        data-bs-toggle="tooltip"
        data-bs-original-title="{{ __('messages.common.view') }}" role="menuitem" tabindex="-1">
        <i class="fas fa-eye"></i>
        <span>{{ __('messages.common.view') }}</span>
    </a>

    <a href="{{ $editUrl }}"
        class="dropdown-item px-1 {{ $needsAttention ? 'text-warning' : 'text-primary' }} fs-3 {{ $needsAttention ? 'pulse-animation' : '' }} position-relative"
        data-bs-toggle="tooltip"
        data-bs-original-title="{{ $needsAttention ? 'Complete Assessment/Plan Required' : __('messages.common.edit') }}" role="menuitem" tabindex="-1">
        <i class="fas fa-pencil"></i>
        @if($needsAttention)
        <i class="fas fa-exclamation-circle text-danger" style="font-size: 0.5em; position: absolute; top: 5px; right: 0;"></i>
        @endif
        <span>{{ $needsAttention ? 'Complete Assessment/Plan Required' : __('messages.common.edit') }}</span>
    </a>

    <a href="{{ $exportPdfUrl }}"
        target="_blank"
        class="dropdown-item px-1 text-success fs-3"
        data-bs-toggle="tooltip"
        data-bs-original-title="Export PDF" role="menuitem" tabindex="-1">
        <i class="fas fa-file-pdf"></i>
        <span>Export PDF</span>
    </a>

    @if(isset($row) && ($row->document_type == 'excuse_slip' || $row->document_type == 'medical_certificate'))
    <a href="{{ $exportPdfUrl }}{{ str_contains($exportPdfUrl, '?') ? '&' : '?' }}action=print"
        target="_blank"
        class="dropdown-item px-1 text-info fs-3"
        data-bs-toggle="tooltip"
        data-bs-original-title="Direct Print (Fast)" role="menuitem" tabindex="-1">
        <i class="fas fa-print"></i>
        <span>Direct Print (Fast)</span>
    </a>
    @endif
</x-row-actions>

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