<div>
    @if($user->type != 3 && !request('user_id'))
    <!-- Only show user search for consultation forms, not for medical certificates with user_id -->
    <div class="mb-10">
        <label class="block text-xs" for="user_search">Search User</label>
        <input type="text" id="user_search" class="w-full border-b border-black" placeholder="Search by name" autocomplete="off">
        <div id="user_search_results" class="absolute bg-white border border-gray-300 w-fit hidden z-10"></div>
    </div>
    @endif


    <div class="bg-white p-6 rounded-lg shadow-lg" style="width: 1065px;">
        <div class="flex items-center my-4">
            <!-- Left Logo -->
            <div>
                <img src="{{ asset('assets/image/norsu_logo.png') }}" alt="Logo" class="w-28 h-28">
            </div>

            <!-- Text Content -->
            <div class="text-center flex-1">
                <h1 class="text-xl font-bold">Negros Oriental State University</h1>
                <h2 class="text-md">University Medical Clinic, CNPAHS Bldg., Kagawasan Ave., Dumaguete City</h2>
                <p class="text-sm">Tel #: 225-9400, then Local # 188, 09263829484</p>
            </div>

            <!-- Right Logo -->
            <div class="ml-4">
                <img src="{{ asset('assets/image/norsu_clinic_logo.png') }}" alt="Logo" class="w-28 h-28">
            </div>
        </div>

        <h3 class="text-lg text-center font-semibold mb-8">MEDICAL CERTIFICATE</h3>
        <form action="{{ 
            isRole('clinic_admin') ? route('request-documents.store') : 
            (isRole('staff') ? route('staff.request-documents.store') : 
            (isRole('doctor') ? route('doctors.request-documents.store') : route('request-documents.store')))
        }}" method="POST">
            @csrf
            <div class="form-group mb-5 d-none">
                <label for="document_type">Document Type</label>
                <select name="document_type" id="document_type" class="form-control" required>
                    <option value="medical_certificate" selected>Medical Certificate</option>
                </select>
            </div>

            <div class="flex row">
                <p>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;This is to certify that Mr./Ms.
                    <input type="text" id="document_creator_id" name="document_creator_id" style="width: 400px; text-align: center;" class="border-b border-black d-none" value="{{ auth()->user()->id }}" readonly required>
                    <input type="text" id="user_id" name="user_id" style="width: 400px; text-align: center;" class="border-b border-black d-none" readonly required>
                    <!-- Hidden field to indicate redirect to patient history -->
                    <input type="hidden" name="redirect_to_patient" value="{{ request('user_id') ? '1' : '0' }}">
                    <input type="text" id="name_2" name="name" style="width: 400px; text-align: center;" class="border-b border-black" value="{{ $user->type == 3 ? $user->first_name . ' ' . $user->last_name : '' }}" readonly required>,
                    <input type="text" id="age_2" name="age" style="width: 70px; text-align: center;" class="border-b border-black" value="{{ $user->type == 3 ? \Carbon\Carbon::parse($user->dob)->age : '' }}" readonly required> yrs old,
                    <input type="text" id="gender_2" name="gender" style="width: 70px; text-align: center;" class="border-b border-black" value="{{ $user->type == 3 ? ($user->gender == 1 ? 'Male' : 'Female') : '' }}" readonly required> a resident of
                </p>
                <p>
                    <input type="text" id="address_2" name="address" style="width: 470px; text-align: center;" class="border-b border-black" value="{{ $user->type == 3 && $patient->address ? $patient->address->address1 : '' }}" {{ $user->type == 3 ? 'readonly' : '' }} required>
                    , was seen and examined at my clinic on
                    <input type="text" id="examined_on_display" name="examined_on_display" style="width: 300px; text-align: center;" class="border-b border-black" placeholder="Click to select date(s)" readonly required>
                    <input type="hidden" id="examined_on" name="examined_on">
                    <button type="button" id="open_date_selector" class="btn btn-sm btn-primary ml-2" style="padding: 2px 8px; font-size: 12px;">
                        <i class="fas fa-calendar-alt"></i> Select Dates
                    </button>
                    with the following
                <p class="font-semibold">complaints/diagnosis:</p>
                <div class="border border-gray-300 p-2 h-28 mb-4">
                    <div class="col-span-3">
                        <textarea id="complaints_diagnosis" name="complaints_diagnosis" class="w-full border-black" rows="4" required></textarea>
                    </div>
                </div>
                </p>
            </div>

            <div class="grid grid-cols-6 grid-rows-1 gap-7 mb-2">
                <div>
                    <p class="font-semibold">BP<span class="text-red-500">*</span>: <input type="text" id="vital_signs_bp_2" name="vital_signs_bp_2" style="width: 30px; text-align: center;" class="border-b border-black" required> / <input type="text" id="vital_signs_bp_22" name="vital_signs_bp_22" style="width: 30px; text-align: center;" class="border-b border-black" required></p>
                </div>
                <div>
                    <p class="font-semibold">P<span class="text-red-500">*</span>: <input type="text" id="vital_signs_pr_2" name="vital_signs_pr_2" style="width: 50px; text-align: center;" class="border-b border-black" required></p>
                </div>
                <div>
                    <p class="font-semibold">R<span class="text-red-500">*</span>: <input type="text" id="vital_signs_rr_2" name="vital_signs_rr_2" style="width: 50px; text-align: center;" class="border-b border-black" required></p>
                </div>
                <div>
                    <p class="font-semibold">T<span class="text-red-500">*</span>: <input type="text" id="vital_signs_temp_2" name="vital_signs_temp_2" style="width: 50px; text-align: center;" class="border-b border-black" required></p>
                </div>
                <div>
                    <p class="font-semibold">Ht<span class="text-red-500">*</span>: <input type="text" id="vital_signs_height_2" name="vital_signs_height_2" style="width: 50px; text-align: center;" class="border-b border-black" required></p>
                </div>
                <div>
                    <p class="font-semibold">Wt<span class="text-red-500">*</span>: <input type="text" id="vital_signs_weight_2" name="vital_signs_weight_2" style="width: 50px; text-align: center;" class="border-b border-black" required></p>
                </div>
            </div>

            <p class="font-semibold">Remark/s:</p>
            <div class="border border-gray-300 p-2 h-28 mb-4">
                <div class="col-span-3">
                    <textarea id="medical_cert_remarks" name="medical_cert_remarks" class="w-full border-black" rows="4" required></textarea>
                </div>
            </div>

            <p class="text-sm text-black">Note: Please check the original copy of med cert before accepting the photocopied med cert. This medical certificate is <span class="font-bold underline">not to be used</span> outside school purposes or medico-legal purposes.</p>

            <p class="text-sm">This certificate is issued upon the request of <input type="text" id="request_of" name="request_of" style="width: 350px; text-align: center;" class="border-b border-black" required> for your reference.</p>

            <div class="text-right mt-4 mr-5">
                <p class="font-semibold">Dr. Michael S. Oliveros</p>
                <p class="text-xs">Lic #: <input type="text" id="doc_lic_no" name="doc_lic_no" style="width: 110px; text-align: center;" class="border-b border-black" value="0113005" required></p>
                <p class=" text-xs">PTR #: <input type="text" id="doc_prt_no" name="doc_prt_no" style="width: 105px; text-align: center;" class="border-b border-black" required></p>
            </div>

            <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded mt-4 text-center">
                Submit
            </button>
        </form>
    </div>

