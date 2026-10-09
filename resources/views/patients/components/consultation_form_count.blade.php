<div class="d-flex justify-content-center">
    @php
    $count = $row->consultation_form_count ?? 0;
    $route = isRole('clinic_admin') ? route('document-issuances.index', ['patient_id' => $row->user_id, 'module' => 'consultation']) :
    (isRole('staff') ? route('staff.document-issuances.index', ['patient_id' => $row->user_id, 'module' => 'consultation']) :
    (isRole('doctor') ? route('doctors.document-issuances.index', ['patient_id' => $row->user_id, 'module' => 'consultation']) : '#'));
    @endphp

    @if($count > 0 && canUseModule('consultations'))
    <a href="{{ $route }}" class="badge bg-success text-decoration-none"
        title="View {{ optional($row->user)->first_name }} {{ optional($row->user)->last_name }}'s consultation forms"
        style="cursor: pointer;">
        {{ $count }}
    </a>
    @elseif($count > 0)
    {{-- The count stays visible; the list behind it is for staff with the consultations module only (else 403). --}}
    <span class="badge bg-success">{{ $count }}</span>
    @else
    <div class="badge bg-secondary">0</div>
    @endif
</div>