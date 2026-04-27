<div class="table-responsive">
    <table class="table table-hover table-rounded table-striped border gy-5 gs-7">
        <thead>
            <tr class="fw-bold fs-6 text-gray-800 border-bottom-2 border-gray-200">
                <th>Date</th>
                <th>Patient</th>
                <th>Age/Gender</th>
                <th>Complaints</th>
                <th>Assessment</th>
                <th>Plan</th>
                <th>Encoder</th>
                <th class="text-end">Details</th>
            </tr>
        </thead>
        <tbody>
            @forelse($reports as $report)
            <tr>
                <td>{{ $report->created_at->format('M d, Y h:i A') }}</td>
                <td>
                    <div class="fw-bold text-gray-800">{{ $report->name }}</div>
                    <div class="text-gray-600 fs-7">{{ $report->address }}</div>
                </td>
                <td>{{ $report->age }} / {{ $report->gender }}</td>
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
                    <div class="text-truncate" style="max-width: 200px;" title="{{ $report->plan }}">
                        {{ $report->plan ?? '-' }}
                    </div>
                </td>
                <td>{{ $report->creator->full_name ?? 'System' }}</td>
                <td class="text-end">
                    <a href="{{ route(isRole('clinic_admin') ? 'document-issuances.show' : (isRole('staff') ? 'staff.document-issuances.show' : 'doctors.document-issuances.show'), $report->id) }}" 
                       class="btn btn-sm btn-light-primary" target="_blank">
                        <i class="fas fa-eye"></i> View
                    </a>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="8" class="text-center text-gray-600 py-5">No visit records found</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>
