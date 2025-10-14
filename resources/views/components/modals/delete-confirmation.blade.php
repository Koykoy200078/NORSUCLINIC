{{-- Delete Confirmation Modal --}}
<div class="modal fade" id="deleteConfirmationModal" tabindex="-1" aria-labelledby="deleteConfirmationModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="deleteConfirmationModalLabel">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    {{ __('messages.common.confirm_delete') }}
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center py-4">
                <div class="mb-3">
                    <i class="fas fa-trash-alt text-danger" style="font-size: 3rem;"></i>
                </div>
                <h6 class="mb-3">{{ __('messages.common.delete_confirmation') }}</h6>
                <p class="text-muted delete-message">
                    {{ __('messages.common.delete_warning') }}
                </p>
            </div>
            <div class="modal-footer justify-content-center">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-1"></i>
                    {{ __('messages.common.cancel') }}
                </button>
                <button type="button" class="btn btn-danger confirm-delete-btn">
                    <i class="fas fa-trash me-1"></i>
                    {{ __('messages.common.delete') }}
                </button>
            </div>
        </div>
    </div>
</div>
