{{-- Full access for all roles that can see the history. A consultation / prescription row belongs to its own record: View only. --}}
<div class="d-flex align-items-center justify-content-center">
    @if ($row->isConsultation())
    <a href="{{ getRouteByRole('dispense-records.consultation', [$row->record_id]) }}"
        title="{{ __('messages.common.view') }}" class='btn px-2 text-primary fs-3 ps-0'> <i class="fas fa-eye text-success"></i></a>
    @else
    <a href="{{ getRouteByRole('dispense-records.show', [$row->record_id]) }}"
        title="{{ __('messages.common.view') }}" class='btn px-2 text-primary fs-3 ps-0'> <i class="fas fa-eye text-success"></i></a>
    @endif
    @if ($row->isManualDispenseRecord())
    <a href="{{ getRouteByRole('dispense-records.edit', [$row->record_id]) }}"
        title="<?php echo __('messages.common.edit') ?>" class="btn px-2 edit-btn text-primary fs-3 py-2">
        <i class="fa-solid fa-pen-to-square"></i>
    </a>
    <a title="<?php echo __('messages.common.delete'); ?>" data-id="{{ $row->record_id }}"
        class="btn medicine-bill-delete-btn px-2 text-danger pe-0 py-2">
        <i class="fa-solid fa-trash"></i>
    </a>
    @endif
</div>
