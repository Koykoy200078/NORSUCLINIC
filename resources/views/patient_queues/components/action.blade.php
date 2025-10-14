@php
use App\Models\PatientQueue;
@endphp

<div class="d-flex justify-content-center gap-1">
    {{-- Start Consultation Button (Only for Waiting status) --}}
    @if($row->status == PatientQueue::WAITING && (isRole('doctor') || isRole('clinic_admin')))
    <button class="btn px-1 text-success fs-3"
        onclick="startConsultation({{ $row->id }})"
        data-bs-toggle="tooltip"
        data-bs-original-title="Start Consultation">
        <i class="fas fa-play"></i>
    </button>
    @endif

    {{-- Complete Button (Only for In Progress status) --}}
    @if($row->status == PatientQueue::IN_PROGRESS && (isRole('doctor') || isRole('clinic_admin')))
    <button class="btn px-1 text-primary fs-3"
        onclick="completeConsultation({{ $row->id }})"
        data-bs-toggle="tooltip"
        data-bs-original-title="Complete Consultation">
        <i class="fas fa-check-circle"></i>
    </button>
    @endif

    {{-- View Button --}}
    <a href="{{ 
        isRole('clinic_admin') ? route('patient-queues.show', $row->id) : 
        (isRole('staff') ? route('staff.patient-queues.show', $row->id) : 
        (isRole('doctor') ? route('doctors.patient-queues.show', $row->id) : route('patient-queues.show', $row->id)))
    }}"
        class="btn px-1 text-info fs-3" data-bs-toggle="tooltip"
        data-bs-original-title="{{ __('messages.common.view') }}">
        <i class="fas fa-eye"></i>
    </a>

    {{-- Priority Toggle (Only for Waiting) --}}
    @if($row->status == PatientQueue::WAITING && (isRole('staff') || isRole('clinic_admin')))
    <button class="btn px-1 {{ $row->priority ? 'text-warning' : 'text-secondary' }} fs-3"
        onclick="togglePriority({{ $row->id }})"
        data-bs-toggle="tooltip"
        data-bs-original-title="{{ $row->priority ? 'Remove Priority' : 'Mark as Priority' }}">
        <i class="fas fa-exclamation-triangle"></i>
    </button>
    @endif

    {{-- Cancel/Delete Button --}}
    @if($row->status != PatientQueue::CANCELLED && $row->status != PatientQueue::COMPLETED)
    <a href="javascript:void(0)" data-id="{{ $row->id }}" data-bs-toggle="tooltip"
        data-bs-original-title="{{ __('messages.common.delete') }}"
        class="btn px-1 text-danger fs-3 patient-queue-delete-btn">
        <i class="fa-solid fa-trash"></i>
    </a>
    @endif
</div>

@push('scripts')
<script>
    function startConsultation(id) {
        if (confirm('Start consultation with this patient?')) {
            $.ajax({
                url: '{{ url(getLogInPrefix()."patient-queues") }}/' + id + '/start',
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    Livewire.dispatch('refresh');
                    displaySuccessMessage(response.message || 'Consultation started');
                }
            });
        }
    }

    function completeConsultation(id) {
        if (confirm('Mark consultation as completed?')) {
            $.ajax({
                url: '{{ url(getLogInPrefix()."patient-queues") }}/' + id + '/complete',
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    Livewire.dispatch('refresh');
                    displaySuccessMessage(response.message || 'Consultation completed');
                }
            });
        }
    }

    function togglePriority(id) {
        $.ajax({
            url: '{{ url(getLogInPrefix()."patient-queues") }}/' + id + '/toggle-priority',
            type: 'POST',
            data: {
                _token: '{{ csrf_token() }}'
            },
            success: function(response) {
                Livewire.dispatch('refresh');
                displaySuccessMessage(response.message || 'Priority updated');
            }
        });
    }
</script>
@endpush