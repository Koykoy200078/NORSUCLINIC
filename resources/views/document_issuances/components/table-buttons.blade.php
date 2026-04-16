@php
// Don't show the create button when viewing a specific patient's consultation forms
$showCreateButton = !request('patient_id');
@endphp

@if($showCreateButton)
<div class="d-flex justify-content-end mb-3">
    <a href="{{ 
        (isRole('clinic_admin') ? route('document-issuances.create') : 
        (isRole('staff') ? route('staff.document-issuances.create') : 
        (isRole('doctor') ? route('doctors.document-issuances.create') : route('document-issuances.create'))))
        . '?document_type=consultation_form'
    }}" class="btn btn-primary">
        <i class="fa-solid fa-file-medical"></i> Create Consultation Form
    </a>
</div>
@endif