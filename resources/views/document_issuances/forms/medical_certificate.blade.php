<div>
    @if($user->type != 4 && !request('user_id') && !isset($requestDocument))
    <div class="mb-10">
        <label class="block text-xs" for="user_search">Search User</label>
        <input type="text" id="user_search" class="w-full border-b border-black" placeholder="Search by name" autocomplete="off">
        <div id="user_search_results" class="absolute bg-white border border-gray-300 w-fit hidden z-10"></div>
    </div>
    @endif

    @php
    $doctorOptions = collect($availableDoctors ?? [])->filter(function ($doctor) {
    return $doctor->doctor !== null;
    })->map(function ($doctor) {
    return [
    'id' => $doctor->id,
    'name' => trim(($doctor->first_name ?? '') . ' ' . ($doctor->last_name ?? '')),
    'lic_no' => (string) ($doctor->doctor->prc_license_number ?? ''),
    'ptr_no' => (string) ($doctor->doctor->ptr_number ?? ''),
    ];
    })->values();

    $singleDoctor = $doctorOptions->count() === 1 ? $doctorOptions->first() : null;
    
    // Logic to match existing doctor if editing
    $selectedDoctorId = old('doctor_user_id', '');
    if (isset($requestDocument) && !$selectedDoctorId) {
        $currentLicNo = (string) $requestDocument->doc_lic_no;
        $currentPtrNo = (string) $requestDocument->doc_prt_no;
        $matchedDoctor = $doctorOptions->first(function ($doctorOption) use ($currentLicNo, $currentPtrNo) {
            return ($currentLicNo !== '' && (string) $doctorOption['lic_no'] === $currentLicNo)
                || ($currentPtrNo !== '' && (string) $doctorOption['ptr_no'] === $currentPtrNo);
        });
        $selectedDoctorId = $matchedDoctor['id'] ?? '';
    }
    
    if (!$selectedDoctorId && $singleDoctor) {
        $selectedDoctorId = $singleDoctor['id'];
    }
    @endphp

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
            isset($requestDocument) ? 
            getRouteByRole('document-issuances.update', ['document_issuance' => $requestDocument]) :
            (isRole('clinic_admin') ? route('document-issuances.store') : 
            (isRole('staff') ? route('staff.document-issuances.store') : 
            (isRole('doctor') ? route('doctors.document-issuances.store') : route('document-issuances.store'))))
        }}" method="POST">
            @csrf
            @if(isset($requestDocument))
                @method('PUT')
            @endif

            <div class="form-group mb-5 d-none">
                <label for="document_type">Document Type</label>
                <select name="document_type" id="document_type" class="form-control" required>
                    <option value="medical_certificate" selected>Medical Certificate</option>
                </select>
            </div>

            <div class="flex row">
                <p>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;This is to certify that Mr./Ms.
                    <input type="text" id="document_creator_id" name="document_creator_id" style="width: 400px; text-align: center;" class="border-b border-black d-none" value="{{ isset($requestDocument) ? $requestDocument->document_creator_id : auth()->user()->id }}" readonly required>
                    <input type="text" id="user_id" name="user_id" style="width: 400px; text-align: center;" class="border-b border-black d-none" value="{{ isset($requestDocument) ? $requestDocument->user_id : (request('user_id') ?? ($user->type == 4 ? $user->id : '')) }}" readonly required>
                    <!-- Hidden field to indicate redirect to patient history -->
                    <input type="hidden" name="redirect_to_patient" value="{{ request('user_id') ? '1' : '0' }}">
                    
                    <input type="text" id="name_2" name="name" style="width: 400px; text-align: center;" class="border-b border-black" value="{{ isset($requestDocument) ? $requestDocument->name : ($user->type == 4 ? $user->first_name . ' ' . $user->last_name : '') }}" readonly required>,
                    <input type="text" id="age_2" name="age" style="width: 70px; text-align: center;" class="border-b border-black" value="{{ isset($requestDocument) ? $requestDocument->age : ($user->type == 4 ? \Carbon\Carbon::parse($user->dob)->age : '') }}" readonly required> yrs old,
                    <input type="text" id="gender_2" name="gender" style="width: 70px; text-align: center;" class="border-b border-black" value="{{ isset($requestDocument) ? $requestDocument->gender : ($user->type == 4 ? ($user->gender == 1 ? 'Male' : 'Female') : '') }}" readonly required> a resident of
                </p>
                <p>
                    <input type="text" id="address_2" name="address" style="width: 470px; text-align: center;" class="border-b border-black" value="{{ isset($requestDocument) ? $requestDocument->address : ($user->type == 4 && $patient->address ? $patient->address->full_address : '') }}" {{ $user->type == 4 ? 'readonly' : '' }} required>
                    , was seen and examined at my clinic on
                    @php
                        $examinedOnRaw = old('examined_on', $requestDocument->examined_on ?? '');
                        if ($examinedOnRaw) {
                            if (str_ends_with($examinedOnRaw, '|range')) {
                                $parts = explode('|', $examinedOnRaw);
                                $examinedOnDisplay = \Carbon\Carbon::parse($parts[0])->format('m/d/Y') . ' - ' . \Carbon\Carbon::parse($parts[1])->format('m/d/Y');
                            } elseif (str_ends_with($examinedOnRaw, '|multiple')) {
                                $datesStr = explode('|', $examinedOnRaw)[0];
                                $examinedOnDisplay = implode(', ', array_map(fn($d) => \Carbon\Carbon::parse(trim($d))->format('m/d/Y'), explode(',', $datesStr)));
                            } elseif (str_contains($examinedOnRaw, ',')) {
                                $examinedOnDisplay = implode(', ', array_map(fn($d) => \Carbon\Carbon::parse(trim($d))->format('m/d/Y'), explode(',', $examinedOnRaw)));
                            } else {
                                $examinedOnDisplay = \Carbon\Carbon::parse($examinedOnRaw)->format('m/d/Y');
                            }
                        } else {
                            $examinedOnDisplay = '';
                        }
                    @endphp
                    <input type="text" id="examined_on_display" name="examined_on_display" style="width: 300px; text-align: center;" class="border-b border-black" placeholder="Click to select date(s)" value="{{ $examinedOnDisplay }}" readonly required>
                    <input type="hidden" id="examined_on" name="examined_on" value="{{ $examinedOnRaw }}">
                    <button type="button" id="open_date_selector" class="btn btn-sm btn-primary ml-2" style="padding: 2px 8px; font-size: 12px;">
                        <i class="fas fa-calendar-alt"></i> Select Dates
                    </button>
                    with the following
                <p class="font-semibold">complaints/diagnosis:</p>
                <div class="border border-gray-300 p-2 h-28 mb-4">
                    <div class="col-span-3">
                        <textarea id="complaints_diagnosis" name="complaints_diagnosis" class="w-full border-black" rows="4" required>{{ isset($requestDocument) ? trim($requestDocument->complaints_diagnosis) : '' }}</textarea>
                    </div>
                </div>
                </p>
            </div>

            <div class="grid grid-cols-6 grid-rows-1 gap-7 mb-2">
                @php
                    $bp = isset($requestDocument) ? explode('/', $requestDocument->vital_signs_bp) : ['', ''];
                @endphp
                <div>
                    <p class="font-semibold">BP<span class="text-red-500">*</span>: <input type="text" id="vital_signs_bp_2" name="vital_signs_bp_2" style="width: 30px; text-align: center;" class="border-b border-black" value="{{ old('vital_signs_bp_2', $bp[0] ?? '') }}" required> / <input type="text" id="vital_signs_bp_22" name="vital_signs_bp_22" style="width: 30px; text-align: center;" class="border-b border-black" value="{{ old('vital_signs_bp_22', $bp[1] ?? '') }}" required></p>
                </div>
                <div>
                    <p class="font-semibold">P<span class="text-red-500">*</span>: <input type="text" id="vital_signs_pr_2" name="vital_signs_pr_2" style="width: 50px; text-align: center;" class="border-b border-black" value="{{ isset($requestDocument) ? $requestDocument->vital_signs_pr : '' }}" required></p>
                </div>
                <div>
                    <p class="font-semibold">R<span class="text-red-500">*</span>: <input type="text" id="vital_signs_rr_2" name="vital_signs_rr_2" style="width: 50px; text-align: center;" class="border-b border-black" value="{{ isset($requestDocument) ? $requestDocument->vital_signs_rr : '' }}" required></p>
                </div>
                <div>
                    <p class="font-semibold">T<span class="text-red-500">*</span>: <input type="text" id="vital_signs_temp_2" name="vital_signs_temp_2" style="width: 50px; text-align: center;" class="border-b border-black" value="{{ isset($requestDocument) ? $requestDocument->vital_signs_temp : '' }}" required></p>
                </div>
                <div>
                    <p class="font-semibold">Ht<span class="text-red-500">*</span>: <input type="text" id="vital_signs_height_2" name="vital_signs_height_2" style="width: 50px; text-align: center;" class="border-b border-black" value="{{ isset($requestDocument) ? $requestDocument->vital_signs_height : '' }}" required></p>
                </div>
                <div>
                    <p class="font-semibold">Wt<span class="text-red-500">*</span>: <input type="text" id="vital_signs_weight_2" name="vital_signs_weight_2" style="width: 50px; text-align: center;" class="border-b border-black" value="{{ isset($requestDocument) ? $requestDocument->vital_signs_weight : '' }}" required></p>
                </div>
            </div>

            <p class="font-semibold">Remark/s:</p>
            <div class="border border-gray-300 p-2 h-28 mb-4">
                <div class="col-span-3">
                    <textarea id="medical_cert_remarks" name="medical_cert_remarks" class="w-full border-black" rows="4" required>{{ isset($requestDocument) ? trim($requestDocument->medical_cert_remarks) : '' }}</textarea>
                </div>
            </div>

            <p class="text-sm text-black">Note: Please check the original copy of med cert before accepting the photocopied med cert. This medical certificate is <span class="font-bold underline">not to be used</span> outside school purposes or medico-legal purposes.</p>

            <p class="text-sm">This certificate is issued upon the request of <input type="text" id="request_of" name="request_of" style="width: 350px; text-align: center;" class="border-b border-black" value="{{ old('request_of', isset($requestDocument) ? $requestDocument->request_of : ($user->type == 4 ? $user->first_name . ' ' . $user->last_name : '')) }}" required> for your reference.</p>

            <div class="text-right mt-4 mr-5">
                @if($doctorOptions->count() > 1)
                <div class="inline-block text-left mb-2" style="min-width: 260px;">
                    <label for="medical_cert_doctor_id" class="block text-xs font-semibold">Attending Doctor<span class="text-red-500">*</span></label>
                    <select id="medical_cert_doctor_id" name="doctor_user_id" class="w-full border-b border-black" required>
                        <option value="" disabled {{ $selectedDoctorId ? '' : 'selected' }}>Select Doctor</option>
                        @foreach($doctorOptions as $doctorOption)
                        <option
                            value="{{ $doctorOption['id'] }}"
                            data-name="{{ $doctorOption['name'] }}"
                            data-lic="{{ $doctorOption['lic_no'] }}"
                            data-ptr="{{ $doctorOption['ptr_no'] }}"
                            {{ (string) $selectedDoctorId === (string) $doctorOption['id'] ? 'selected' : '' }}>
                            {{ $doctorOption['name'] }}
                        </option>
                        @endforeach
                    </select>
                </div>
                <p class="font-semibold" id="doctor_display_name"></p>
                @else
                <input
                    type="hidden"
                    id="medical_cert_doctor_id"
                    name="doctor_user_id"
                    value="{{ $singleDoctor['id'] ?? '' }}"
                    data-name="{{ $singleDoctor['name'] ?? '' }}"
                    data-lic="{{ $singleDoctor['lic_no'] ?? '' }}"
                    data-ptr="{{ $singleDoctor['ptr_no'] ?? '' }}">
                <p class="font-semibold" id="doctor_display_name">{{ $singleDoctor ? 'Dr. ' . $singleDoctor['name'] : 'Doctor' }}</p>
                @endif
                <p class="text-xs">Lic #: <input type="text" id="doc_lic_no" name="doc_lic_no" style="width: 110px; text-align: center;" class="border-b border-black" value="{{ old('doc_lic_no', isset($requestDocument) ? $requestDocument->doc_lic_no : ($singleDoctor['lic_no'] ?? '')) }}" required></p>
                <p class=" text-xs">PTR #: <input type="text" id="doc_prt_no" name="doc_prt_no" style="width: 105px; text-align: center;" class="border-b border-black" value="{{ old('doc_prt_no', isset($requestDocument) ? $requestDocument->doc_prt_no : ($singleDoctor['ptr_no'] ?? '')) }}" required></p>
            </div>

            <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded mt-4 text-center">
                {{ isset($requestDocument) ? 'Update' : 'Submit' }}
            </button>
        </form>
    </div>

