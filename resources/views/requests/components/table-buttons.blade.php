@php
// Don't show the create button when viewing a specific patient's consultation forms
$showCreateButton = !request('patient_id');
@endphp

@if($showCreateButton)
<div class="d-flex justify-content-end mb-3">
    <a href="{{ 
        (isRole('clinic_admin') ? route('request-documents.create') : 
        (isRole('staff') ? route('staff.request-documents.create') : 
        (isRole('doctor') ? route('doctors.request-documents.create') : route('request-documents.create'))))
        . '?document_type=consultation_form'
    }}" class="btn btn-primary">
        <i class="fa-solid fa-file-medical"></i> Create Consultation Form
    </a>
</div>
@endif
