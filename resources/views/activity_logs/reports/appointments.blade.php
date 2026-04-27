<div class="table-responsive">
    <table class="table table-hover table-rounded table-striped border gy-5 gs-7">
        <thead>
            <tr class="fw-bold fs-6 text-gray-800 border-bottom-2 border-gray-200">
                <th>Scheduled Date</th>
                <th>Patient</th>
                <th>Added By</th>
                <th>Status</th>
                <th>Notes</th>
            </tr>
        </thead>
        <tbody>
            @forelse($reports as $appointment)
            <tr>
                <td>
                    <div class="fw-bold text-primary">{{ $appointment->scheduled_at->format('M d, Y') }}</div>
                    <div class="text-gray-600 fs-7">{{ $appointment->scheduled_at->format('h:i A') }}</div>
                </td>
                <td>
                    <div class="fw-bold text-gray-800">{{ $appointment->patient->user->full_name ?? 'Unknown' }}</div>
                    <div class="text-gray-600 fs-7">ID: {{ $appointment->patient->patient_unique_id ?? '-' }}</div>
                </td>
                <td>{{ $appointment->addedBy->full_name ?? 'System' }}</td>
                <td>
                    <span class="badge bg-{{ 
                        $appointment->status === 'completed' ? 'success' : 
                        ($appointment->status === 'cancelled' ? 'danger' : 'warning') 
                    }} {{ $appointment->status === 'completed' || $appointment->status === 'cancelled' ? 'text-white' : 'text-dark' }}">
                        {{ ucfirst($appointment->status) }}
                    </span>
                </td>
                <td>
                    <div class="text-truncate" style="max-width: 250px;" title="{{ $appointment->notes }}">
                        {{ $appointment->notes ?? '-' }}
                    </div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="5" class="text-center text-gray-600 py-5">No scheduled appointments found</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>
