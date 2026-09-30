@extends('layouts.app')

@section('title')
New Laboratory / Medical Request
@endsection

@section('header_toolbar')
<div class="container-fluid">
    <div class="d-flex flex-wrap align-items-center justify-content-between mb-7">
        <div>
            <h1 class="mb-0">
                <i class="fa-solid fa-flask me-2" style="color:#2563a8;"></i>
                New Laboratory / Medical Request
            </h1>
            <p class="text-muted mb-0 mt-1" style="font-size:.88rem;">
                Fill in the patient details and select the required laboratory tests.
            </p>
        </div>
        <a href="{{ getRouteByRole('lab-requests.index') }}" class="btn btn-outline-secondary mt-4 mt-md-0">
            <i class="fas fa-arrow-left me-1"></i> Back to List
        </a>
    </div>
</div>
@endsection

@section('content')
<div class="container-fluid">
    @include('flash::message')

    <form action="{{ getRouteByRole('lab-requests.store') }}" method="POST" id="lab-request-form" novalidate>
        @csrf

        {{-- ================================================================== --}}
        {{-- ROW 1: Patient Search --}}
        {{-- ================================================================== --}}
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-primary text-white fw-bold">
                <i class="fas fa-user-injured me-2"></i> Patient Information
            </div>
            <div class="card-body">
                {{-- Hidden field for patient_user_id --}}
                <input type="hidden" name="patient_user_id" id="patient_user_id" value="{{ $user?->id ?? old('patient_user_id') }}">

                {{-- Patient search (hidden for patients who are pre-selected) --}}
                @if(!isRole('patient'))
                <div class="mb-4 position-relative">
                    <label class="form-label" style="font-size: 0.75rem;" for="user_search">Search User</label>
                    <input type="text" id="user_search" class="form-control"
                        placeholder="Search by name"
                        value="{{ $user ? $user->full_name : '' }}"
                        autocomplete="off">
                    <ul id="user_search_results" class="list-group position-absolute w-100 shadow-sm d-none" style="z-index: 1050; max-height: 250px; overflow-y: auto;"></ul>
                </div>
                @endif

                {{-- Patient demographics (auto-filled on select, or pre-filled if user known) --}}
                <div id="user_search_info" class="mt-3 {{ $user ? '' : 'd-none' }}">
                    <div class="row g-2">
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Full Name <span class="text-danger">*</span></label>
                            <input type="text" name="patient_name" id="patient_name"
                                class="form-control @error('patient_name') is-invalid @enderror"
                                value="{{ $user?->full_name ?? old('patient_name') }}" required readonly>
                            @error('patient_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-2">
                            <label class="form-label fw-semibold">Age</label>
                            <input type="number" name="patient_age" id="patient_age" class="form-control"
                                value="{{ ($user && $user->dob) ? \Carbon\Carbon::parse($user->dob)->age : old('patient_age') }}" readonly>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label fw-semibold">Gender</label>
                            <select name="patient_gender" id="patient_gender" class="form-select" style="pointer-events:none;background-color:#e9ecef;">
                                <option value="">Select</option>
                                <option value="Male" {{ ($user && $user->gender === \App\Models\User::MALE) ? 'selected' : '' }}>Male</option>
                                <option value="Female" {{ ($user && $user->gender === \App\Models\User::FEMALE) ? 'selected' : '' }}>Female</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Contact No.</label>
                            <input type="text" name="patient_contact" id="patient_contact" class="form-control"
                                value="{{ $user?->contact ?? old('patient_contact') }}" readonly>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Address</label>
                            <input type="text" name="address" id="address" class="form-control"
                                value="{{ $user?->address?->full_address ?? ($patient?->address?->full_address ?? old('address')) }}" readonly>
                        </div>
                        <div id="student_fields" class="row g-2 col-md-12 {{ ($user && ($user->patient?->patientType?->code === 'student' || old('status_affiliation') === 'student')) ? '' : 'd-none' }}">
                            <div class="col-md-3">
                                <label class="form-label fw-semibold">Campus</label>
                                <input type="text" name="campus" id="campus" class="form-control"
                                    value="{{ $user?->campus?->campus_name ?? old('campus') }}" readonly>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-semibold">College</label>
                                <input type="text" name="college" id="college" class="form-control"
                                    value="{{ $user?->college?->college_name ?? old('college') }}" readonly>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-semibold">Course / Program</label>
                                <input type="text" name="course" id="course" class="form-control"
                                    value="{{ $user?->course?->course_name ?? old('course') }}" readonly>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-semibold">Year Level</label>
                                <input type="text" name="year_level" id="year_level" class="form-control"
                                    value="{{ $user?->yearLevel?->year_level_name ?? old('year_level') }}" readonly>
                            </div>
                        </div>

                        <div id="staff_fields" class="row g-2 col-md-12 {{ ($user && ($user->patient?->patientType?->code === 'staff' || old('status_affiliation') === 'staff')) ? '' : 'd-none' }}">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Office</label>
                                <input type="text" name="office" id="office" class="form-control"
                                    value="{{ $user?->office?->office_name ?? old('office') }}" readonly>
                            </div>
                        </div>

                        <div id="faculty_fields" class="row g-2 col-md-12 {{ ($user && ($user->patient?->patientType?->code === 'faculty' || old('status_affiliation') === 'faculty')) ? '' : 'd-none' }}">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">College</label>
                                <input type="text" name="college_faculty" id="college_faculty" class="form-control"
                                    value="{{ $user?->college?->college_name ?? old('college') }}" readonly>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Department</label>
                                <input type="text" name="department" id="department" class="form-control"
                                    value="{{ $user?->department?->department_name ?? old('department') }}" readonly>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">Affiliation / Status</label>

                            <select name="status_affiliation" id="status_affiliation" class="form-select" style="pointer-events:none;background-color:#e9ecef;">
                                <option value="">Select</option>
                                <option value="student" {{ old('status_affiliation') == 'student' ? 'selected' : '' }}>Student</option>
                                <option value="staff" {{ old('status_affiliation') == 'staff' ? 'selected' : '' }}>Staff</option>
                                <option value="faculty" {{ old('status_affiliation') == 'faculty' ? 'selected' : '' }}>Faculty</option>
                                <option value="guest" {{ old('status_affiliation') == 'guest' ? 'selected' : '' }}>Guest</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ================================================================== --}}
        {{-- ROW 2: Request Details --}}
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
                            value="{{ old('requested_at', now()->format('Y-m-d')) }}" required>
                        @error('requested_at')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-5">
                        <label class="form-label fw-semibold">Clinical Indication / Purpose</label>
                        <input type="text" name="clinical_indication" class="form-control"
                            value="{{ old('clinical_indication') }}"
                            placeholder="e.g., Pre-employment, Annual PE, Illness screening...">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Requesting Physician</label>

                        <select name="requesting_physician" id="requesting_physician" class="form-select @error('requesting_physician') is-invalid @enderror">
                            <option value="">Select Physician</option>
                            @foreach($physicians as $physician)
                            <option value="{{ $physician->full_name }}" data-license="{{ $physician->doctor?->prc_license_number }}">
                                {{ $physician->full_name }}
                            </option>
                            @endforeach
                        </select>
                        @error('requesting_physician')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Physician License No.</label>
                        <input type="text" name="physician_license_no" class="form-control"
                            value="{{ old('physician_license_no') }}">
                    </div>
                    <div class="col-md-8">
                        <label class="form-label fw-semibold">Additional Remarks</label>
                        <textarea name="remarks" class="form-control" rows="2"
                            placeholder="Additional remarks or instructions...">{{ old('remarks') }}</textarea>
                    </div>
                </div>
            </div>
        </div>

        {{-- ================================================================== --}}
        {{-- ROW 3: Laboratory Tests Selection --}}
        {{-- ================================================================== --}}
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                <span class="fw-bold"><i class="fas fa-vials me-2"></i> Select Laboratory / Medical Tests</span>
                <span class="badge bg-white text-primary" id="selected-count-badge">0 selected</span>
            </div>
            <div class="card-body">
                @error('test_ids')
                <div class="alert alert-danger py-2">
                    <i class="fas fa-exclamation-triangle me-1"></i> {{ $message }}
                </div>
                @enderror
                @error('custom_tests')
                <div class="alert alert-danger py-2">
                    <i class="fas fa-exclamation-triangle me-1"></i> {{ $message }}
                </div>
                @enderror

                @foreach($labTestsGrouped as $category => $tests)
                <div class="mb-4">
                    <div class="d-flex align-items-center mb-2 gap-2">
                        <span class="badge bg-secondary fs-7">{{ $category }}</span>
                        <button type="button" class="btn btn-link btn-sm p-0 select-all-category"
                            data-category="{{ $category }}">
                            Select All
                        </button>
                        <button type="button" class="btn btn-link btn-sm p-0 deselect-all-category text-danger"
                            data-category="{{ $category }}">
                            Clear
                        </button>
                    </div>
                    <div class="row g-2">
                        @foreach($tests as $test)
                        <div class="col-md-4 col-lg-3">
                            <div class="form-check lab-test-item" data-category="{{ $test->category }}">
                                <input class="form-check-input test-checkbox" type="checkbox"
                                    name="test_ids[]"
                                    value="{{ $test->id }}"
                                    id="test_{{ $test->id }}"
                                    {{ in_array($test->id, old('test_ids', [])) ? 'checked' : '' }}>
                                <label class="form-check-label user-select-none" for="test_{{ $test->id }}">
                                    {{ $test->name }}
                                    @if($test->unit)
                                    <small class="text-muted">({{ $test->unit }})</small>
                                    @endif
                                </label>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
                @if(!$loop->last)
                <hr>@endif
                @endforeach

                @php
                $oldCustomTests = old('custom_tests', ['']);
                if (!is_array($oldCustomTests)) {
                $oldCustomTests = [$oldCustomTests];
                }

                $hasNonEmptyCustomTest = collect($oldCustomTests)
                ->contains(function ($value) {
                return trim((string) $value) !== '';
                });

                if (!$hasNonEmptyCustomTest) {
                $oldCustomTests = [''];
                }
                @endphp

                <div class="border-top pt-3">
                    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-2">
                        <h6 class="mb-0 fw-bold text-primary">
                            <i class="fas fa-plus-circle me-1"></i> Others (Custom Tests)
                        </h6>
                        <button type="button" class="btn btn-sm btn-outline-primary" id="add-custom-test-btn">
                            <i class="fas fa-plus me-1"></i> Add Row
                        </button>
                    </div>
                    <p class="text-muted small mb-2">Add laboratory/medical tests not found in the list above.</p>

                    <div id="custom-tests-container">
                        @foreach($oldCustomTests as $customTest)
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
            </div>
        </div>

        {{-- Submit --}}
        <div class="d-flex gap-2 mb-5">
            <button type="submit" class="btn btn-primary px-4" id="submit-btn">
                <i class="fas fa-save me-1"></i> Create Lab Request
            </button>
            <a href="{{ getRouteByRole('lab-requests.index') }}" class="btn btn-outline-secondary px-4">
                Cancel
            </a>
        </div>
    </form>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {

        function calculateAge(dob) {
            if (!dob) return '';
            const birthDate = new Date(dob);
            const today = new Date();
            let age = today.getFullYear() - birthDate.getFullYear();
            const m = today.getMonth() - birthDate.getMonth();
            if (m < 0 || (m === 0 && today.getDate() < birthDate.getDate())) {
                age--;
            }
            return age;
        }

        // -------------------------------------------------------------------------
        // Patient search (AJAX)
        // -------------------------------------------------------------------------
        const searchInput = document.getElementById('user_search');
        const searchResults = document.getElementById('user_search_results');
        const patientSection = document.getElementById('user_search_info');
        const searchUsersRoute = @json(parse_url(getRouteByRole('lab-requests.search-users'), PHP_URL_PATH) ? : getRouteByRole('lab-requests.search-users'));

        if (searchInput) {
            let debounceTimer;
            let isSelecting = false;

            searchInput.addEventListener('keydown', function(e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                }
            });

            searchInput.addEventListener('input', function() {
                if (isSelecting) return;

                clearTimeout(debounceTimer);
                const q = this.value.trim();

                if (q.length > 1) {
                    debounceTimer = setTimeout(() => {
                        fetch(`${searchUsersRoute}?query=${encodeURIComponent(q)}`)
                            .then(r => {
                                if (!r.ok) throw new Error('Network response was not ok');
                                return r.json();
                            })
                            .then(data => {
                                searchResults.innerHTML = '';
                                searchResults.classList.remove('d-none');
                                searchResults.style.display = 'block';

                                if (!data || data.length === 0) {
                                    const noResults = document.createElement('div');
                                    noResults.className = 'list-group-item text-muted';
                                    noResults.textContent = 'No patients found.';
                                    searchResults.appendChild(noResults);
                                    return;
                                }

                                data.forEach(user => {
                                    const option = document.createElement('a');
                                    option.href = 'javascript:void(0)';
                                    option.className = 'list-group-item list-group-item-action py-2';
                                    option.textContent = user.name;

                                    option.addEventListener('click', (e) => {
                                        e.preventDefault();
                                        isSelecting = true;

                                        // The backend now returns a flattened object with names
                                        fillPatient(user);

                                        searchResults.classList.add('d-none');
                                        searchResults.style.display = 'none';
                                        searchInput.value = user.name;
                                        setTimeout(() => {
                                            isSelecting = false;
                                        }, 200);
                                    });

                                    searchResults.appendChild(option);
                                });
                            })
                            .catch((error) => {
                                console.error('Search failed:', error);
                                searchResults.classList.add('d-none');
                                searchResults.style.display = 'none';
                            });
                    }, 300);
                } else {
                    searchResults.classList.add('d-none');
                    searchResults.style.display = 'none';
                }
            });

            document.addEventListener('click', e => {
                if (!searchInput.contains(e.target) && !searchResults.contains(e.target)) {
                    searchResults.classList.add('d-none');
                    searchResults.style.display = 'none';
                }
            });
        }

        function fillPatient(u) {
            try {
                const safeSet = (id, val) => {
                    const el = document.getElementById(id);
                    if (el) el.value = val || '';
                    else console.warn(`Element ${id} not found`);
                };

                safeSet('patient_user_id', u.id);
                safeSet('patient_name', u.name);
                safeSet('patient_age', u.age);
                safeSet('patient_gender', u.gender);
                safeSet('patient_contact', u.contact);
                safeSet('address', u.address);
                safeSet('campus', u.campus);
                safeSet('college', u.college);
                safeSet('course', u.course);
                safeSet('year_level', u.year_level);
                safeSet('status_affiliation', u.status_affiliation);
                safeSet('department', u.department);
                safeSet('office', u.office);
                safeSet('college_faculty', u.college);

                if (patientSection) {
                    patientSection.style.display = 'block';
                    patientSection.classList.remove('d-none');
                }

                // Toggle fields based on affiliation
                const toggleFields = (id, condition) => {
                    const el = document.getElementById(id);
                    if (el) {
                        if (condition) el.classList.remove('d-none');
                        else el.classList.add('d-none');
                    }
                };

                toggleFields('student_fields', u.status_affiliation === 'student');
                toggleFields('staff_fields', u.status_affiliation === 'staff');
                toggleFields('faculty_fields', u.status_affiliation === 'faculty');
            } catch (err) {
                console.error("Error inside fillPatient:", err);
            }
        }

        // physician license auto-fill
        const physSelect = document.getElementById('requesting_physician');
        if (physSelect) {
            physSelect.addEventListener('change', function() {
                const selected = this.options[this.selectedIndex];
                const license = selected.dataset.license;
                const licenseInput = document.querySelector('input[name="physician_license_no"]');
                if (licenseInput) {
                    licenseInput.value = license || '';
                }
            });
        }

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

        // -------------------------------------------------------------------------
        // Selected tests counter
        // -------------------------------------------------------------------------
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

        function appendCustomTestRow(value = '') {
            if (!customTestsContainer) {
                return;
            }

            const row = document.createElement('div');
            row.className = 'input-group mb-2 custom-test-row';
            row.innerHTML = `
                <input type="text" name="custom_tests[]" class="form-control custom-test-input" placeholder="Enter custom test name" value="${String(value).replace(/[&<>"']/g, (ch) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[ch]))}">
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
            badge.classList.toggle('bg-white', count === 0);
            badge.classList.toggle('text-primary', count === 0);
            badge.classList.toggle('bg-success', count > 0);
            badge.classList.toggle('text-white', count > 0);
        }

        document.querySelectorAll('.test-checkbox').forEach(cb => {
            cb.addEventListener('change', updateBadge);
        });

        if (customTestsContainer) {
            customTestsContainer.querySelectorAll('.custom-test-row').forEach((row) => {
                bindCustomTestRowEvents(row);
            });
        }

        if (addCustomTestBtn) {
            addCustomTestBtn.addEventListener('click', function() {
                appendCustomTestRow('');
            });
        }

        // Select All / Clear per category
        document.querySelectorAll('.select-all-category').forEach(btn => {
            btn.addEventListener('click', () => {
                const cat = btn.dataset.category;
                document.querySelectorAll(`.lab-test-item[data-category="${cat}"] .test-checkbox`)
                    .forEach(cb => {
                        cb.checked = true;
                    });
                updateBadge();
            });
        });

        document.querySelectorAll('.deselect-all-category').forEach(btn => {
            btn.addEventListener('click', () => {
                const cat = btn.dataset.category;
                document.querySelectorAll(`.lab-test-item[data-category="${cat}"] .test-checkbox`)
                    .forEach(cb => {
                        cb.checked = false;
                    });
                updateBadge();
            });
        });

        // Initial count on page load (for old() re-population)
        updateBadge();

        // -------------------------------------------------------------------------
        // Form validation
        // -------------------------------------------------------------------------
        document.getElementById('lab-request-form').addEventListener('submit', function(e) {
            const pid = document.getElementById('patient_user_id').value;
            const selected = document.querySelectorAll('.test-checkbox:checked').length;
            const customTests = getCustomTestCount();

            if (!pid) {
                e.preventDefault();
                alert('Please search and select a patient first.');
                return;
            }

            if (selected === 0 && customTests === 0) {
                e.preventDefault();
                alert('Please select at least one laboratory/medical test or add one custom test in Others.');
                return;
            }
        });

    });
</script>
@endsection