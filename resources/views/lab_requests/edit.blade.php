@extends('layouts.app')

@section('title')
Edit Lab Request #{{ $lab_request->request_number }}
@endsection

@section('header_toolbar')
<div class="container-fluid">
    <div class="d-flex flex-wrap align-items-center justify-content-between mb-7">
        <div>
            <h1 class="mb-0">
                <i class="fa-solid fa-flask me-2" style="color:#2563a8;"></i>
                Edit Lab Request <span class="text-muted fs-5">#{{ $lab_request->request_number }}</span>
            </h1>
            <p class="text-muted mb-0 mt-1" style="font-size:.88rem;">
                Modify request details, tests, and record results below.
            </p>
        </div>
        <div class="d-flex gap-2 mt-4 mt-md-0">
            <a href="{{ getRouteByRole('lab-requests.show', [$lab_request]) }}"
                class="btn btn-outline-info">
                <i class="fas fa-eye me-1"></i> View
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

    <form action="{{ getRouteByRole('lab-requests.update', [$lab_request]) }}"
        method="POST" id="lab-edit-form">
        @csrf
        @method('PUT')

        {{-- Status badge + quick transition --}}
        <div class="alert alert-light border d-flex align-items-center gap-3 mb-4">
            <span class="badge bg-{{ $lab_request->getStatusBadgeClass() }} fs-6 px-3 py-2">
                <i class="fas {{ $lab_request->getStatusIcon() }} me-1"></i>
                {{ ucfirst($lab_request->status) }}
            </span>
            @if(!$lab_request->isTerminal() && count($allowedStatuses) > 0)
            <button type="button" class="btn btn-sm btn-outline-primary ms-auto" data-bs-toggle="modal" data-bs-target="#statusModal">
                <i class="fas fa-exchange-alt me-1"></i> Update Status
            </button>
            @endif
        </div>

        {{-- ================================================================== --}}
        {{-- Patient Info (read-only snapshot) --}}
        {{-- ================================================================== --}}
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-primary text-white fw-bold">
                <i class="fas fa-user-injured me-2"></i> Patient Information
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Full Name <span class="text-danger">*</span></label>
                        <input type="text" name="patient_name" class="form-control @error('patient_name') is-invalid @enderror"
                            value="{{ old('patient_name', $lab_request->patient_name) }}" required>
                        @error('patient_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-2">
                        <label class="form-label fw-semibold">Age</label>
                        <input type="number" name="patient_age" class="form-control"
                            value="{{ old('patient_age', $lab_request->patient_age) }}">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label fw-semibold">Gender</label>
                        <select name="patient_gender" class="form-select">
                            <option value="">Select</option>
                            <option value="Male" {{ old('patient_gender', $lab_request->patient_gender) === 'Male' ? 'selected' : '' }}>Male</option>
                            <option value="Female" {{ old('patient_gender', $lab_request->patient_gender) === 'Female' ? 'selected' : '' }}>Female</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Contact No.</label>
                        <input type="text" name="patient_contact" class="form-control"
                            value="{{ old('patient_contact', $lab_request->patient_contact) }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Address</label>
                        <input type="text" name="address" class="form-control"
                            value="{{ old('address', $lab_request->address) }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">Affiliation / Status</label>
                        <select name="status_affiliation" id="status_affiliation" class="form-select">
                            <option value="">Select</option>
                            <option value="student" {{ old('status_affiliation', $lab_request->status_affiliation) === 'student' ? 'selected' : '' }}>Student</option>
                            <option value="staff" {{ old('status_affiliation', $lab_request->status_affiliation) === 'staff' ? 'selected' : '' }}>Staff</option>
                            <option value="faculty" {{ old('status_affiliation', $lab_request->status_affiliation) === 'faculty' ? 'selected' : '' }}>Faculty</option>
                            <option value="guest" {{ old('status_affiliation', $lab_request->status_affiliation) === 'guest' ? 'selected' : '' }}>Guest</option>
                        </select>
                    </div>

                    {{-- Student-specific --}}
                    <div id="student_fields" class="row g-2 col-md-12 {{ old('status_affiliation', $lab_request->status_affiliation) === 'student' ? '' : 'd-none' }}">
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">Campus</label>
                            <input type="text" name="campus" class="form-control" value="{{ old('campus', $lab_request->campus) }}">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">College</label>
                            <input type="text" name="college" class="form-control" value="{{ old('college', $lab_request->college) }}">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">Course</label>
                            <input type="text" name="course" class="form-control" value="{{ old('course', $lab_request->course) }}">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">Year Level</label>
                            <input type="text" name="year_level" class="form-control" value="{{ old('year_level', $lab_request->year_level) }}">
                        </div>
                    </div>

                    {{-- Staff-specific --}}
                    <div id="staff_fields" class="row g-2 col-md-12 {{ old('status_affiliation', $lab_request->status_affiliation) === 'staff' ? '' : 'd-none' }}">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Office</label>
                            <input type="text" name="office" class="form-control" value="{{ old('office', $lab_request->office) }}">
                        </div>
                    </div>

                    {{-- Faculty-specific --}}
                    <div id="faculty_fields" class="row g-2 col-md-12 {{ old('status_affiliation', $lab_request->status_affiliation) === 'faculty' ? '' : 'd-none' }}">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">College</label>
                            <input type="text" name="college_faculty" class="form-control" value="{{ old('college', $lab_request->college) }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Department</label>
                            <input type="text" name="department" class="form-control" value="{{ old('department', $lab_request->department) }}">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ================================================================== --}}
        {{-- Request Details --}}
        {{-- ================================================================== --}}
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-primary text-white fw-bold">
                <i class="fas fa-clipboard-list me-2"></i> Request Details
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">Date of Request <span class="text-danger">*</span></label>
                        <input type="date" name="requested_at" class="form-control @error('requested_at') is-invalid @enderror"
                            value="{{ old('requested_at', $lab_request->requested_at?->format('Y-m-d')) }}" required>
                        @error('requested_at')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-5">
                        <label class="form-label fw-semibold">Clinical Indication / Purpose</label>
                        <input type="text" name="clinical_indication" class="form-control"
                            value="{{ old('clinical_indication', $lab_request->clinical_indication) }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Requesting Physician</label>
                        <input type="text" name="requesting_physician" class="form-control"
                            value="{{ old('requesting_physician', $lab_request->requesting_physician) }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Physician License No.</label>
                        <input type="text" name="physician_license_no" class="form-control"
                            value="{{ old('physician_license_no', $lab_request->physician_license_no) }}">
                    </div>
                    <div class="col-md-8">
                        <label class="form-label fw-semibold">Remarks</label>
                        <textarea name="remarks" class="form-control" rows="2">{{ old('remarks', $lab_request->remarks) }}</textarea>
                    </div>
                </div>
            </div>
        </div>

        {{-- ================================================================== --}}
        {{-- Tests + Results --}}
        {{-- ================================================================== --}}
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-primary text-white d-flex justify-content-between">
                <span class="fw-bold"><i class="fas fa-vials me-2"></i> Tests & Results</span>
                <span class="badge bg-white text-primary" id="selected-count-badge">0 selected</span>
            </div>
            <div class="card-body">
                @error('test_ids')
                <div class="alert alert-danger py-2">{{ $message }}</div>
                @enderror
                @error('custom_tests')
                <div class="alert alert-danger py-2">{{ $message }}</div>
                @enderror

                @foreach($labTestsGrouped as $category => $tests)
                <div class="mb-4">
                    <div class="d-flex align-items-center mb-2 gap-2">
                        <span class="badge bg-secondary">{{ $category }}</span>
                        <button type="button" class="btn btn-link btn-sm p-0 select-all-category" data-category="{{ $category }}">Select All</button>
                        <button type="button" class="btn btn-link btn-sm p-0 deselect-all-category text-danger" data-category="{{ $category }}">Clear</button>
                    </div>
                    <div class="row g-2">
                        @foreach($tests as $test)
                        @php
                        $isSelected = in_array($test->id, $selectedTestIds);
                        $existingItem = $lab_request->items->firstWhere('lab_test_id', $test->id);
                        @endphp
                        <div class="col-md-4 col-lg-3">
                            <div class="form-check lab-test-item" data-category="{{ $test->category }}">
                                <input class="form-check-input test-checkbox" type="checkbox"
                                    name="test_ids[]"
                                    value="{{ $test->id }}"
                                    id="test_{{ $test->id }}"
                                    {{ $isSelected ? 'checked' : '' }}
                                    data-item-id="{{ $existingItem?->id }}">
                                <label class="form-check-label user-select-none" for="test_{{ $test->id }}">
                                    {{ $test->name }}
                                    @if($isSelected && $existingItem)
                                    @if($existingItem->result_status === 'critical')
                                    <span class="badge bg-danger ms-1">Critical</span>
                                    @elseif($existingItem->result_status === 'abnormal')
                                    <span class="badge bg-warning text-dark ms-1">Abnormal</span>
                                    @elseif($existingItem->result_status === 'normal')
                                    <span class="badge bg-success ms-1">Normal</span>
                                    @endif
                                    @endif
                                </label>
                            </div>

                            {{-- Result entry for already-selected items --}}
                            @if($isSelected && $existingItem)
                            <div class="result-entry ms-4 mt-1" id="result-{{ $existingItem->id }}">
                                <input type="text" name="results[{{ $existingItem->id }}][result_value]"
                                    class="form-control form-control-sm mb-1"
                                    placeholder="Result value"
                                    value="{{ $existingItem->result_value }}">
                                <select name="results[{{ $existingItem->id }}][result_status]"
                                    class="form-select form-select-sm mb-1">
                                    @foreach(\App\Models\LabRequestItem::resultStatuses() as $key => $label)
                                    <option value="{{ $key }}" {{ $existingItem->result_status === $key ? 'selected' : '' }}>{{ $label }}</option>
                                    @endforeach
                                </select>
                                <input type="text" name="results[{{ $existingItem->id }}][notes]"
                                    class="form-control form-control-sm"
                                    placeholder="Notes (optional)"
                                    value="{{ $existingItem->notes }}">
                            </div>
                            @endif
                        </div>
                        @endforeach
                    </div>
                </div>
                @if(!$loop->last)
                <hr>@endif
                @endforeach

                @php
                $customTestsForForm = old('custom_tests');
                if (!is_array($customTestsForForm)) {
                $customTestsForForm = $customTestItems->pluck('test_name')->all();
                }

                $hasCustomValue = collect($customTestsForForm)
                ->contains(function ($value) {
                return trim((string) $value) !== '';
                });

                if (!$hasCustomValue) {
                $customTestsForForm = [''];
                }
                @endphp

                <div class="border-top pt-3 mt-2">
                    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-2">
                        <h6 class="mb-0 fw-bold text-primary">
                            <i class="fas fa-plus-circle me-1"></i> Others (Custom Tests)
                        </h6>
                        <button type="button" class="btn btn-sm btn-outline-primary" id="add-custom-test-btn">
                            <i class="fas fa-plus me-1"></i> Add Row
                        </button>
                    </div>
                    <p class="text-muted small mb-2">Add or update custom laboratory/medical tests that are not in the checklist.</p>

                    <div id="custom-tests-container">
                        @foreach($customTestsForForm as $customTest)
                        <div class="input-group mb-2 custom-test-row">
                            <input type="text"
                                name="custom_tests[]"
                                class="form-control custom-test-input @error('custom_tests.*') is-invalid @enderror"
                                value="{{ $customTest }}"
                                placeholder="Enter custom test name">
                            <button type="button" class="btn btn-outline-danger remove-custom-test-btn" title="Remove this custom test">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                        @endforeach
                    </div>
                    @error('custom_tests.*')
                    <div class="text-danger small mt-1">{{ $message }}</div>
                    @enderror
                </div>

                @if($customTestItems->count() > 0)
                <div class="border-top pt-3 mt-3">
                    <h6 class="mb-3 fw-bold text-primary">
                        <i class="fas fa-notes-medical me-1"></i> Custom Test Results
                    </h6>

                    <div class="row g-2">
                        @foreach($customTestItems as $customItem)
                        <div class="col-md-6">
                            <div class="border rounded p-2 h-100">
                                <div class="fw-semibold mb-2">{{ $customItem->test_name }}</div>
                                <input type="text" name="results[{{ $customItem->id }}][result_value]"
                                    class="form-control form-control-sm mb-1"
                                    placeholder="Result value"
                                    value="{{ old('results.' . $customItem->id . '.result_value', $customItem->result_value) }}">
                                <select name="results[{{ $customItem->id }}][result_status]" class="form-select form-select-sm mb-1">
                                    @foreach(\App\Models\LabRequestItem::resultStatuses() as $key => $label)
                                    <option value="{{ $key }}" {{ old('results.' . $customItem->id . '.result_status', $customItem->result_status) === $key ? 'selected' : '' }}>{{ $label }}</option>
                                    @endforeach
                                </select>
                                <input type="text" name="results[{{ $customItem->id }}][notes]"
                                    class="form-control form-control-sm"
                                    placeholder="Notes (optional)"
                                    value="{{ old('results.' . $customItem->id . '.notes', $customItem->notes) }}">
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif
            </div>
        </div>

        <div class="d-flex gap-2 mb-5">
            <button type="submit" class="btn btn-primary px-4">
                <i class="fas fa-save me-1"></i> Save Changes
            </button>
            <a href="{{ getRouteByRole('lab-requests.index') }}" class="btn btn-outline-secondary px-4">Cancel</a>
        </div>
    </form>
