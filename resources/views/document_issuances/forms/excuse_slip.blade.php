<div>
    @if($user->type != 4 && !request('user_id'))
    <!-- Patient Search Section -->
    <div class="mx-auto mb-20" style="width: 10in; min-width: 10in;">
        <label for="user_search" class="block text-xs font-bold text-gray-500 mb-3 flex items-center uppercase tracking-widest">
            <i class="fas fa-search mr-2 text-blue-600"></i> SEARCH PATIENT (STUDENT/STAFF)
        </label>
        <div class="relative group">
            <input type="text" id="user_search"
                class="w-full bg-white border-2 border-gray-200 rounded-xl shadow-sm focus:border-blue-500 focus:ring-4 focus:ring-blue-50/50 text-lg py-4 px-6 transition-all outline-none font-medium"
                placeholder="Type name here to search and autofill..." autocomplete="off">
            <div id="user_search_results" class="absolute top-full left-0 right-0 z-[100] mt-4 bg-white border-2 border-gray-100 rounded-xl shadow-[0_20px_50px_rgba(0,0,0,0.3)] max-h-60 overflow-y-auto hidden pb-2" style="background-color: white !important;"></div>
        </div>
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
    $selectedDoctorId = old('doctor_user_id', $requestDocument->doctor_user_id ?? ($singleDoctor['id'] ?? ''));
    @endphp

    <!-- Main Excuse Slip Form -->
    <div class="mx-auto bg-white shadow-2xl rounded-sm p-12 mb-10 border border-gray-200" style="width: 10in; min-width: 10in; min-height: 11in; font-family: 'Arial', sans-serif; color: #000; box-sizing: border-box; position: relative;">
        <!-- Header -->
        <table class="w-full mb-10" style="border-collapse: collapse; table-layout: auto;">
            <tr>
                <td style="width: 100px; text-align: left; vertical-align: middle;">
                    <img src="{{ asset('assets/image/norsu_logo.png') }}" alt="Logo" style="width: 80px; height: 80px; object-fit: contain;">
                </td>
                <td style="text-align: center; vertical-align: middle;">
                    <h1 class="text-2xl font-bold uppercase tracking-wider leading-tight" style="margin: 0;">Negros Oriental State University</h1>
                    <h2 class="text-xs font-medium" style="margin: 2px 0;">University Medical Clinic, CNPAHS Bldg., Kagawasan Ave., Dumaguete City</h2>
                    <p class="text-[10px] text-gray-600" style="margin: 0;">Tel #: 225-9400, then Local # 188, 09263829484</p>
                </td>
                <td style="width: 100px; text-align: right; vertical-align: middle;">
                    <img src="{{ asset('assets/image/norsu_clinic_logo.png') }}" alt="Logo" style="width: 80px; height: 80px; object-fit: contain;">
                </td>
            </tr>
        </table>

        <div class="text-center mb-10 border-t border-b border-black py-4">
            <h3 class="text-3xl font-black tracking-widest" style="margin: 0;">STUDENT EXCUSE SLIP</h3>
        </div>

        <form action="{{ 
            isRole('clinic_admin') ? (isset($requestDocument) ? route('document-issuances.update', $requestDocument->id) : route('document-issuances.store')) : 
            (isRole('staff') ? (isset($requestDocument) ? route('staff.document-issuances.update', $requestDocument->id) : route('staff.document-issuances.store')) : 
            (isRole('doctor') ? (isset($requestDocument) ? route('doctors.document-issuances.update', $requestDocument->id) : route('doctors.document-issuances.store')) : 
            (isset($requestDocument) ? route('document-issuances.update', $requestDocument->id) : route('document-issuances.store'))))
        }}" method="POST">
            @csrf
            @if(isset($requestDocument))
            @method('PUT')
            @endif

            <input type="hidden" name="document_type" value="excuse_slip">
            <input type="hidden" id="user_id" name="user_id" value="{{ isset($requestDocument) ? $requestDocument->user_id : (request('user_id') ?? ($user->type == 4 ? $user->id : '')) }}">
            <input type="hidden" name="redirect_to_patient" value="{{ request('user_id') ? '1' : '0' }}">
            <input type="hidden" id="document_creator_id" name="document_creator_id" value="{{ isset($requestDocument) ? $requestDocument->document_creator_id : auth()->user()->id }}">

            <table class="w-full mb-10" style="border-collapse: collapse; table-layout: auto;">
                <tr>
                    <!-- Left Side: Student Information -->
                    <td style="width: 70%; vertical-align: top; padding-right: 40px;">
                        <div class="space-y-8">
                            <div class="flex items-baseline border-b-2 border-black pb-1 gap-10">
                                <label class="font-bold text-[13px] whitespace-nowrap uppercase tracking-tighter">DATE FILED:</label>
                                <span class="text-base font-bold">{{ isset($requestDocument) ? $requestDocument->created_at->format('M d, Y') : date('M d, Y') }}</span>
                            </div>

                            <div class="flex items-baseline border-b-2 border-black focus-within:border-blue-500 pb-1 px-1 hover:bg-gray-50 transition-all gap-10">
                                <label class="font-bold text-[13px] whitespace-nowrap uppercase tracking-tighter">STUDENT'S NAME:</label>
                                <input type="text" id="name_2" name="name" class="flex-1 bg-transparent border-none focus:ring-0 text-base font-bold uppercase p-0 h-7" value="{{ isset($requestDocument) ? $requestDocument->name : ($user->type == 4 ? $user->first_name . ' ' . $user->last_name : '') }}" placeholder="...">
                            </div>

                            <div class="flex items-baseline border-b-2 border-black focus-within:border-blue-500 pb-1 px-1 hover:bg-gray-50 transition-all gap-10">
                                <label class="font-bold text-[13px] whitespace-nowrap uppercase tracking-tighter">SECTION:</label>
                                <input type="text" id="section_display" class="flex-1 bg-transparent border-none focus:ring-0 text-base p-0 h-7 font-bold" value="{{ isset($requestDocument) ? $requestDocument->course . ' ' . $requestDocument->year_level : ($user->type == 4 && isset($patient) ? $patient->course . ' ' . $patient->year_level : '') }}" placeholder="...">
                                <input type="hidden" id="course" name="course" value="{{ isset($requestDocument) ? $requestDocument->course : ($user->type == 4 && isset($patient) ? $patient->course : '') }}">
                                <input type="hidden" id="year_level" name="year_level" value="{{ isset($requestDocument) ? $requestDocument->year_level : ($user->type == 4 && isset($patient) ? $patient->year_level : '') }}">
                            </div>

                            <div class="flex items-baseline border-b-2 border-black focus-within:border-blue-500 pb-1 px-1 hover:bg-gray-50 transition-all group gap-10">
                                <label class="font-bold text-[13px] whitespace-nowrap uppercase tracking-tighter">DATE/S ABSENT:</label>
                                <input type="text" id="examined_on_display" class="flex-1 bg-transparent border-none focus:ring-0 text-base cursor-pointer p-0 h-7 font-bold" placeholder="CLICK CALENDAR..." value="{{ isset($requestDocument) && $requestDocument->examined_on ? formatExaminedOnForPDF($requestDocument->examined_on) : '' }}" readonly required>
                                <input type="hidden" id="examined_on" name="examined_on" value="{{ isset($requestDocument) ? $requestDocument->examined_on : '' }}">
                                <button type="button" id="open_date_selector" class="text-blue-600 hover:text-blue-800 ml-1"><i class="fas fa-calendar-alt text-lg"></i></button>
                            </div>

                            <div class="pt-4">
                                <label class="font-bold text-[13px] block mb-3 uppercase tracking-tighter">REASON (COMPLAINTS/DIAGNOSIS):</label>
                                <textarea id="complaints_diagnosis" name="complaints_diagnosis" rows="3" class="w-full border-2 border-black p-4 text-base focus:ring-0 leading-relaxed overflow-hidden" placeholder="TYPE REASON HERE..." oninput="this.style.height = 'auto'; this.style.height = this.scrollHeight + 'px';" required>{{ isset($requestDocument) ? $requestDocument->complaints_diagnosis : '' }}</textarea>
                            </div>
                        </div>
                    </td>

                    <!-- Right Side: Subject Table -->
                    <td style="width: 45%; vertical-align: top;">
                        <table class="w-full border-collapse border-2 border-black" style="font-size: 10px;">
                            <thead>
                                <tr class="bg-gray-100">
                                    <th class="border border-black p-2 text-center font-bold" style="width: 50%;">SUBJECT</th>
                                    <th class="border border-black p-2 text-center font-bold" style="width: 50%;">TEACHER</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                $subjects = isset($requestDocument) && isset($requestDocument->subjects) ? (is_array($requestDocument->subjects) ? $requestDocument->subjects : json_decode($requestDocument->subjects, true)) : array_fill(0, 10, ['subject' => '', 'teacher' => '']);
                                @endphp
                                @for($i=0; $i<10; $i++)
                                    <tr>
                                    <td class="border border-black h-9 p-0">
                                        <input type="text" name="subjects[{{ $i }}][subject]" class="w-full h-full bg-transparent border-none focus:ring-0 text-[12px] px-2 font-medium" value="{{ $subjects[$i]['subject'] ?? '' }}" placeholder="...">
                                    </td>
                                    <td class="border border-black h-9 p-0">
                                        <input type="text" name="subjects[{{ $i }}][teacher]" class="w-full h-full bg-transparent border-none focus:ring-0 text-[12px] px-2 font-medium" value="{{ $subjects[$i]['teacher'] ?? '' }}" placeholder="...">
                                    </td>
                </tr>
                @endfor
                </tbody>
            </table>
            <p class="text-[9px] italic mt-2 text-gray-500 text-center uppercase tracking-widest">To be filled by Subject Teachers upon return</p>
            </td>
            </tr>
            </table>
            <!-- Parent Signature Area -->
            <div class="mt-12 bg-gray-50/50 p-6 rounded-xl border border-dashed border-gray-200">
                <div class="flex items-baseline border-b-2 border-black/10 pb-2 mb-8">
                    <label class="font-bold text-[11px] mr-2 whitespace-nowrap text-gray-500 uppercase tracking-tighter">PARENT'S OR GUARDIAN'S SIGNATURE:</label>
                    <div class="flex-1"></div>
                </div>
            </div>


            <!-- Remarks -->
            <div class="mt-12 border-t border-black pt-4">
                <label class="font-bold text-sm block mb-2">CLINIC REMARKS / RECOMMENDATION:</label>
                <textarea id="medical_cert_remarks" name="medical_cert_remarks" rows="3" class="w-full border-none p-0 text-sm focus:ring-0 leading-relaxed overflow-hidden" placeholder="e.g. Advised to rest for 3 days. Fit to return on..." oninput="this.style.height = 'auto'; this.style.height = this.scrollHeight + 'px';" required>{{ isset($requestDocument) ? $requestDocument->medical_cert_remarks : '' }}</textarea>
            </div>

            <!-- Vital Signs (Compact Row) -->
            <div class="mt-8 pt-4 border-t border-gray-200">
                <table class="w-full" style="border-collapse: collapse; table-layout: fixed; font-size: 9px;">
                    <tr>
                        <td style="width: 16%;">
                            <label class="font-bold">BP:</label>
                            <div class="inline-flex items-center">
                                <input type="text" name="vital_signs_bp_2" class="w-6 bg-transparent border-none p-0 text-[9px] text-center focus:ring-0" value="{{ isset($requestDocument) ? explode('/', $requestDocument->vital_signs_bp)[0] : '' }}" required>
                                <span>/</span>
                                <input type="text" name="vital_signs_bp_22" class="w-6 bg-transparent border-none p-0 text-[9px] text-center focus:ring-0" value="{{ isset($requestDocument) ? (explode('/', $requestDocument->vital_signs_bp)[1] ?? '') : '' }}" required>
                            </div>
                        </td>
                        <td style="width: 16%;">
                            <label class="font-bold">P:</label>
                            <input type="text" name="vital_signs_pr_2" class="w-12 bg-transparent border-none p-0 text-[9px] focus:ring-0" value="{{ isset($requestDocument) ? $requestDocument->vital_signs_pr : '' }}" required>
                        </td>
                        <td style="width: 16%;">
                            <label class="font-bold">R:</label>
                            <input type="text" name="vital_signs_rr_2" class="w-12 bg-transparent border-none p-0 text-[9px] focus:ring-0" value="{{ isset($requestDocument) ? $requestDocument->vital_signs_rr : '' }}" required>
                        </td>
                        <td style="width: 16%;">
                            <label class="font-bold">T:</label>
                            <input type="text" name="vital_signs_temp_2" class="w-12 bg-transparent border-none p-0 text-[9px] focus:ring-0" value="{{ isset($requestDocument) ? $requestDocument->vital_signs_temp : '' }}" required>
                        </td>
                        <td style="width: 16%;">
                            <label class="font-bold">Ht:</label>
                            <input type="text" name="vital_signs_height_2" class="w-12 bg-transparent border-none p-0 text-[9px] focus:ring-0" value="{{ isset($requestDocument) ? $requestDocument->vital_signs_height : '' }}" required>
                        </td>
                        <td style="width: 16%;">
                            <label class="font-bold">Wt:</label>
                            <input type="text" name="vital_signs_weight_2" class="w-12 bg-transparent border-none p-0 text-[9px] focus:ring-0" value="{{ isset($requestDocument) ? $requestDocument->vital_signs_weight : '' }}" required>
                        </td>
                    </tr>
                </table>
            </div>

            <!-- Approvals Section -->
            <table class="w-full mt-12" style="border-collapse: collapse; table-layout: fixed;">
                <tr>
                    <td class="text-center" style="width: 50%; vertical-align: top;">
                        <div style="border-top: 2px solid #e5e7eb; padding-top: 12px; margin: 0 40px;">
                            <div class="font-bold text-sm tracking-tighter text-gray-200">-----------------</div>
                            <div class="text-[10px] text-gray-400 font-bold uppercase mt-1 tracking-widest">PROGRAM CHAIR</div>
                        </div>
                    </td>
                    <td class="text-center" style="width: 50%; vertical-align: top;">
                        <div style="border-top: 2px solid #e5e7eb; padding-top: 12px; margin: 0 40px;">
                            <div class="font-bold text-sm tracking-tighter text-blue-900">
                                @if($doctorOptions->count() > 1)
                                <select id="medical_cert_doctor_id" name="doctor_user_id" class="w-full bg-transparent border-none focus:ring-0 text-center text-sm font-bold p-0 h-5" style="appearance: none;" required>
                                    <option value="" disabled {{ $selectedDoctorId ? '' : 'selected' }}>SELECT DOCTOR</option>
                                    @foreach($doctorOptions as $doctorOption)
                                    <option
                                        value="{{ $doctorOption['id'] }}"
                                        data-name="{{ $doctorOption['name'] }}"
                                        data-lic="{{ $doctorOption['lic_no'] }}"
                                        data-ptr="{{ $doctorOption['ptr_no'] }}"
                                        {{ (string) $selectedDoctorId === (string) $doctorOption['id'] ? 'selected' : '' }}>
                                        DR. {{ strtoupper($doctorOption['name']) }}
                                    </option>
                                    @endforeach
                                </select>
                                @else
                                <input type="hidden" id="medical_cert_doctor_id" name="doctor_user_id" value="{{ $singleDoctor['id'] ?? '' }}">
                                <span>DR. {{ strtoupper($singleDoctor['name'] ?? 'UNIVERSITY PHYSICIAN') }}</span>
                                @endif
                            </div>
                            <div class="text-[10px] text-gray-400 font-bold uppercase mt-1 tracking-widest">UNIVERSITY PHYSICIAN</div>
                            <div class="text-[9px] mt-2 space-y-1 text-gray-500">
                                <p class="flex items-center justify-center">Lic #: <input type="text" id="doc_lic_no" name="doc_lic_no" class="bg-transparent border-b border-gray-100 p-0 focus:ring-0 text-[9px] w-20 text-center ml-1" value="{{ isset($requestDocument) ? $requestDocument->doc_lic_no : ($singleDoctor['lic_no'] ?? '') }}" readonly></p>
                                <p class="flex items-center justify-center">PTR #: <input type="text" id="doc_prt_no" name="doc_prt_no" class="bg-transparent border-b border-gray-100 p-0 focus:ring-0 text-[9px] w-20 text-center ml-1" value="{{ isset($requestDocument) ? $requestDocument->doc_prt_no : ($singleDoctor['ptr_no'] ?? '') }}" readonly></p>
                            </div>
                        </div>
                    </td>
                </tr>
            </table>

            <div class="flex justify-end mt-12 gap-4 no-print">
                <button type="submit" class="px-6 py-2 bg-blue-600 border border-transparent rounded-md text-sm font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 shadow-lg">
                    {{ isset($requestDocument) ? 'Update Excuse Slip' : 'Save & Issue Slip' }}
                </button>
            </div>
        </form>

        <div class="text-right mt-4 text-[8px] text-gray-400">
            NORSU-CLINIC-FORM-02
        </div>
    </div>
