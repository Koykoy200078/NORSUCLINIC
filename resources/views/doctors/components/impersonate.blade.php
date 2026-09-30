@if($row->user->status)
    <form method="POST" action="{{ route('impersonate', $row->user->id) }}" class="d-inline">
        @csrf
        <button type="submit" title="Impersonate {{ $row->user->full_name }}" class="btn btn-sm btn-primary me-5"
                style="width: fit-content;">
            {{ __('messages.common.impersonate') }}
        </button>
    </form>
@else
    <button type="button" title="Impersonate {{ $row->user->full_name }}" class="btn btn-sm btn-secondary me-5"
            style="pointer-events: none; cursor: default;" disabled>
        {{ __('messages.common.impersonate') }}
    </button>
@endif
