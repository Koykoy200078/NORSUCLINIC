<div class="table-responsive">
    <table class="table table-hover table-rounded table-striped border gy-5 gs-7">
        <thead>
            <tr class="fw-bold fs-6 text-gray-800 border-bottom-2 border-gray-200">
                <th>Date Dispensed</th>
                <th>Medicine</th>
                <th>Dosage / Batch</th>
                <th>Quantity</th>
                <th>Associated Record</th>
                <th>Dispensed By</th>
            </tr>
        </thead>
        <tbody>
            @forelse($reports as $entry)
            @php
                $batch = $entry->batch;
                $referenceLabel = match ($entry->reference_type) {
                    \App\Models\DocumentIssuance::class, \App\Models\RequestDocuments::class => 'Consultation',
                    \App\Models\Prescription::class => 'Prescription',
                    \App\Models\DispenseRecord::class => 'Dispense record',
                    default => $entry->reference_type ? class_basename($entry->reference_type) : 'Manual entry',
                };
            @endphp
            <tr>
                <td>{{ $entry->created_at->format('M d, Y h:i A') }}</td>
                <td>{{ $batch?->medicine?->name ?? 'Deleted Medicine' }}</td>
                <td>
                    {{ $batch?->dosage ?: 'N/A' }}
                    @if($batch?->batch_number)
                        <small class="text-muted d-block">{{ $batch->batch_number }}</small>
                    @endif
                </td>
                <td><span class="badge bg-secondary text-white">{{ $entry->quantity }}</span></td>
                <td><span class="text-gray-600">{{ $referenceLabel }}{{ $entry->reference_id ? ' #' . $entry->reference_id : '' }}</span></td>
                <td>{{ $entry->user?->full_name ?? 'N/A' }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="6" class="text-center text-gray-600 py-5">No dispensing records found</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>
<p class="text-muted small mt-3 mb-0">Shows stock issued, taken from the stock ledger (each unit dispensed for a consultation, prescription or dispense record).</p>
