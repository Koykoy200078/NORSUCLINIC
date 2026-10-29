{{-- Full access for all roles that can see the history. A consultation / prescription row belongs to its own record: View only. --}}
<x-row-actions module="dispensing">
    @if ($row->isMedsOnly())
    {{-- A Meds Only record is recorded, changed and deleted in the Meds Only module. --}}
    <a href="{{ getRouteByRole('meds-only.show', [$row->record_id]) }}"
        title="{{ __('messages.common.view') }}" class='dropdown-item px-2 text-primary fs-3 ps-0' role="menuitem" tabindex="-1"> <i class="fas fa-eye text-success"></i>
        <span>{{ __('messages.common.view') }}</span>
    </a>
    @unless (isRole('doctor'))
    <a href="{{ getRouteByRole('meds-only.edit', [$row->record_id]) }}"
        title="{{ __('messages.common.edit') }}" class="dropdown-item px-2 text-primary fs-3 py-2" role="menuitem" tabindex="-1">
        <i class="fa-solid fa-pen-to-square"></i>
        <span>{{ __('messages.common.edit') }}</span>
    </a>
    @endunless
    @elseif ($row->isConsultation())
    <a href="{{ getRouteByRole('dispense-records.consultation', [$row->record_id]) }}"
        title="{{ __('messages.common.view') }}" class='dropdown-item px-2 text-primary fs-3 ps-0' role="menuitem" tabindex="-1"> <i class="fas fa-eye text-success"></i>
        <span>{{ __('messages.common.view') }}</span>
    </a>
    @else
    <a href="{{ getRouteByRole('dispense-records.show', [$row->record_id]) }}"
        title="{{ __('messages.common.view') }}" class='dropdown-item px-2 text-primary fs-3 ps-0' role="menuitem" tabindex="-1"> <i class="fas fa-eye text-success"></i>
        <span>{{ __('messages.common.view') }}</span>
    </a>
    @endif
    @if ($row->isManualDispenseRecord())
    <a href="{{ getRouteByRole('dispense-records.edit', [$row->record_id]) }}"
        title="<?php echo __('messages.common.edit') ?>" class="dropdown-item px-2 edit-btn text-primary fs-3 py-2" role="menuitem" tabindex="-1">
        <i class="fa-solid fa-pen-to-square"></i>
        <span><?php echo __('messages.common.edit') ?></span>
    </a>
    <a title="<?php echo __('messages.common.delete'); ?>" data-id="{{ $row->record_id }}"
        class="dropdown-item medicine-bill-delete-btn px-2 text-danger pe-0 py-2" role="menuitem" href="javascript:void(0)" tabindex="-1">
        <i class="fa-solid fa-trash"></i>
        <span><?php echo __('messages.common.delete'); ?></span>
    </a>
    @endif
</x-row-actions>