</div>

<!-- Date Selector Modal -->
<div id="date_selector_modal" class="modal fade" tabindex="-1" aria-labelledby="dateSelectorModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="dateSelectorModalLabel">Select Examination Date(s)</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label fw-bold">Select Date Type:</label>
                    <div class="btn-group w-100" role="group">
                        <input type="radio" class="btn-check" name="date_type" id="single_date_radio" value="single" checked>
                        <label class="btn btn-outline-primary" for="single_date_radio">Single Date</label>

                        <input type="radio" class="btn-check" name="date_type" id="date_range_radio" value="range">
                        <label class="btn btn-outline-primary" for="date_range_radio">Date Range</label>

                        <input type="radio" class="btn-check" name="date_type" id="multiple_dates_radio" value="multiple">
                        <label class="btn btn-outline-primary" for="multiple_dates_radio">Multiple Dates</label>
                    </div>
                </div>

                <!-- Single Date -->
                <div id="single_date_section" class="date-section">
                    <label for="single_date_input" class="form-label">Select Date:</label>
                    <input type="date" id="single_date_input" class="form-control" max="{{ date('Y-m-d') }}" value="{{ date('Y-m-d') }}">
                </div>

                <!-- Date Range -->
                <div id="date_range_section" class="date-section" style="display: none;">
                    <div class="row">
                        <div class="col-md-6">
                            <label for="start_date_input" class="form-label">Start Date:</label>
                            <input type="date" id="start_date_input" class="form-control" max="{{ date('Y-m-d') }}">
                        </div>
                        <div class="col-md-6">
                            <label for="end_date_input" class="form-label">End Date:</label>
                            <input type="date" id="end_date_input" class="form-control" max="{{ date('Y-m-d') }}">
                        </div>
                    </div>
                </div>

                <!-- Multiple Dates -->
                <div id="multiple_dates_section" class="date-section" style="display: none;">
                    <label for="add_date_input" class="form-label">Add Date:</label>
                    <div class="input-group mb-3">
                        <input type="date" id="add_date_input" class="form-control" max="{{ date('Y-m-d') }}">
                        <button type="button" id="add_date_btn" class="btn btn-success">
                            <i class="fas fa-plus"></i> Add
                        </button>
                    </div>
                    <div id="selected_dates_list" class="border rounded p-3" style="min-height: 100px; max-height: 200px; overflow-y: auto;">
                        <p class="text-muted text-center mb-0">No dates selected</p>
                    </div>
                </div>

                <!-- Preview -->
                <div class="mt-4 p-3 bg-light rounded">
                    <label class="form-label fw-bold">Preview:</label>
                    <p id="date_preview" class="mb-0 text-primary">No date selected</p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" id="apply_dates_btn" class="btn btn-primary">Apply Dates</button>
            </div>
        </div>
    </div>
