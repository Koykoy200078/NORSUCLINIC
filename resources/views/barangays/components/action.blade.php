<x-row-actions module="cities">
    <a href="javascript:void(0)" title="{{ __('messages.common.edit') }}"
       class="dropdown-item px-1 text-primary fs-3 barangay-edit-btn" data-bs-toggle="tooltip"
       data-bs-original-title="{{ __('messages.common.edit') }}" data-id="{{$row->id}}" role="menuitem" tabindex="-1">
        <i class="fa-solid fa-pen-to-square"></i>
        <span>{{ __('messages.common.edit') }}</span>
    </a>
    <a href="javascript:void(0)" data-id="{{ $row->id }}" title="{{ __('messages.common.delete') }}"  data-bs-toggle="tooltip"
       data-bs-original-title="{{ __('messages.common.delete') }}"
       class="dropdown-item px-1 text-danger fs-3 barangay-delete-btn" role="menuitem" tabindex="-1">
        <i class="fa-solid fa-trash"></i>
        <span>{{ __('messages.common.delete') }}</span>
    </a>
</x-row-actions>
