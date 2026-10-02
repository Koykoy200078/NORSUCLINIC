@extends('layouts.app')
@section('title')
{{ __('messages.settings') }}
@endsection
@section('content')
<div class="container-fluid">
    <div class="d-flex flex-column">
        @include('setting.setting_menu')

        <div class="card mb-6">
            <div class="card-header">
                <h3 class="m-0">Deleted consultations, certificates and excuse slips</h3>
            </div>
            <div class="card-body">
                <p class="text-muted">
                    A record deleted from a screen is only hidden: it is still here, with its photos. Restoring it puts it back on the
                    lists and reports. A consultation that gave out medicines takes them out of stock again; if there is not enough stock
                    the restore is refused and says why.
                </p>

                <div class="table-responsive">
                    <table class="table table-row-bordered align-middle gy-4">
                        <thead>
                            <tr class="fw-bold text-gray-700">
                                <th>Record</th>
                                <th>Patient</th>
                                <th>Date of record</th>
                                <th>Deleted</th>
                                <th>Deleted by</th>
                                <th class="text-end">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                        @forelse($documents as $document)
                            @php
                                $label = match ($document->document_type) {
                                    'consultation_form' => 'Consultation',
                                    'excuse_slip' => 'Excuse slip',
                                    'medical_certificate' => 'Medical certificate',
                                    default => 'Document',
                                };
                                $deletion = $documentDeletions[$document->id] ?? null;
                            @endphp
                            <tr>
                                <td>{{ $label }} #{{ $document->id }}</td>
                                <td>{{ $document->name }}</td>
                                <td>{{ optional($document->requested_at)->format('M d, Y') ?? '-' }}</td>
                                <td>{{ $document->deleted_at->format('M d, Y h:i A') }}</td>
                                <td>{{ $deletion?->user_name ?? 'Unknown' }}</td>
                                <td class="text-end">
                                    <form method="POST" action="{{ route('deleted-records.restore', ['type' => 'document', 'id' => $document->id]) }}"
                                          onsubmit="return confirm('Restore this {{ strtolower($label) }}?');">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-light-primary"><i class="fas fa-rotate-left"></i> Restore</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="text-center text-muted py-6">Nothing has been deleted.</td></tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="mt-4">{{ $documents->links() }}</div>
            </div>
        </div>

        <div class="card mb-6">
            <div class="card-header">
                <h3 class="m-0">Deleted lab requests</h3>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-row-bordered align-middle gy-4">
                        <thead>
                            <tr class="fw-bold text-gray-700">
                                <th>Request</th>
                                <th>Patient</th>
                                <th>Status when deleted</th>
                                <th>Deleted</th>
                                <th>Deleted by</th>
                                <th class="text-end">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                        @forelse($labRequests as $labRequest)
                            @php $deletion = $labDeletions[$labRequest->id] ?? null; @endphp
                            <tr>
                                <td>#{{ $labRequest->request_number }}</td>
                                <td>{{ $labRequest->patient_name }}</td>
                                <td>{{ ucfirst($labRequest->status) }}</td>
                                <td>{{ $labRequest->deleted_at->format('M d, Y h:i A') }}</td>
                                <td>{{ $deletion?->user_name ?? 'Unknown' }}</td>
                                <td class="text-end">
                                    <form method="POST" action="{{ route('deleted-records.restore', ['type' => 'lab-request', 'id' => $labRequest->id]) }}"
                                          onsubmit="return confirm('Restore this lab request?');">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-light-primary"><i class="fas fa-rotate-left"></i> Restore</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="text-center text-muted py-6">Nothing has been deleted.</td></tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="mt-4">{{ $labRequests->links() }}</div>
            </div>
        </div>
    </div>
</div>
@endsection