</div>

<style>
    #complaints_diagnosis,
    #medical_cert_remarks {
        resize: none;
    }

    .selected-date-item {
        display: inline-block;
        background: #e7f3ff;
        border: 1px solid #2196F3;
        border-radius: 4px;
        padding: 5px 10px;
        margin: 3px;
        font-size: 14px;
    }

    .selected-date-item .remove-date {
        margin-left: 8px;
        color: #d32f2f;
        cursor: pointer;
        font-weight: bold;
    }

    .selected-date-item .remove-date:hover {
        color: #b71c1c;
    }
</style>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Pre-fill user_id if coming from patient history
        @if(request('user_id') && $patient)
        document.getElementById('user_id').value = '{{ request("user_id") }}';
        @endif

        // Initialize with today's date
        const today = new Date().toLocaleDateString('en-US', {
            year: 'numeric',
            month: '2-digit',
            day: '2-digit'
        });
        document.getElementById('examined_on_display').value = today;
        document.getElementById('examined_on').value = '{{ date("Y-m-d") }}';

        const userSearchInput = document.getElementById('user_search');
        const userSearchResults = document.getElementById('user_search_results');

        // Set the search route based on user role
        @if(isRole('clinic_admin'))
        const searchRoute = '{{ route("search-users") }}';
        @elseif(isRole('staff'))
        const searchRoute = '{{ route("staff.request-documents.search-users") }}';
        @elseif(isRole('doctor'))
        const searchRoute = '{{ route("doctors.request-documents.search-users") }}';
        @else
        const searchRoute = '{{ route("search-users") }}';
        @endif

        if (userSearchInput) {
            userSearchInput.addEventListener('input', function() {
                const query = userSearchInput.value;

                if (query.length > 1) {
                    fetch(`${searchRoute}?query=${query}`)
                        .then(response => response.json())
                        .then(data => {
                            userSearchResults.innerHTML = '';
                            userSearchResults.classList.remove('hidden');

                            if (data.length === 0) {
                                const noResults = document.createElement('div');
                                noResults.classList.add('p-2', 'text-gray-500');
                                noResults.textContent = 'No patients found.';
                                userSearchResults.appendChild(noResults);
                                return;
                            }

                            data.forEach(patient => {
                                const option = document.createElement('div');
                                option.classList.add('p-2', 'cursor-pointer', 'hover:bg-gray-200');
                                option.textContent = `${patient.user.first_name} ${patient.user.last_name}`;
                                option.dataset.patient = JSON.stringify(patient);

                                option.addEventListener('click', function() {
                                    const patientData = JSON.parse(this.dataset.patient);

                                    document.getElementById('user_id').value = patientData.user.id;
                                    document.getElementById('name_2').value = `${patientData.user.full_name}`;
                                    document.getElementById('request_of').value = `${patientData.user.full_name}`;
                                    document.getElementById('age_2').value = calculateAge(patientData.user.dob);
                                    document.getElementById('gender_2').value = patientData.user.gender === 1 ? 'Male' : 'Female';

                                    if (patientData.address) {
                                        document.getElementById('address_2').value = `${patientData.address.address1}`;
                                    }

                                    userSearchResults.classList.add('hidden');
                                });

                                userSearchResults.appendChild(option);
                            });
                        })
                        .catch(error => {
                            console.error('Error fetching patients:', error);
                        });
                } else {
                    userSearchResults.classList.add('hidden');
                }
            });

            document.addEventListener('click', function(e) {
                if (!userSearchResults.contains(e.target) && e.target !== userSearchInput) {
                    userSearchResults.classList.add('hidden');
                }
            });
        } else {
            console.warn('Element with id "user_search" not found. Skipping event listener.');
        }

        function calculateAge(dob) {
            if (!dob) return '';
            const birthDate = new Date(dob);
            if (isNaN(birthDate)) return '';
            const today = new Date();
            let age = today.getFullYear() - birthDate.getFullYear();
            const monthDiff = today.getMonth() - birthDate.getMonth();
            if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < birthDate.getDate())) {
                age--;
            }
            return age;
        }

        // ==================== DATE SELECTOR FUNCTIONALITY ====================

        let selectedDatesArray = []; // For multiple dates mode
        let currentDateType = 'single';

        const dateSelectorModal = new bootstrap.Modal(document.getElementById('date_selector_modal'));
        const openDateSelectorBtn = document.getElementById('open_date_selector');
        const applyDatesBtn = document.getElementById('apply_dates_btn');
        const dateTypeRadios = document.querySelectorAll('input[name="date_type"]');

        // Open modal
        openDateSelectorBtn.addEventListener('click', function() {
            dateSelectorModal.show();
            updatePreview();
        });

        // Switch between date types
        dateTypeRadios.forEach(radio => {
            radio.addEventListener('change', function() {
                currentDateType = this.value;

                // Hide all sections
                document.getElementById('single_date_section').style.display = 'none';
                document.getElementById('date_range_section').style.display = 'none';
                document.getElementById('multiple_dates_section').style.display = 'none';

                // Show selected section
                if (currentDateType === 'single') {
                    document.getElementById('single_date_section').style.display = 'block';
                } else if (currentDateType === 'range') {
                    document.getElementById('date_range_section').style.display = 'block';
                } else if (currentDateType === 'multiple') {
                    document.getElementById('multiple_dates_section').style.display = 'block';
                }

                updatePreview();
            });
        });

        // Single date change
        document.getElementById('single_date_input').addEventListener('change', updatePreview);

        // Date range changes
        document.getElementById('start_date_input').addEventListener('change', function() {
            // Set end date minimum to start date
            const startDate = this.value;
            document.getElementById('end_date_input').min = startDate;
            updatePreview();
        });

        document.getElementById('end_date_input').addEventListener('change', updatePreview);

        // Add date to multiple dates
        document.getElementById('add_date_btn').addEventListener('click', function() {
            const dateInput = document.getElementById('add_date_input');
            const dateValue = dateInput.value;

            if (!dateValue) {
                alert('Please select a date');
                return;
            }

            if (selectedDatesArray.includes(dateValue)) {
                alert('This date is already added');
                return;
            }

            selectedDatesArray.push(dateValue);
            selectedDatesArray.sort(); // Sort dates chronologically
            renderSelectedDates();
            updatePreview();
            dateInput.value = '';
        });

        function renderSelectedDates() {
            const listContainer = document.getElementById('selected_dates_list');

            if (selectedDatesArray.length === 0) {
                listContainer.innerHTML = '<p class="text-muted text-center mb-0">No dates selected</p>';
                return;
            }

            listContainer.innerHTML = '';
            selectedDatesArray.forEach((date, index) => {
                const dateItem = document.createElement('span');
                dateItem.className = 'selected-date-item';
                dateItem.innerHTML = `
                    ${formatDateDisplay(date)}
                    <span class="remove-date" data-index="${index}">&times;</span>
                `;
                listContainer.appendChild(dateItem);
            });

            // Add remove functionality
            document.querySelectorAll('.remove-date').forEach(btn => {
                btn.addEventListener('click', function() {
                    const index = parseInt(this.dataset.index);
                    selectedDatesArray.splice(index, 1);
                    renderSelectedDates();
                    updatePreview();
                });
            });
        }

        function updatePreview() {
            const preview = document.getElementById('date_preview');
            let previewText = '';

            if (currentDateType === 'single') {
                const singleDate = document.getElementById('single_date_input').value;
                previewText = singleDate ? formatDateDisplay(singleDate) : 'No date selected';
            } else if (currentDateType === 'range') {
                const startDate = document.getElementById('start_date_input').value;
                const endDate = document.getElementById('end_date_input').value;

                if (startDate && endDate) {
                    previewText = `${formatDateDisplay(startDate)} - ${formatDateDisplay(endDate)}`;
                } else if (startDate) {
                    previewText = `${formatDateDisplay(startDate)} - (End date not selected)`;
                } else {
                    previewText = 'No date range selected';
                }
            } else if (currentDateType === 'multiple') {
                if (selectedDatesArray.length === 0) {
                    previewText = 'No dates selected';
                } else {
                    previewText = selectedDatesArray.map(date => formatDateDisplay(date)).join(', ');
                }
            }

            preview.textContent = previewText;
        }

        function formatDateDisplay(dateString) {
            if (!dateString) return '';
            const date = new Date(dateString + 'T00:00:00'); // Add time to avoid timezone issues
            return date.toLocaleDateString('en-US', {
                month: '2-digit',
                day: '2-digit',
                year: 'numeric'
            });
        }

        // Apply dates
        applyDatesBtn.addEventListener('click', function() {
            let displayValue = '';
            let storageValue = '';

            if (currentDateType === 'single') {
                const singleDate = document.getElementById('single_date_input').value;
                if (!singleDate) {
                    alert('Please select a date');
                    return;
                }
                displayValue = formatDateDisplay(singleDate);
                storageValue = singleDate;
            } else if (currentDateType === 'range') {
                const startDate = document.getElementById('start_date_input').value;
                const endDate = document.getElementById('end_date_input').value;

                if (!startDate || !endDate) {
                    alert('Please select both start and end dates');
                    return;
                }

                if (new Date(endDate) < new Date(startDate)) {
                    alert('End date cannot be before start date');
                    return;
                }

                displayValue = `${formatDateDisplay(startDate)} - ${formatDateDisplay(endDate)}`;
                storageValue = `${startDate}|${endDate}|range`;
            } else if (currentDateType === 'multiple') {
                if (selectedDatesArray.length === 0) {
                    alert('Please add at least one date');
                    return;
                }
                displayValue = selectedDatesArray.map(date => formatDateDisplay(date)).join(', ');
                storageValue = selectedDatesArray.join(',') + '|multiple';
            }

            document.getElementById('examined_on_display').value = displayValue;
            document.getElementById('examined_on').value = storageValue;
            dateSelectorModal.hide();
        });
    });
</script>