    <x-row-actions module="roles">
        <a href="{{ route('roles.edit', $row->id) }}" title="{{ __('messages.common.edit') }}"
           class="dropdown-item px-1 text-primary fs-3 user- edit-btn" data-bs-toggle="tooltip"
           data-bs-original-title="{{ __('messages.common.edit') }}" data-id="{{$row->id}}" role="menuitem" tabindex="-1">
            <i class="fa-solid {{ $row->isReadOnly() ? 'fa-lock' : 'fa-pen-to-square' }}"></i>
            <span>{{ __('messages.common.edit') }}</span>
        </a>
        @if(! $row->is_default && ! $row->isReadOnly())
        <a href="javascript:void(0)" data-id="{{ $row->id }}" title="{{ __('messages.common.delete') }}"
           class="dropdown-item px-1 text-danger fs-3 role-delete-btn" data-bs-toggle="tooltip"
           data-bs-original-title="{{ __('messages.common.delete') }}" role="menuitem" tabindex="-1">
            <i class="fa-solid fa-trash"></i>
            <span>{{ __('messages.common.delete') }}</span>
        </a>
        @endif
    </x-row-actions>
