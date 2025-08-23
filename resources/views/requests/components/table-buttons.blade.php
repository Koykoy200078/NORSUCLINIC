<div class="d-flex justify-content-end mb-3">
    <a href="{{ 
        isRole('clinic_admin') ? route('request-documents.create') : 
        (isRole('staff') ? route('staff.request-documents.create') : 
        (isRole('doctor') ? route('doctors.request-documents.create') : route('request-documents.create')))
    }}" class="btn btn-primary">
        Create New Request
    </a>
</div>