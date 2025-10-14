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
        class="btn px-1 text-primary fs-3"
        data-bs-toggle="tooltip"
        data-bs-original-title="{{ __('messages.common.edit') }}">
        <i class="fas fa-pencil"></i>
    </a>
</div>
