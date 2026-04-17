@php
// Don't show the create button when viewing a specific patient's consultation forms
$showCreateButton = !request('patient_id');
$documentModule = request('module', 'consultation');
$isConsultationModule = $documentModule !== 'certificate';
@endphp

@if($showCreateButton)
<div class="d-flex justify-content-end mb-3">
    <a href="{{ 
        (isRole('clinic_admin') ? route('document-issuances.create') : 
        (isRole('staff') ? route('staff.document-issuances.create') : 
        (isRole('doctor') ? route('doctors.document-issuances.create') : route('document-issuances.create'))))
        . ($isConsultationModule ? '?document_type=consultation_form&module=consultation' : '?document_type=medical_certificate&module=certificate')
    }}" class="btn btn-primary">
        <i class="fa-solid {{ $isConsultationModule ? 'fa-notes-medical' : 'fa-file-medical' }}"></i>
        {{ $isConsultationModule ? 'Record Walk-in / Schedule Visit' : 'Create Medical Certificate' }}
    </a>
</div>
@endif