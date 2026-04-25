@extends('layouts.app')

@section('title')
Lab Request #{{ $lab_request->request_number }} — {{ $lab_request->patient_name }}
@endsection

@section('header_toolbar')
<div class="container-fluid">
    <div class="d-flex flex-wrap align-items-center justify-content-between mb-7">
        <div>
            <h1 class="mb-0">
                <i class="fa-solid fa-flask me-2" style="color:#2563a8;"></i>
                Lab Request <span class="text-muted fs-5">#{{ $lab_request->request_number }}</span>
            </h1>
            <p class="text-muted mb-0 mt-1" style="font-size:.88rem;">
                {{ $lab_request->patient_name }} &middot; {{ $lab_request->requested_at?->format('F d, Y') }}
            </p>
        </div>
        <div class="d-flex gap-2 flex-wrap mt-4 mt-md-0">
            @if(!$lab_request->isTerminal())
            <a href="{{ getRouteByRole('lab-requests.edit', [$lab_request]) }}"
               class="btn btn-warning text-dark">
                <i class="fas fa-edit me-1"></i> Edit
            </a>
            @endif
            <a href="{{ getRouteByRole('lab-requests.pdf', [$lab_request]) }}"
               target="_blank" class="btn btn-secondary">
                <i class="fas fa-print me-1"></i> Print PDF
            </a>
            <a href="{{ getRouteByRole('lab-requests.index') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-1"></i> Back
            </a>
        </div>
    </div>
</div>
@endsection

