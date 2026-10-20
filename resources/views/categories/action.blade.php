{{-- Full CRUD access for all roles including doctors --}}
<x-row-actions module="inventory">
    <a title="{{__('messages.common.edit')}}" data-id="{{ $row->id }}"
        class="dropdown-item px-1 text-primary fs-3 ps-0 category-edit-btn" role="menuitem" href="javascript:void(0)" tabindex="-1">
        <i class="fa-solid fa-pen-to-square"></i>
        <span>{{__('messages.common.edit')}}</span>
    </a>
    <a title="{{__('messages.common.delete')}}" href="javascript:void(0)" data-id="{{ $row->id }}" wire:key="{{$row->id}}"
        class="dropdown-item px-1 text-danger fs-3 ps-0 category-delete-btn" role="menuitem" tabindex="-1">
        <i class="fa-solid fa-trash"></i>
        <span>{{__('messages.common.delete')}}</span>
    </a>
</x-row-actions>