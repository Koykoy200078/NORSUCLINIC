<x-row-actions module="cms">
    <a href="{{ route('banner.edit', $row->id) }}" title="{{ __('messages.common.edit') }}"  data-bs-toggle="tooltip"
       data-bs-original-title="{{ __('messages.common.edit') }}"
       class="dropdown-item px-1 text-primary fs-3" data-id="{{$row->id}}" role="menuitem" tabindex="-1">
        <i class="fa-solid fa-pen-to-square"></i>
        <span>{{ __('messages.common.edit') }}</span>
    </a>
</x-row-actions>
