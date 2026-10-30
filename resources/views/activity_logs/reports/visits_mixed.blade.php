<div class="table-responsive">
    <div class="text-muted fs-7 mb-2">
        {{ $reports->total() }} visit{{ $reports->total() === 1 ? '' : 's' }} found
        @if($activeFilters > 0)
            with {{ $activeFilters }} filter{{ $activeFilters === 1 ? '' : 's' }} on
        @endif
        <span class="ms-1">Consultations and the patients who only came for medicine; the illness, service, nurse and similar filters apply to consultations only.</span>
    </div>
    <table class="table table-hover table-rounded table-striped border gy-5 gs-7">
        <thead>
            <tr class="fw-bold fs-6 text-gray-800 border-bottom-2 border-gray-200">
                <th>Date</th>
                <th>Type</th>
                <th>Patient</th>
                <th>Affiliation</th>
                <th>Illness / Services / Drugs</th>
                <th>Recorded by</th>
                <th class="text-end">Details</th>
            </tr>
        </thead>
        <tbody>
            @forelse($reports as $visit)
            <tr wire:key="visit-{{ $visit->key }}">
                <td class="fw-bold text-gray-800 text-nowrap">{{ $visit->date !== '' ? \Illuminate\Support\Carbon::parse($visit->date)->format('M d, Y') : '-' }}</td>
                <td>
                    @if($visit->kind === 'meds_only')
                        <span class="badge bg-warning text-dark">{{ $visit->type }}</span>
                    @else
                        <span class="badge bg-primary">{{ $visit->type }}</span>
                    @endif
                </td>
                <td>
                    <div class="fw-bold text-gray-800">{{ $visit->name }}</div>
                    @if($visit->kind === 'consultation')
                        <div class="text-gray-600 fs-7">{{ $visit->record->address }}</div>
                    @endif
                </td>
                <td>
                    <div class="fw-semibold">{{ $visit->patient_type ?: '-' }}</div>
                    <div class="text-gray-600 fs-7">
                        {{ collect([$visit->college, $visit->course, $visit->year_level, $visit->campus])->filter()->implode(' | ') ?: '-' }}
                    </div>
                </td>
                <td>
                    @if($visit->summary !== '')
                        <span class="fs-7">{{ $visit->summary }}</span>
                    @else
                        <span class="text-gray-500 fs-7">{{ $visit->kind === 'consultation' ? 'Not classified' : '-' }}</span>
                    @endif
                </td>
                <td>{{ $visit->recorded_by ?: '-' }}</td>
                <td class="text-end text-nowrap">
                    @if($visit->kind === 'consultation' && canUseModule('consultations'))
                    <a href="{{ route(getRouteNameByRole('document-issuances.edit'), $visit->id) }}"
                       class="btn btn-sm {{ $visit->record->illnesses->isEmpty() ? 'btn-outline-warning' : 'btn-light' }}" target="_blank"
                       title="Open the consultation to pick the illness and services">
                        <i class="fas fa-pen"></i> {{ $visit->record->illnesses->isEmpty() ? 'Classify' : 'Edit' }}
                    </a>
                    <a href="{{ route(getRouteNameByRole('document-issuances.show'), $visit->id) }}"
                       class="btn btn-sm btn-outline-primary" target="_blank">
                        <i class="fas fa-eye"></i> View
                    </a>
                    @elseif($visit->kind === 'meds_only' && canUseModule('meds_only'))
                    <a href="{{ getRouteByRole('meds-only.show', ['record' => $visit->id]) }}"
                       class="btn btn-sm btn-outline-primary" target="_blank">
                        <i class="fas fa-eye"></i> View
                    </a>
                    @endif
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="7" class="text-center text-gray-600 py-5">No visit records found</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>