</div>

<!-- Date Selector Modal -->
@if(!isset($requestDocument))
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
@endif

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
        // Shared logic with Excuse Slip
        @if(!isset($requestDocument))
            // Initialize with today's date if creating
            const todayStr = new Date().toLocaleDateString('en-US', {
                year: 'numeric',
                month: '2-digit',
                day: '2-digit'
            });
            const examinedOnDisplay = document.getElementById('examined_on_display');
            if (examinedOnDisplay && !examinedOnDisplay.value) {
                examinedOnDisplay.value = todayStr;
                document.getElementById('examined_on').value = '{{ date("Y-m-d") }}';
            }
        @endif

        const userSearchInput = document.getElementById('user_search');
        const userSearchResults = document.getElementById('user_search_results');
        const searchRoute = '{{ getRouteByRole("document-issuances.search-users") }}';
        const getLastMedicalCertificateRoute = '{{ getRouteByRole("document-issuances.get-last-medical-certificate") }}';

        const setFieldValue = (fieldId, value) => {
            const field = document.getElementById(fieldId);
            if (field && value !== null && value !== undefined && value !== '') {
                field.value = value;
            }
        };

        const setBloodPressureFields = (value) => {
            if (!value) return;
            const parts = String(value).split('/');
            setFieldValue('vital_signs_bp_2', (parts[0] || '').trim());
            setFieldValue('vital_signs_bp_22', (parts[1] || '').trim());
        };

        const loadLastData = async (userId, fallbackRequestOf = '') => {
            if (!userId) return;
            try {
                const response = await fetch(`${getLastMedicalCertificateRoute}?user_id=${encodeURIComponent(userId)}`);
                const result = await response.json();
                if (!result.success) {
                    if (fallbackRequestOf) setFieldValue('request_of', fallbackRequestOf);
                    return;
                }
                const data = result.data || {};
                setFieldValue('complaints_diagnosis', data.complaints_diagnosis);
                setBloodPressureFields(data.vital_signs_bp);
                setFieldValue('vital_signs_pr_2', data.vital_signs_pr);
                setFieldValue('vital_signs_rr_2', data.vital_signs_rr);
                setFieldValue('vital_signs_temp_2', data.vital_signs_temp);
                setFieldValue('vital_signs_height_2', data.vital_signs_height);
                setFieldValue('vital_signs_weight_2', data.vital_signs_weight);
                setFieldValue('medical_cert_remarks', data.medical_cert_remarks);
                if (data.request_of) setFieldValue('request_of', data.request_of);
                else if (fallbackRequestOf) setFieldValue('request_of', fallbackRequestOf);
            } catch (error) {
                console.error('Error loading latest data:', error);
            }
        };

        if (userSearchInput) {
            userSearchInput.addEventListener('input', function() {
                const query = this.value;
                if (query.length > 1) {
                    fetch(`${searchRoute}?query=${encodeURIComponent(query)}`)
                        .then(response => response.json())
                        .then(data => {
                            userSearchResults.innerHTML = '';
                            userSearchResults.classList.remove('hidden');
                            if (data.length === 0) {
                                userSearchResults.innerHTML = '<div class="p-2 text-gray-500">No patients found.</div>';
                                return;
                            }
                            data.forEach(patient => {
                                const option = document.createElement('div');
                                option.className = 'p-2 cursor-pointer hover:bg-gray-200';
                                option.textContent = `${patient.user.first_name} ${patient.user.last_name}`;
                                option.addEventListener('click', async function() {
                                    const fullName = `${patient.user.first_name} ${patient.user.last_name}`;
                                    document.getElementById('user_id').value = patient.user.id;
                                    document.getElementById('name_2').value = fullName;
                                    document.getElementById('request_of').value = fullName;
                                    document.getElementById('age_2').value = calculateAge(patient.user.dob);
                                    document.getElementById('gender_2').value = patient.user.gender === 1 ? 'Male' : 'Female';
                                    if (patient.address) {
                                        document.getElementById('address_2').value = patient.address.full_address || patient.address.address1 || '';
                                    }
                                    await loadLastData(patient.user.id, fullName);
                                    userSearchResults.classList.add('hidden');
                                });
                                userSearchResults.appendChild(option);
                            });
                        });
                } else {
                    userSearchResults.classList.add('hidden');
                }
            });
        }

        function calculateAge(dob) {
            if (!dob) return '';
            const birthDate = new Date(dob);
            const today = new Date();
            let age = today.getFullYear() - birthDate.getFullYear();
            const m = today.getMonth() - birthDate.getMonth();
            if (m < 0 || (m === 0 && today.getDate() < birthDate.getDate())) age--;
            return age;
        }

        const doctorSelector = document.getElementById('medical_cert_doctor_id');
        const doctorDisplayName = document.getElementById('doctor_display_name');
        const doctorLicenseInput = document.getElementById('doc_lic_no');
        const doctorPtrInput = document.getElementById('doc_prt_no');

        const syncDoctorDetails = () => {
            if (!doctorSelector) return;
            let name = '', lic = '', ptr = '';
            if (doctorSelector.tagName === 'SELECT') {
                const opt = doctorSelector.options[doctorSelector.selectedIndex];
                if (!opt || !opt.value) return;
                name = opt.dataset.name || opt.textContent.trim();
                lic = opt.dataset.lic || '';
                ptr = opt.dataset.ptr || '';
            } else {
                name = doctorSelector.dataset.name || '';
                lic = doctorSelector.dataset.lic || '';
                ptr = doctorSelector.dataset.ptr || '';
            }
            if (doctorDisplayName) doctorDisplayName.textContent = name ? `Dr. ${name}` : 'Doctor';
            if (doctorLicenseInput && lic) doctorLicenseInput.value = lic;
            if (doctorPtrInput && ptr) doctorPtrInput.value = ptr;
        };

        if (doctorSelector) {
            if (doctorSelector.tagName === 'SELECT') doctorSelector.addEventListener('change', syncDoctorDetails);
            syncDoctorDetails();
        }

        // Initialize Date Selector if exists
        const dateModalEl = document.getElementById('date_selector_modal');
        if (dateModalEl) {
            const dateSelectorModal = new bootstrap.Modal(dateModalEl);
            const openBtn = document.getElementById('open_date_selector');
            const applyBtn = document.getElementById('apply_dates_btn');
            let selectedDatesArray = [];
            let currentDateType = 'single';

            openBtn.addEventListener('click', () => dateSelectorModal.show());

            document.querySelectorAll('input[name="date_type"]').forEach(radio => {
                radio.addEventListener('change', function() {
                    currentDateType = this.value;
                    document.getElementById('single_date_section').style.display = currentDateType === 'single' ? 'block' : 'none';
                    document.getElementById('date_range_section').style.display = currentDateType === 'range' ? 'block' : 'none';
                    document.getElementById('multiple_dates_section').style.display = currentDateType === 'multiple' ? 'block' : 'none';
                    updatePreview();
                });
            });

            const updatePreview = () => {
                const preview = document.getElementById('date_preview');
                let text = '';
                if (currentDateType === 'single') {
                    const d = document.getElementById('single_date_input').value;
                    text = d ? formatDateDisplay(d) : 'No date selected';
                } else if (currentDateType === 'range') {
                    const s = document.getElementById('start_date_input').value;
                    const e = document.getElementById('end_date_input').value;
                    text = s && e ? `${formatDateDisplay(s)} - ${formatDateDisplay(e)}` : (s ? `${formatDateDisplay(s)} - ...` : 'No range');
                } else {
                    text = selectedDatesArray.length ? selectedDatesArray.map(formatDateDisplay).join(', ') : 'No dates';
                }
                preview.textContent = text;
            };

            const formatDateDisplay = (ds) => {
                const d = new Date(ds + 'T00:00:00');
                return d.toLocaleDateString('en-US', { month: '2-digit', day: '2-digit', year: 'numeric' });
            };

            document.getElementById('add_date_btn')?.addEventListener('click', () => {
                const val = document.getElementById('add_date_input').value;
                if (val && !selectedDatesArray.includes(val)) {
                    selectedDatesArray.push(val);
                    selectedDatesArray.sort();
                    renderDates();
                    updatePreview();
                }
            });

            const renderDates = () => {
                const cont = document.getElementById('selected_dates_list');
                cont.innerHTML = selectedDatesArray.map((d, i) => `
                    <span class="selected-date-item">${formatDateDisplay(d)} <span class="remove-date" data-index="${i}">&times;</span></span>
                `).join('') || '<p class="text-muted text-center">None</p>';
                cont.querySelectorAll('.remove-date').forEach(b => b.addEventListener('click', function() {
                    selectedDatesArray.splice(this.dataset.index, 1);
                    renderDates();
                    updatePreview();
                }));
            };

            applyBtn.addEventListener('click', () => {
                let disp = '', stor = '';
                if (currentDateType === 'single') {
                    const d = document.getElementById('single_date_input').value;
                    if (!d) return alert('Select date');
                    disp = formatDateDisplay(d); stor = d;
                } else if (currentDateType === 'range') {
                    const s = document.getElementById('start_date_input').value;
                    const e = document.getElementById('end_date_input').value;
                    if (!s || !e) return alert('Select range');
                    disp = `${formatDateDisplay(s)} - ${formatDateDisplay(e)}`; stor = `${s}|${e}|range`;
                } else {
                    if (!selectedDatesArray.length) return alert('Add dates');
                    disp = selectedDatesArray.map(formatDateDisplay).join(', '); stor = selectedDatesArray.join(',') + '|multiple';
                }
                document.getElementById('examined_on_display').value = disp;
                document.getElementById('examined_on').value = stor;
                dateSelectorModal.hide();
            });
        }
    });
</script>