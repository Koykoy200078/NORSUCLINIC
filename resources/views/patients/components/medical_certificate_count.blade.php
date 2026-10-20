<div class="d-flex justify-content-center">
    @php
    $count = $row->medical_certificate_count ?? 0;
    $route = getRouteByRole('document-issuances.index', ['patient_id' => $row->user_id, 'module' => 'certificate']);
    @endphp

    @if($count > 0)
    <a href="{{ $route }}" class="badge bg-primary text-decoration-none"
        title="View {{ optional($row->user)->first_name }} {{ optional($row->user)->last_name }}'s medical certificates"
        style="cursor: pointer;">
        {{ $count }}
    </a>
    @else
    <div class="badge bg-secondary">0</div>
    @endif
</div>