</div>

{{-- Status Change Modal --}}
@if(!$lab_request->isTerminal() && count($allowedStatuses) > 0)
<div class="modal fade" id="statusModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title"><i class="fas fa-exchange-alt me-2"></i>Update Request Status</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ getRouteByRole('lab-requests.update-status', [$lab_request]) }}"
                method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">New Status</label>
                        <select name="status" class="form-select" required>
                            <option value="">-- Select new status --</option>
                            @foreach($allowedStatuses as $s)
                            <option value="{{ $s }}">{{ ucfirst($s) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Note (optional)</label>
                        <textarea name="status_note" class="form-control" rows="2"
                            placeholder="Reason or additional information..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update Status</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif
@endsection

@section('scripts')
<script>
    (function() {
        const badge = document.getElementById('selected-count-badge');
        const customTestsContainer = document.getElementById('custom-tests-container');
        const addCustomTestBtn = document.getElementById('add-custom-test-btn');

        function getCustomTestCount() {
            if (!customTestsContainer) {
                return 0;
            }

            return Array.from(customTestsContainer.querySelectorAll('.custom-test-input'))
                .filter((input) => input.value.trim() !== '')
                .length;
        }

        function bindCustomTestRowEvents(row) {
            const input = row.querySelector('.custom-test-input');
            const removeButton = row.querySelector('.remove-custom-test-btn');

            if (input) {
                input.addEventListener('input', updateBadge);
            }

            if (removeButton) {
                removeButton.addEventListener('click', function() {
                    row.remove();
                    updateBadge();
                });
            }
        }

        function appendCustomTestRow() {
            if (!customTestsContainer) {
                return;
            }

            const row = document.createElement('div');
            row.className = 'input-group mb-2 custom-test-row';
            row.innerHTML = `
            <input type="text" name="custom_tests[]" class="form-control custom-test-input" placeholder="Enter custom test name">
            <button type="button" class="btn btn-outline-danger remove-custom-test-btn" title="Remove this custom test">
                <i class="fas fa-times"></i>
            </button>
        `;

            customTestsContainer.appendChild(row);
            bindCustomTestRowEvents(row);
            updateBadge();
        }

        function updateBadge() {
            const selectedCheckboxCount = document.querySelectorAll('.test-checkbox:checked').length;
            const customTestCount = getCustomTestCount();
            const count = selectedCheckboxCount + customTestCount;

            badge.textContent = count + ' selected';
            badge.className = count > 0 ? 'badge bg-success text-white' : 'badge bg-white text-primary';
        }

        document.querySelectorAll('.test-checkbox').forEach(cb => cb.addEventListener('change', updateBadge));

        if (customTestsContainer) {
            customTestsContainer.querySelectorAll('.custom-test-row').forEach((row) => {
                bindCustomTestRowEvents(row);
            });
        }

        if (addCustomTestBtn) {
            addCustomTestBtn.addEventListener('click', function() {
                appendCustomTestRow();
            });
        }
        document.querySelectorAll('.select-all-category').forEach(btn => {
            btn.addEventListener('click', () => {
                document.querySelectorAll(`.lab-test-item[data-category="${btn.dataset.category}"] .test-checkbox`)
                    .forEach(cb => {
                        cb.checked = true;
                    });
                updateBadge();
            });
        });
        document.querySelectorAll('.deselect-all-category').forEach(btn => {
            btn.addEventListener('click', () => {
                document.querySelectorAll(`.lab-test-item[data-category="${btn.dataset.category}"] .test-checkbox`)
                    .forEach(cb => {
                        cb.checked = false;
                    });
                updateBadge();
            });
        });

        updateBadge();

        // Handle manual affiliation change
        const affilSelect = document.getElementById('status_affiliation');
        if (affilSelect) {
            affilSelect.addEventListener('change', function() {
                const val = this.value;
                const toggleFields = (id, condition) => {
                    const el = document.getElementById(id);
                    if (el) {
                        if (condition) el.classList.remove('d-none');
                        else el.classList.add('d-none');
                    }
                };

                toggleFields('student_fields', val === 'student');
                toggleFields('staff_fields', val === 'staff');
                toggleFields('faculty_fields', val === 'faculty');
            });
        }
    })();
</script>
@endsection