@section('content')
<div class="container-fluid">
    @include('flash::message')

    <div class="row g-4">
        {{-- ================================================================== --}}
        {{-- Left: Patient Info & Request Details --}}
        {{-- ================================================================== --}}
        <div class="col-lg-8">
            {{-- Patient Card --}}
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-primary text-white fw-bold">
                    <i class="fas fa-user-injured me-2"></i> Patient Information
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label text-muted small">Full Name</label>
                            <div class="fw-semibold">{{ $lab_request->patient_name }}</div>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label text-muted small">Age</label>
                            <div>{{ $lab_request->patient_age ?? '—' }}</div>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label text-muted small">Gender</label>
                            <div>{{ $lab_request->patient_gender ?? '—' }}</div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-muted small">Address</label>
                            <div>{{ $lab_request->address ?? '—' }}</div>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label text-muted small">Contact No.</label>
                            <div>{{ $lab_request->patient_contact ?? '—' }}</div>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label text-muted small">Affiliation</label>
                            <div>{{ ucfirst($lab_request->status_affiliation ?? '—') }}</div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label text-muted small">College</label>
                            <div>{{ $lab_request->college ?? '—' }}</div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label text-muted small">Course / Program</label>
                            <div>{{ $lab_request->course ?? '—' }}</div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label text-muted small">Year Level</label>
                            <div>{{ $lab_request->year_level ?? '—' }}</div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Request Details Card --}}
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-primary text-white fw-bold">
                    <i class="fas fa-clipboard-list me-2"></i> Request Details
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label text-muted small">Date of Request</label>
                            <div class="fw-semibold">{{ $lab_request->requested_at?->format('M d, Y') ?? '—' }}</div>
                        </div>
                        <div class="col-md-5">
                            <label class="form-label text-muted small">Clinical Indication</label>
                            <div>{{ $lab_request->clinical_indication ?? '—' }}</div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label text-muted small">Requesting Physician</label>
                            <div>{{ $lab_request->requesting_physician ?? '—' }}</div>
                        </div>
                        @if($lab_request->physician_license_no)
                        <div class="col-md-4">
                            <label class="form-label text-muted small">Physician License No.</label>
                            <div>{{ $lab_request->physician_license_no }}</div>
                        </div>
                        @endif
                        @if($lab_request->remarks)
                        <div class="col-12">
                            <label class="form-label text-muted small">Remarks</label>
                            <div>{{ $lab_request->remarks }}</div>
                        </div>
                        @endif
                        <div class="col-12">
                            <label class="form-label text-muted small">Created by</label>
                            <div>{{ $lab_request->creator?->full_name ?? '—' }}
                                <small class="text-muted">· {{ $lab_request->created_at?->format('M d, Y h:i A') }}</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Tests & Results --}}
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-primary text-white fw-bold">
                    <i class="fas fa-vials me-2"></i>
                    Tests Requested
                    <span class="badge bg-white text-primary ms-2">{{ $lab_request->items->count() }}</span>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>#</th>
                                    <th>Test Name</th>
                                    <th>Category</th>
                                    <th>Result</th>
                                    <th>Unit</th>
                                    <th>Normal Range</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($lab_request->items as $i => $item)
                                <tr>
                                    <td class="text-center text-muted">{{ $i + 1 }}</td>
                                    <td class="fw-semibold">{{ $item->test_name }}</td>
                                    <td><span class="badge bg-secondary">{{ $item->test_category }}</span></td>
                                    <td>{{ $item->result_value ?? '—' }}</td>
                                    <td class="text-muted">{{ $item->unit ?? '—' }}</td>
                                    <td class="text-muted" style="font-size:.85rem;">{{ $item->normal_range ?? '—' }}</td>
                                    <td>
                                        <span class="badge bg-{{ $item->getResultStatusBadgeClass() }}">
                                            {{ ucfirst($item->result_status) }}
                                        </span>
                                    </td>
                                </tr>
                                @if($item->notes)
                                <tr>
                                    <td colspan="7" class="text-muted ps-4" style="font-size:.85rem;">
                                        <i class="fas fa-comment me-1"></i> {{ $item->notes }}
                                    </td>
                                </tr>
                                @endif
                                @empty
                                <tr>
                                    <td colspan="7" class="text-center text-muted py-3">No tests recorded.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        {{-- ================================================================== --}}
        {{-- Right: Status Timeline --}}
        {{-- ================================================================== --}}
        <div class="col-lg-4">
            <div class="card shadow-sm sticky-top" style="top:1rem;">
                <div class="card-header bg-primary text-white fw-bold">
                    <i class="fas fa-tasks me-2"></i> Request Status
                </div>
                <div class="card-body">
                    {{-- Current status badge --}}
                    <div class="text-center mb-4">
                        <span class="badge bg-{{ $lab_request->getStatusBadgeClass() }} fs-5 px-4 py-2">
                            <i class="fas {{ $lab_request->getStatusIcon() }} me-1"></i>
                            {{ ucfirst($lab_request->status) }}
                        </span>
                    </div>

                    {{-- Timeline --}}
                    <ul class="list-unstyled mb-0">
                        @php
                            $timelineSteps = [
                                ['label' => 'Pending',    'ts' => $lab_request->created_at,       'icon' => 'fa-clock',         'status' => 'pending'],
                                ['label' => 'Collected',  'ts' => $lab_request->collected_at,     'icon' => 'fa-vial',          'status' => 'collected'],
                                ['label' => 'Processing', 'ts' => $lab_request->processed_at,     'icon' => 'fa-microscope',    'status' => 'processing'],
                                ['label' => 'Completed',  'ts' => $lab_request->completed_at,     'icon' => 'fa-check-circle',  'status' => 'completed'],
                                ['label' => 'Cancelled',  'ts' => $lab_request->cancelled_at,     'icon' => 'fa-times-circle',  'status' => 'cancelled'],
                                ['label' => 'Referred',   'ts' => $lab_request->referred_at,      'icon' => 'fa-external-link', 'status' => 'referred'],
                                ['label' => 'Rejected',   'ts' => $lab_request->rejected_at,      'icon' => 'fa-ban',           'status' => 'rejected'],
                            ];
                            $relevantSteps = array_filter($timelineSteps, fn($s) => $s['ts'] !== null || $s['status'] === 'pending');
                        @endphp

                        @foreach($relevantSteps as $step)
                        @if($step['ts'] || $step['status'] === 'pending')
                        <li class="d-flex align-items-start gap-3 mb-3">
                            <div class="mt-1">
                                <span class="badge rounded-circle p-2 bg-{{ $step['ts'] ? 'success' : 'light text-muted' }}">
                                    <i class="fas {{ $step['icon'] }}"></i>
                                </span>
                            </div>
                            <div>
                                <div class="fw-semibold">{{ $step['label'] }}</div>
                                <div class="text-muted" style="font-size:.82rem;">
                                    {{ $step['ts'] ? $step['ts']->format('M d, Y h:i A') : 'Not yet reached' }}
                                </div>
                            </div>
                        </li>
                        @endif
                        @endforeach

                        @if($lab_request->status_note)
                        <li class="border-top pt-2 mt-2">
                            <div class="text-muted small"><i class="fas fa-comment me-1"></i>{{ $lab_request->status_note }}</div>
                        </li>
                        @endif
                    </ul>

                    {{-- Quick status update (if not terminal) --}}
                    @php
                        $allowedStatuses = \App\Models\LabRequest::allowedTransitions()[$lab_request->status] ?? [];
                    @endphp
                    @if(!$lab_request->isTerminal() && count($allowedStatuses) > 0)
                    <hr>
                    <form action="{{ getRouteByRole('lab-requests.update-status', [$lab_request]) }}"
                          method="POST">
                        @csrf
                        <label class="form-label fw-semibold small">Update Status</label>
                        <select name="status" class="form-select form-select-sm mb-2" required>
                            <option value="">-- Select --</option>
                            @foreach($allowedStatuses as $s)
                            <option value="{{ $s }}">{{ ucfirst($s) }}</option>
                            @endforeach
                        </select>
                        <textarea name="status_note" class="form-control form-control-sm mb-2" rows="2"
                                  placeholder="Optional note..."></textarea>
                        <button type="submit" class="btn btn-sm btn-primary w-100">
                            <i class="fas fa-save me-1"></i> Update
                        </button>
                    </form>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