</div>

<!-- Date Selector Modal -->
@if(!isset($requestDocument))
<div id="date_selector_modal" class="modal fade" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Select Absence Date(s)</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <!-- Copied logic from med cert for consistency -->
                <div class="mb-4">
                    <label class="form-label font-bold">Select Date Type:</label>
                    <div class="grid grid-cols-3 gap-2">
                        <button type="button" class="btn btn-outline-primary date-type-btn active" data-type="single">Single Date</button>
                        <button type="button" class="btn btn-outline-primary date-type-btn" data-type="range">Date Range</button>
                        <button type="button" class="btn btn-outline-primary date-type-btn" data-type="multiple">Multiple Dates</button>
                    </div>
                </div>

                <div id="single_date_section" class="date-section">
                    <label class="form-label">Select Date:</label>
                    <input type="date" id="single_date_input" class="form-control" max="{{ date('Y-m-d') }}" value="{{ date('Y-m-d') }}">
                </div>

                <div id="range_date_section" class="date-section hidden">
                    <div class="row">
                        <div class="col-md-6"><label class="form-label">From:</label><input type="date" id="start_date_input" class="form-control"></div>
                        <div class="col-md-6"><label class="form-label">To:</label><input type="date" id="end_date_input" class="form-control"></div>
                    </div>
                </div>

                <div id="multiple_date_section" class="date-section hidden">
                    <div class="input-group mb-3">
                        <input type="date" id="add_date_input" class="form-control">
                        <button type="button" id="add_date_btn" class="btn btn-success">Add</button>
                    </div>
                    <div id="selected_dates_list" class="border rounded p-3 min-h-[100px] flex flex-wrap gap-2">
                        <p class="text-gray-400 w-full text-center">No dates added yet</p>
                    </div>
                </div>

                <div class="mt-4 p-4 bg-blue-50 border border-blue-200 rounded">
                    <p class="font-bold text-blue-800">Preview: <span id="date_preview" class="font-normal">Today</span></p>
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

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const userSearchInput = document.getElementById('user_search');
        const userSearchResults = document.getElementById('user_search_results');
        const searchRoute = @json(parse_url(getRouteByRole("document-issuances.search-users"), PHP_URL_PATH) ? : getRouteByRole("document-issuances.search-users"));
        const getLastMedicalCertificateRoute = @json(parse_url(getRouteByRole("document-issuances.get-last-medical-certificate"), PHP_URL_PATH) ? : getRouteByRole("document-issuances.get-last-medical-certificate"));

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
                                userSearchResults.innerHTML = '<div class="p-6 text-gray-400 text-center text-sm italic">No patients found matching your search.</div>';
                                return;
                            }
                            // Patient names/courses are user-entered text: escape before using innerHTML.
                            const escapeHtml = (value) => String(value ?? '').replace(/[&<>"']/g, (ch) => ({
                                '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;'
                            }[ch]));
                            data.forEach(patient => {
                                const option = document.createElement('div');
                                option.className = 'px-6 py-4 hover:bg-blue-50 cursor-pointer flex items-center transition-all border-b border-gray-50 last:border-0 group/item';
                                option.innerHTML = `
                                    <div class="w-8 h-8 rounded-full bg-blue-50 flex items-center justify-center mr-10 group-hover/item:bg-blue-100 transition-colors shrink-0" style="margin-right: 40px !important;">
                                        <i class="fas fa-user text-blue-500 text-[11px]"></i>
                                    </div>
                                    <div class="flex-1 flex items-center overflow-hidden">
                                        <span class="font-medium text-sm text-gray-800 group-hover/item:text-blue-600 transition-colors whitespace-nowrap">${escapeHtml(patient.user.first_name)} ${escapeHtml(patient.user.last_name)}</span>
                                        <span class="mx-3 text-gray-300">|</span>
                                        <div class="flex items-center text-[10px] text-gray-400 uppercase tracking-widest whitespace-nowrap overflow-hidden">
                                            <span class="truncate max-w-[250px]">${patient.user.course ? escapeHtml(patient.user.course.course_name) : 'NO COURSE'}</span>
                                            <span class="mx-2 text-gray-200">•</span>
                                            <span>${patient.user.gender === 1 ? 'Male' : 'Female'}</span>
                                        </div>
                                    </div>
                                    <div class="text-blue-300 opacity-0 group-hover/item:opacity-100 transition-opacity ml-2">
                                        <i class="fas fa-chevron-right text-[10px]"></i>
                                    </div>
                                `;
                                option.addEventListener('click', function() {
                                    const fullName = `${patient.user.first_name} ${patient.user.last_name}`;
                                    const courseName = patient.user.course ? patient.user.course.course_name : '';
                                    const yearLevelName = patient.user.year_level ? patient.user.year_level.year_level_name : '';

                                    document.getElementById('user_id').value = patient.user.id;
                                    document.getElementById('name_2').value = fullName;
                                    document.getElementById('section_display').value = `${courseName} ${yearLevelName}`.trim();
                                    document.getElementById('course').value = courseName;
                                    document.getElementById('year_level').value = yearLevelName;

                                    loadLastData(patient.user.id);
                                    userSearchResults.classList.add('hidden');
                                    userSearchInput.value = fullName;
                                });
                                userSearchResults.appendChild(option);
                            });
                        });
                } else {
                    userSearchResults.classList.add('hidden');
                }
            });

            // Close results when clicking outside
            document.addEventListener('click', (e) => {
                if (!userSearchInput.contains(e.target) && !userSearchResults.contains(e.target)) {
                    userSearchResults.classList.add('hidden');
                }
            });
        }

        async function loadLastData(userId) {
            try {
                const response = await fetch(`${getLastMedicalCertificateRoute}?user_id=${userId}`);
                const result = await response.json();
                if (result.success && result.data) {
                    const d = result.data;
                    document.getElementById('complaints_diagnosis').value = d.complaints_diagnosis || '';
                    document.getElementById('medical_cert_remarks').value = d.medical_cert_remarks || '';
                    if (d.vital_signs_bp) {
                        const parts = d.vital_signs_bp.split('/');
                        document.getElementsByName('vital_signs_bp_2')[0].value = parts[0] || '';
                        document.getElementsByName('vital_signs_bp_22')[0].value = parts[1] || '';
                    }
                    document.getElementsByName('vital_signs_pr_2')[0].value = d.vital_signs_pr || '';
                    document.getElementsByName('vital_signs_rr_2')[0].value = d.vital_signs_rr || '';
                    document.getElementsByName('vital_signs_temp_2')[0].value = d.vital_signs_temp || '';
                    document.getElementsByName('vital_signs_height_2')[0].value = d.vital_signs_height || '';
                    document.getElementsByName('vital_signs_weight_2')[0].value = d.vital_signs_weight || '';
                }
            } catch (e) {
                console.error('Failed to load last data', e);
            }
        }

        // Date Selector Modal Logic
        const dateModalEl = document.getElementById('date_selector_modal');
        if (dateModalEl) {
            const dateModal = new bootstrap.Modal(dateModalEl);
            const openBtn = document.getElementById('open_date_selector');
            const applyBtn = document.getElementById('apply_dates_btn');
            const typeBtns = document.querySelectorAll('.date-type-btn');
            let selectedType = 'single';
            let multipleDates = [];

            openBtn.addEventListener('click', () => dateModal.show());

            typeBtns.forEach(btn => {
                btn.addEventListener('click', function() {
                    typeBtns.forEach(b => b.classList.remove('active', 'btn-primary'));
                    typeBtns.forEach(b => b.classList.add('btn-outline-primary'));
                    this.classList.remove('btn-outline-primary');
                    this.classList.add('active', 'btn-primary');

                    selectedType = this.dataset.type;
                    document.querySelectorAll('.date-section').forEach(s => s.classList.add('hidden'));
                    document.getElementById(`${selectedType}_date_section`).classList.remove('hidden');
                    updatePreview();
                });
            });

            function updatePreview() {
                const preview = document.getElementById('date_preview');
                if (selectedType === 'single') {
                    const d = document.getElementById('single_date_input').value;
                    preview.textContent = d ? formatDate(d) : 'Select a date';
                } else if (selectedType === 'range') {
                    const s = document.getElementById('start_date_input').value;
                    const e = document.getElementById('end_date_input').value;
                    preview.textContent = s && e ? `${formatDate(s)} to ${formatDate(e)}` : 'Select range...';
                } else {
                    preview.textContent = multipleDates.length ? multipleDates.map(formatDate).join(', ') : 'Add dates...';
                }
            }

            function formatDate(ds) {
                return new Date(ds).toLocaleDateString('en-US', {
                    month: 'short',
                    day: 'numeric',
                    year: 'numeric'
                });
            }

            document.getElementById('add_date_btn').addEventListener('click', () => {
                const d = document.getElementById('add_date_input').value;
                if (d && !multipleDates.includes(d)) {
                    multipleDates.push(d);
                    multipleDates.sort();
                    renderMultiple();
                    updatePreview();
                }
            });

            function renderMultiple() {
                const list = document.getElementById('selected_dates_list');
                list.innerHTML = multipleDates.map((d, i) => `
                    <span class="bg-blue-100 text-blue-700 px-2 py-1 rounded text-xs flex items-center">
                        ${formatDate(d)}
                        <i class="fas fa-times ml-2 cursor-pointer remove-date" data-index="${i}"></i>
                    </span>
                `).join('') || '<p class="text-gray-400 w-full text-center">No dates added yet</p>';

                list.querySelectorAll('.remove-date').forEach(icon => {
                    icon.addEventListener('click', function() {
                        multipleDates.splice(this.dataset.index, 1);
                        renderMultiple();
                        updatePreview();
                    });
                });
            }

            applyBtn.addEventListener('click', () => {
                let disp = '',
                    value = '';
                if (selectedType === 'single') {
                    const d = document.getElementById('single_date_input').value;
                    if (!d) return;
                    disp = formatDate(d);
                    value = d;
                } else if (selectedType === 'range') {
                    const s = document.getElementById('start_date_input').value;
                    const e = document.getElementById('end_date_input').value;
                    if (!s || !e) return;
                    disp = `${formatDate(s)} - ${formatDate(e)}`;
                    value = `${s}|${e}|range`;
                } else {
                    if (!multipleDates.length) return;
                    disp = multipleDates.map(formatDate).join(', ');
                    value = multipleDates.join(',') + '|multiple';
                }
                document.getElementById('examined_on_display').value = disp;
                document.getElementById('examined_on').value = value;
                dateModal.hide();
            });

            // Update preview on inputs
            document.getElementById('single_date_input').addEventListener('input', updatePreview);
            document.getElementById('start_date_input').addEventListener('input', updatePreview);
            document.getElementById('end_date_input').addEventListener('input', updatePreview);

            // Trigger initial resize for textareas
            document.querySelectorAll('textarea').forEach(textarea => {
                textarea.style.height = 'auto';
                textarea.style.height = textarea.scrollHeight + 'px';
            });
        }

        // Doctor Sync
        const doctorSelect = document.getElementById('medical_cert_doctor_id');
        if (doctorSelect) {
            doctorSelect.addEventListener('change', function() {
                const opt = this.options[this.selectedIndex];
                document.getElementById('doc_lic_no').value = opt.dataset.lic || '';
                document.getElementById('doc_prt_no').value = opt.dataset.ptr || '';
            });
        }

        // Handle manual input in Section field
        const sectionInput = document.getElementById('section_display');
        const courseHidden = document.getElementById('course');
        if (sectionInput && courseHidden) {
            sectionInput.addEventListener('input', function() {
                courseHidden.value = this.value;
                document.getElementById('year_level').value = '';
            });
        }

        // Handle manual input in Name field
        const nameInput = document.getElementById('name_2');
        const userIdHidden = document.getElementById('user_id');
        if (nameInput && userIdHidden) {
            nameInput.addEventListener('input', function() {
                if (this.value.trim() === '') {
                    userIdHidden.value = '';
                }
            });
        }
    });
</script>