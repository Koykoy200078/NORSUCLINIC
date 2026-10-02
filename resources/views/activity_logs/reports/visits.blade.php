<div class="table-responsive">
    <div class="text-muted fs-7 mb-2">
        {{ $reports->total() }} visit{{ $reports->total() === 1 ? '' : 's' }} found
        @if($activeFilters > 0)
            with {{ $activeFilters }} filter{{ $activeFilters === 1 ? '' : 's' }} on
        @endif
    </div>
    <table class="table table-hover table-rounded table-striped border gy-5 gs-7">
        <thead>
            <tr class="fw-bold fs-6 text-gray-800 border-bottom-2 border-gray-200">
                <th>Date</th>
                <th>Patient</th>
                <th>Age/Gender</th>
                <th>Affiliation</th>
                <th>Illness / Services</th>
                <th>Complaints</th>
                <th>Assessment</th>
                <th>Nurse / Encoder</th>
                <th class="text-end">Details</th>
            </tr>
        </thead>
        <tbody>
            @forelse($reports as $report)
            <tr>
                <td>
                    <div class="fw-bold text-gray-800">{{ ($report->requested_at ?? $report->created_at)->format('M d, Y') }}</div>
                    <div class="text-gray-600 fs-7">{{ $report->consult_mode === 'physical' ? 'Walk-in' : ($report->consult_mode === 'virtual' ? 'Virtual' : '') }}</div>
                </td>
                <td>
                    <div class="fw-bold text-gray-800">{{ $report->name }}</div>
                    <div class="text-gray-600 fs-7">{{ $report->address }}</div>
                </td>
                <td>{{ $report->age }} / {{ $report->gender }}</td>
                <td>
                    <div class="fw-semibold">{{ $report->informant ?: '-' }}</div>
                    <div class="text-gray-600 fs-7">
                        {{ collect([$report->college, $report->course, $report->year_level, $report->campus])->filter()->implode(' | ') ?: '-' }}
                    </div>
                </td>
                <td>
                    @forelse($report->illnessLabels() as $illnessLabel)
                        <span class="badge badge-light-primary me-1 mb-1">{{ $illnessLabel }}</span>
                    @empty
                        <span class="text-gray-500 fs-7">Not classified</span>
                    @endforelse
                    @foreach($report->serviceLabels() as $serviceLabel)
                        <span class="badge badge-light-success me-1 mb-1">{{ $serviceLabel }}</span>
                    @endforeach
                </td>
                <td>
                    <div class="text-truncate" style="max-width: 200px;" title="{{ $report->complaints }}">
                        {{ $report->complaints ?? '-' }}
                    </div>
                </td>
                <td>
                    <div class="text-truncate" style="max-width: 200px;" title="{{ $report->assessment }}">
                        {{ $report->assessment ?? '-' }}
                    </div>
                </td>
                <td>
                    <div>{{ $report->nursingInCharge?->full_name ?? '-' }}</div>
                    <div class="text-gray-600 fs-7">{{ $report->creator->full_name ?? 'System' }}</div>
                </td>
                <td class="text-end text-nowrap">
                    @if(canStaffAccessModule('consultations'))
                    <a href="{{ route(isRole('clinic_admin') ? 'document-issuances.edit' : (isRole('staff') ? 'staff.document-issuances.edit' : 'doctors.document-issuances.edit'), $report->id) }}"
                       class="btn btn-sm {{ $report->illnesses->isEmpty() ? 'btn-light-warning' : 'btn-light' }}" target="_blank"
                       title="Open the consultation to pick the illness and services">
                        <i class="fas fa-pen"></i> {{ $report->illnesses->isEmpty() ? 'Classify' : 'Edit' }}
                    </a>
                    @endif
                    <a href="{{ route(isRole('clinic_admin') ? 'document-issuances.show' : (isRole('staff') ? 'staff.document-issuances.show' : 'doctors.document-issuances.show'), $report->id) }}"
                       class="btn btn-sm btn-light-primary" target="_blank">
                        <i class="fas fa-eye"></i> View
                    </a>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="9" class="text-center text-gray-600 py-5">No visit records found</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>
