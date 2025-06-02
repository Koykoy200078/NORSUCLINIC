<div class="d-flex justify-content-center">
    <a href="{{ route('request-documents.show', $id) }}"
        class="btn px-1 text-primary fs-3"
        data-bs-toggle="tooltip"
        data-bs-original-title="{{ __('messages.common.view') }}">
        <i class="fas fa-eye"></i>
    </a>

    <a href="{{ route('request-documents.edit', $id) }}"
        class="btn px-1 text-primary fs-3"
        data-bs-toggle="tooltip"
        data-bs-original-title="{{ __('messages.common.edit') }}">
        <i class="fas fa-pencil"></i>
    </a>
</div>