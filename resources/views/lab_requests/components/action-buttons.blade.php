@php
    $showRoute  = getRouteByRole('lab-requests.show', [$row]);
    $editRoute  = getRouteByRole('lab-requests.edit', [$row]);
    $pdfRoute   = getRouteByRole('lab-requests.pdf',  [$row]);
    $deleteRoute = getRouteByRole('lab-requests.destroy', [$row]);
@endphp

<div class="d-flex gap-1 flex-wrap">
    {{-- View --}}
    <a href="{{ $showRoute }}" class="btn btn-sm btn-info text-white" title="View Request">
        <i class="fas fa-eye"></i>
    </a>

    {{-- Edit (disabled for terminal states) --}}
    @if(!$row->isTerminal())
    <a href="{{ $editRoute }}" class="btn btn-sm btn-warning text-dark" title="Edit Request">
        <i class="fas fa-edit"></i>
    </a>
    @endif

    {{-- Print PDF --}}
    <a href="{{ $pdfRoute }}" target="_blank" class="btn btn-sm btn-secondary" title="Print Request Form">
        <i class="fas fa-print"></i>
    </a>

    {{-- Delete: only a pending / cancelled request, by its creator or the clinic admin --}}
    @if($row->isDeletable() && $row->canBeDeletedBy(auth()->user()))
    @can('manage_request_documents')
    <form method="POST" action="{{ $deleteRoute }}"
          onsubmit="return confirm('Delete Lab Request #{{ $row->request_number }}? This cannot be undone.');">
        @csrf
        @method('DELETE')
        <button type="submit" class="btn btn-sm btn-danger" title="Delete">
            <i class="fas fa-trash"></i>
        </button>
    </form>
    @endcan
    @endif
</div>
