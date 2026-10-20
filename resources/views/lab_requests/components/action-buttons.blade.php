@php
    $showRoute  = getRouteByRole('lab-requests.show', [$row]);
    $editRoute  = getRouteByRole('lab-requests.edit', [$row]);
    $pdfRoute   = getRouteByRole('lab-requests.pdf',  [$row]);
    $deleteRoute = getRouteByRole('lab-requests.destroy', [$row]);
@endphp

<x-row-actions module="lab_requests">
    {{-- View --}}
    <a href="{{ $showRoute }}" class="dropdown-item text-info" title="View Request" role="menuitem" tabindex="-1">
        <i class="fas fa-eye"></i>
        <span>View Request</span>
    </a>

    {{-- Edit (disabled for terminal states) --}}
    @if(!$row->isTerminal())
    <a href="{{ $editRoute }}" class="dropdown-item text-warning" title="Edit Request" role="menuitem" tabindex="-1">
        <i class="fas fa-edit"></i>
        <span>Edit Request</span>
    </a>
    @endif

    {{-- Print PDF --}}
    <a href="{{ $pdfRoute }}" target="_blank" class="dropdown-item text-secondary" title="Print Request Form" role="menuitem" tabindex="-1">
        <i class="fas fa-print"></i>
        <span>Print Request Form</span>
    </a>

    {{-- Delete: only a pending / cancelled request, by its creator or the clinic admin --}}
    @if($row->isDeletable() && $row->canBeDeletedBy(auth()->user()))
    @can('manage_request_documents')
    <form method="POST" action="{{ $deleteRoute }}"
          onsubmit="return confirm('Delete Lab Request #{{ $row->request_number }}? This cannot be undone.');">
        @csrf
        @method('DELETE')
        <button type="submit" class="dropdown-item text-danger" title="Delete" role="menuitem" tabindex="-1">
            <i class="fas fa-trash"></i>
            <span>Delete</span>
        </button>
    </form>
    @endcan
    @endif
</x-row-actions>
