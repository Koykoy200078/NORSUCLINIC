<div class="d-flex justify-content-center">
    @php
    $count = $row->consultation_form_count ?? 0;
    $route = isRole('clinic_admin') ? route('request-documents.index', ['patient_id' => $row->user_id]) :
    (isRole('staff') ? route('staff.request-documents.index', ['patient_id' => $row->user_id]) :
    (isRole('doctor') ? route('doctors.request-documents.index', ['patient_id' => $row->user_id]) : '#'));
    @endphp

    @if($count > 0)
    <a href="{{ $route }}" class="badge bg-success text-decoration-none"
        title="View {{ $row->user->first_name }} {{ $row->user->last_name }}'s consultation forms"
        style="cursor: pointer;">
        {{ $count }}
    </a>
    @else
    <div class="badge bg-secondary">0</div>
    @endif
</div>