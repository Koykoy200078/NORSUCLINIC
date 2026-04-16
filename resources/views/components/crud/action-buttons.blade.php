@props([
'model',
'routes' => [],
'permissions' => [],
'size' => 'sm',
'showTooltips' => true,
'extraActions' => []
])

<div class="d-flex align-items-center gap-2">
    @if(isset($routes['show']) && (!isset($permissions['show']) || auth()->user()->can($permissions['show'])))
    <a href="{{ route($routes['show'], $model) }}"
        @if($showTooltips) title="{{ __('messages.common.view') }}" @endif
        class="btn btn-{{ $size }} btn-outline-info action-btn">
        <i class="fas fa-eye"></i>
    </a>
    @endif

    @if(isset($routes['edit']) && (!isset($permissions['edit']) || auth()->user()->can($permissions['edit'])))
    <a href="{{ route($routes['edit'], $model) }}"
        @if($showTooltips) title="{{ __('messages.common.edit') }}" @endif
        class="btn btn-{{ $size }} btn-outline-primary action-btn">
        <i class="fas fa-edit"></i>
    </a>
    @endif

    @if(isset($routes['pdf']) && (!isset($permissions['pdf']) || auth()->user()->can($permissions['pdf'])))
    <a href="{{ route($routes['pdf'], $model) }}"
        @if($showTooltips) title="{{ __('messages.common.download_pdf') }}" @endif
        class="btn btn-{{ $size }} btn-outline-secondary action-btn"
        target="_blank">
        <i class="fas fa-file-pdf"></i>
    </a>
    @endif

    @foreach($extraActions as $action)
    @if(!isset($action['permission']) || auth()->user()->can($action['permission']))
    <a href="{{ route($action['route'], $model) }}"
        @if($showTooltips && isset($action['title'])) title="{{ $action['title'] }}" @endif
        class="btn btn-{{ $size }} btn-outline-{{ $action['color'] ?? 'secondary' }} action-btn"
        @if(isset($action['target'])) target="{{ $action['target'] }}" @endif>
        <i class="{{ $action['icon'] }}"></i>
    </a>
    @endif
    @endforeach

    @if(isset($routes['delete']) && (!isset($permissions['delete']) || auth()->user()->can($permissions['delete'])))
    <button type="button"
        @if($showTooltips) title="{{ __('messages.common.delete') }}" @endif
        class="btn btn-{{ $size }} btn-outline-danger action-btn delete-btn"
        data-url="{{ route($routes['delete'], $model) }}"
        data-name="{{ $model->name ?? ($model->user->university_id_number ?? $model->patient_unique_id ?? $model->id) }}"
        data-bs-toggle="modal"
        data-bs-target="#deleteConfirmationModal">
        <i class="fas fa-trash"></i>
    </button>
    @endif
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Tooltip initialization
        @if($showTooltips)
        const tooltipTriggerList = [].slice.call(document.querySelectorAll('[title]'));
        const tooltipList = tooltipTriggerList.map(function(tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
        @endif

        // Delete confirmation handler
        const deleteButtons = document.querySelectorAll('.delete-btn');
        deleteButtons.forEach(button => {
            button.addEventListener('click', function() {
                const url = this.dataset.url;
                const name = this.dataset.name || 'this item';

                // Set confirmation modal content
                const modal = document.getElementById('deleteConfirmationModal');
                if (modal) {
                    const messageElement = modal.querySelector('.delete-message');
                    const confirmButton = modal.querySelector('.confirm-delete-btn');

                    if (messageElement) {
                        messageElement.textContent = `Are you sure you want to delete "${name}"?`;
                    }

                    if (confirmButton) {
                        confirmButton.onclick = function() {
                            // Create and submit form for DELETE request
                            const form = document.createElement('form');
                            form.method = 'POST';
                            form.action = url;

                            // Add CSRF token
                            const csrfInput = document.createElement('input');
                            csrfInput.type = 'hidden';
                            csrfInput.name = '_token';
                            csrfInput.value = '{{ csrf_token() }}';
                            form.appendChild(csrfInput);

                            // Add method override for DELETE
                            const methodInput = document.createElement('input');
                            methodInput.type = 'hidden';
                            methodInput.name = '_method';
                            methodInput.value = 'DELETE';
                            form.appendChild(methodInput);

                            document.body.appendChild(form);
                            form.submit();
                        };
                    }
                }
            });
        });
    });
</script>
@endpush