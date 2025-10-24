@php
// Check if consultation form has incomplete assessment or plan (for doctors only)
$needsAttention = false;
if (isset($row) && isRole('doctor') && $row->document_type === 'consultation_form') {
// Check if assessment or plan is truly empty (null or empty string after trimming)
$assessmentEmpty = is_null($row->assessment) || trim($row->assessment) === '';
$planEmpty = is_null($row->plan) || trim($row->plan) === '';
$needsAttention = $assessmentEmpty || $planEmpty;
}
@endphp

<div class="d-flex justify-content-center">
    <a href="{{ 
        isRole('clinic_admin') ? route('request-documents.show', $id) : 
        (isRole('staff') ? route('staff.request-documents.show', $id) : 
        (isRole('doctor') ? route('doctors.request-documents.show', $id) : route('request-documents.show', $id)))
    }}"
        class="btn px-1 text-primary fs-3"
        data-bs-toggle="tooltip"
        data-bs-original-title="{{ __('messages.common.view') }}">
        <i class="fas fa-eye"></i>
    </a>

    <a href="{{ 
        isRole('clinic_admin') ? route('request-documents.edit', $id) : 
        (isRole('staff') ? route('staff.request-documents.edit', $id) : 
        (isRole('doctor') ? route('doctors.request-documents.edit', $id) : route('request-documents.edit', $id)))
    }}"
        class="btn px-1 {{ $needsAttention ? 'text-warning' : 'text-primary' }} fs-3 {{ $needsAttention ? 'pulse-animation' : '' }}"
        data-bs-toggle="tooltip"
        data-bs-original-title="{{ $needsAttention ? 'Complete Assessment/Plan Required' : __('messages.common.edit') }}">
        <i class="fas fa-pencil"></i>
        @if($needsAttention)
        <i class="fas fa-exclamation-circle" style="font-size: 0.6em; position: absolute; top: 0; right: 0;"></i>
        @endif
    </a>
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