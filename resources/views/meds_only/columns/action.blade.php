{{-- Everyone who may open Meds Only reads, changes and deletes (doctors too since the owner's decision of 2026-11-14). --}}
<x-row-actions module="meds_only">
    <a href="{{ getRouteByRole('meds-only.show', [$row->id]) }}" title="{{ __('messages.common.view') }}"
        class="dropdown-item px-2 text-primary fs-3 ps-0" role="menuitem" tabindex="-1">
        <i class="fas fa-eye text-success"></i> <span>{{ __('messages.common.view') }}</span>
    </a>
    <a href="{{ getRouteByRole('meds-only.pdf', [$row->id]) }}" title="PDF" target="_blank" rel="noopener"
        class="dropdown-item px-2 text-primary fs-3 py-2" role="menuitem" tabindex="-1">
        <i class="fas fa-file-pdf text-danger"></i> <span>PDF</span>
    </a>
    <a href="{{ getRouteByRole('meds-only.edit', [$row->id]) }}" title="{{ __('messages.common.edit') }}"
        class="dropdown-item px-2 text-primary fs-3 py-2" role="menuitem" tabindex="-1">
        <i class="fa-solid fa-pen-to-square"></i> <span>{{ __('messages.common.edit') }}</span>
    </a>
    <form method="post" action="{{ getRouteByRole('meds-only.destroy', [$row->id]) }}" class="m-0"
        onsubmit="return confirm('Delete this Meds Only record? The medicine goes back into stock.');">
        @csrf
        @method('DELETE')
        <button type="submit" class="dropdown-item px-2 text-danger py-2" role="menuitem" tabindex="-1">
            <i class="fa-solid fa-trash"></i> <span>{{ __('messages.common.delete') }}</span>
        </button>
    </form>
</x-row-actions>
