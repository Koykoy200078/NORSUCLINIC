<div>
    @if($user->type != 3)
    <div class="mb-10">
        <label class="block text-xs" for="user_search">Search User</label>
        <input type="text" id="user_search" class="w-full border-b border-black" placeholder="Search by name" autocomplete="off">
        <div id="user_search_results" class="absolute bg-white border border-gray-300 w-full hidden z-10"></div>
    </div>
    @endif
    <form action="{{ 
        isRole('clinic_admin') ? route('request-documents.store') : 
        (isRole('staff') ? route('staff.request-documents.store') : 
        (isRole('doctor') ? route('doctors.request-documents.store') : route('request-documents.store')))
    }}" method="POST" enctype="multipart/form-data">
        @csrf
        <div class="form-group mb-5 d-none">
            <label for="document_type">Document Type</label>
            <select name="document_type" id="document_type" class="form-control" required>
                <option value="consultation_form" selected>Consultation Form</option>
            </select>
        </div>

        <!-- Hidden field to trigger redirect to patient history -->
        <input type="hidden" name="redirect_to_patient" value="{{ request('user_id') ? '1' : '0' }}">

        <div class="grid grid-cols-4 gap-2 pb-2">
            <div class="col-span-1 d-none">
                <label class="block text-xs" for="name">ID<span class="text-red-500">*</span></label>
                <input type="text" id="document_creator_id" name="document_creator_id" style="width: 400px; text-align: center;" class="border-b border-black" value="{{ auth()->user()->id }}" readonly required>
                <input type="text" id="user_id" name="user_id" style="width: 400px; text-align: center;" class="border-b border-black" value="{{ request('user_id') ?? ($user->type == 3 ? $user->id : '') }}" readonly required>
            </div>
            <div class="col-span-1">
                <label class="block text-xs" for="name">NAME<span class="text-red-500">*</span></label>
                <input type="text" id="name" name="name" class="w-full border-b border-black" value="{{ $user->type == 3 ? $user->first_name . ' ' . $user->last_name : '' }}" {{ $user->type == 3 ? 'readonly' : '' }} required>
            </div>
            <div class="col-span-1">
                <label class="block text-xs" for="age">AGE<span class="text-red-500">*</span></label>
                <input type="text" id="age" name="age" class="w-full border-b border-black" value="{{ $user->type == 3 ? \Carbon\Carbon::parse($user->dob)->age : '' }}" {{ $user->type == 3 ? 'readonly' : '' }} required>
            </div>
            <div class="col-span-1">
                <label class="block text-xs" for="gender">GENDER<span class="text-red-500">*</span></label>
                <input type="text" id="gender" name="gender" class="w-full border-b border-black" value="{{ $user->type == 3 ? ($user->gender == 1 ? 'Male' : 'Female') : '' }}" {{ $user->type == 3 ? 'readonly' : '' }} required>
            </div>
            <div class="col-span-1">
                <label class="block text-xs" for="status">STATUS<span class="text-red-500">*</span></label>
                <input type="text" id="status" name="status" class="w-full border-b border-black" required>
            </div>
            <div class="col-span-1">
                <label class="block text-xs" for="date_of_birth">DATE OF BIRTH<span class="text-red-500">*</span></label>
                <input type="date" id="date_of_birth" name="date_of_birth" class="w-full border-b border-black" value="{{ $user->type == 3 ? $user->dob : '' }}" {{ $user->type == 3 ? 'readonly' : '' }} required>
            </div>
            <div class="col-span-1">
                <label class="block text-xs" for="address">ADDRESS<span class="text-red-500">*</span></label>
                <input type="text" id="address" name="address" class="w-full border-b border-black" value="{{ $user->type == 3 && $patient->address ? $patient->address->address1 : '' }}" {{ $user->type == 3 ? 'readonly' : '' }} required>
            </div>
            <div class="col-span-1">
                <label class="block text-xs" for="religion">RELIGION<span class="text-red-500">*</span></label>
                <input type="text" id="religion" name="religion" class="w-full border-b border-black" required>
            </div>
            <div class="col-span-1">
                <label class="block text-xs" for="patient_contact">PATIENT'S CONTACT #<span class="text-red-500">*</span></label>
                <input type="text" id="patient_contact" name="patient_contact" class="w-full border-b border-black" value="{{ $user->type == 3 ? $user->contact : '' }}" {{ $user->type == 3 ? 'readonly' : '' }} required>
            </div>

            <!-- Campus Field (for Students only) - Auto-filled if available -->
            <div class="col-span-1" id="campus_field">
                <label class="block text-xs" for="campus">CAMPUS</label>
                {{ Form::select('campus_id', $data['campuses'], $user->type == 3 ? $user->campus_id : null, ['id' => 'campus_id', 'class' => 'w-full border-b border-black', 'placeholder' => 'Select Campus']) }}
            </div>

            <!-- College Field (for Students and Faculty) - Auto-filled if available -->
            <div class="col-span-1" id="college_field">
                <label class="block text-xs" for="college">COLLEGE</label>
                {{ Form::select('college_id', $data['colleges'], $user->type == 3 ? $user->college_id : null, ['id' => 'college_id', 'class' => 'w-full border-b border-black', 'placeholder' => 'Select College']) }}
            </div>

            <!-- Course & Year Field (for Students only) - Auto-filled if available -->
            <div class="col-span-1" id="course_year_field">
                <label class="block text-xs" for="course_year">COURSE & YEAR</label>
                <div class="grid grid-cols-2 gap-2">
                    {{ Form::select('course_id', $data['courses'], $user->type == 3 ? $user->course_id : null, ['id' => 'course_id', 'class' => 'w-full border-b border-black', 'placeholder' => 'Select Course']) }}
                    {{ Form::select('year_level_id', $data['year_levels'], $user->type == 3 ? $user->year_level_id : null, ['id' => 'year_level_id', 'class' => 'w-full border-b border-black', 'placeholder' => 'Select Year Level']) }}
                </div>
            </div>

            <!-- Department Field (for Faculty only) - Auto-filled if available -->
            <div class="col-span-1" id="department_field" style="display: none;">
                <label class="block text-xs" for="department">DEPARTMENT</label>
                {{ Form::select('department_id', $data['departments'] ?? [], $user->type == 3 ? $user->department_id : null, ['id' => 'department_id', 'class' => 'w-full border-b border-black', 'placeholder' => 'Select Department']) }}
            </div>

            <!-- Office Field (for Staff only) - Auto-filled if available -->
            <div class="col-span-1" id="office_field" style="display: none;">
                <label class="block text-xs" for="office">OFFICE</label>
                {{ Form::select('office_id', $data['offices'] ?? [], $user->type == 3 ? $user->office_id : null, ['id' => 'office_id', 'class' => 'w-full border-b border-black', 'placeholder' => 'Select Office']) }}
            </div>

            <div class="col-span-1">
                <label class="block text-xs" for="informant">INFORMANT</label>
                <input type="text" id="informant" name="informant" class="w-full border-b border-black" value="Student">
            </div>
            <div class="col-span-4">
                <label class="block text-xs" for="emergency_contact">CONTACT PERSON & NUMBER IN EMERGENCY</label>
                <input type="text" id="emergency_contact" name="emergency_contact" class="w-full border-b border-black" value="{{ $user->type == 3 ? ($user->emergency_contact_name . ' / ' . $user->emergency_contact_no . ($user->emergency_relationship ? ' (' . $user->emergency_relationship . ')' : '')) : '' }}" required>
            </div>
        </div>
        <div class="grid grid-cols-4 gap-2 py-2">
            <div class="col-span-1">
                <label class="block text-xs" for="requested_at">CONSULTATION DATE<span class="text-red-500">*</span></label>
                <input type="date" id="requested_at" name="requested_at" class="w-full border-b border-black" max="{{ date('Y-m-d') }}" required>
            </div>
            <div class="col-span-3">
                <label class="block text-xs" for="complaints">Complaint/s:</label>
                <textarea id="complaints" name="complaints" class="w-full border-b border-black" rows="5"></textarea>
            </div>
        </div>
        <div class="grid grid-cols-4 gap-2 py-2">
            <div class="col-span-1">
                <label class="block text-red-500 font-bold">S</label>
                <label class="block text-xs">(Subjective Complaints)</label>
            </div>
            <div class="col-span-3">
                <div class="grid grid-cols-2 gap-2">
                    <div class="col-span-1">
                        <label class="block text-xs" for="covid_vaccination">COVID Vaccination<span class="text-red-500">*</span></label>
                        {{ Form::select('vaccination_id', $data['vaccination_data'], $user->type == 3 ? $user->vaccination_id : null, ['id' => 'vaccination_id', 'class' => 'w-full border-b border-black', 'placeholder' => 'Select Vaccination Status']) }}
                    </div>
                    <div class="col-span-1">
                        <label class="block text-xs" for="comorbidities">Comorbidities</label>
                        <select name="comorbidities_id" id="comorbidities_id" class="w-full border-b border-black">
                            <option value="none">None</option>
                            @foreach($data['comorbidities'] as $key => $value)
                            <option value="{{ $key }}">{{ $value }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-span-1">
                        <label class="block text-xs" for="allergies">Allergies<span class="text-red-500">*</span></label>
                        <input type="text" id="allergies" name="allergies" class="w-full border-b border-black">
                    </div>
                    <div class="col-span-1">
                        <label class="block text-xs" for="admissions_surgeries">Pertinent Admissions or Surgeries</label>
                        <input type="text" id="admissions_surgeries" name="admissions_surgeries" class="w-full border-b border-black">
                    </div>
                    <div class="col-span-1">
                        <label class="block text-xs" for="maintenance">Maintenance<span class="text-red-500">*</span></label>
                        <input type="text" id="maintenance" name="maintenance" class="w-full border-b border-black">
                    </div>
                    <div class="col-span-1">
                        <label class="block text-xs" for="pregnancy_status">Pregnant or Not?<span class="text-red-500">*</span></label>
                        <input type="text" id="pregnancy_status" name="pregnancy_status" class="w-full border-b border-black">
                    </div>
                    <div class="col-span-1">
                        <label class="block text-xs" for="lmp_aog">If YES, LMP/AOG</label>
                        <input type="text" id="lmp_aog" name="lmp_aog" class="w-full border-b border-black">
                    </div>
                </div>
            </div>
        </div>
        <div class="grid grid-cols-4 gap-2 py-2">
            <div class="col-span-1">
                <label class="block text-red-500 font-bold">O</label>
                <label class="block text-xs">(Objective Data)</label>
            </div>
            <div class="col-span-3">
                <div class="grid grid-cols-6 gap-2">
                    <div class="col-span-1">
                        <label class="block text-xs" for="vital_signs_bp">BP<span class="text-red-500">*</span></label>
                        <input type="text" id="vital_signs_bp" name="vital_signs_bp" class="w-full border-b border-black" placeholder="mmHg" required>
                    </div>
                    <div class="col-span-1">
                        <label class="block text-xs" for="vital_signs_pr">PR<span class="text-red-500">*</span></label>
                        <input type="text" id="vital_signs_pr" name="vital_signs_pr" class="w-full border-b border-black" placeholder="bpm" required>
                    </div>
                    <div class="col-span-1">
                        <label class="block text-xs" for="vital_signs_temp">Temp<span class="text-red-500">*</span></label>
                        <input type="text" id="vital_signs_temp" name="vital_signs_temp" class="w-full border-b border-black" placeholder="°C" required>
                    </div>
                    <div class="col-span-1">
                        <label class="block text-xs" for="vital_signs_rr">RR</label>
                        <input type="text" id="vital_signs_rr" name="vital_signs_rr" class="w-full border-b border-black" placeholder="breaths/min">
                    </div>
                    <div class="col-span-1">
                        <label class="block text-xs" for="vital_signs_o2_sat">O2 Sat<span class="text-red-500">*</span></label>
                        <input type="text" id="vital_signs_o2_sat" name="vital_signs_o2_sat" class="w-full border-b border-black" placeholder="%" required>
                    </div>
                    <div class="col-span-1">
                        <label class="block text-xs" for="vital_signs_weight">Weight (kg)</label>
                        <input type="text" id="vital_signs_weight" name="vital_signs_weight" class="w-full border-b border-black" placeholder="kg">
                    </div>
                    <div class="col-span-1">
                        <label class="block text-xs" for="vital_signs_height">Height (cm)</label>
                        <input type="text" id="vital_signs_height" name="vital_signs_height" class="w-full border-b border-black" placeholder="cm">
                    </div>
                </div>
                <div class="col-span-5">
                    <label class="block text-xs" for="pertinent_exam">PERTINENT EXAM<span class="text-red-500">*</span></label>
                    <textarea id="pertinent_exam" name="pertinent_exam" class="w-full border-b border-black" rows="5" required></textarea>
                </div>
            </div>
        </div>
        <div class="grid grid-cols-4 gap-2 py-2">
            <div class="col-span-1">
                <label class="block text-red-500 font-bold">A</label>
                <label class="block text-xs">(Assessment)<span class="text-red-500">*</span></label>
            </div>
            <div class="col-span-3">
                <textarea id="assessment" name="assessment" class="w-full border-b border-black" rows="5" required></textarea>
            </div>
        </div>
        <div class="grid grid-cols-4 gap-2 py-2">
            <div class="col-span-1">
                <label class="block text-red-500 font-bold">P</label>
                <label class="block text-xs">(Plan)<span class="text-red-500">*</span></label>
            </div>
            <div class="col-span-3">
                <textarea id="plan" name="plan" class="w-full border-b border-black" rows="5" required></textarea>
            </div>
        </div>

        <div class="grid grid-cols-4 gap-2 py-2">
            <div class="col-span-1">
                <label class="block">Consult Mode<span class="text-red-500">*</span></label>
            </div>
            <div class="col-span-3">
                {{ Form::select('consult_mode', ['physical' => 'Physical', 'virtual' => 'Virtual'], null, ['id' => 'consult_mode', 'class' => 'w-full border-b border-black', 'placeholder' => 'Select Consultation Mode', 'required']) }}
            </div>
        </div>

        <div class="grid grid-cols-4 gap-2 py-2">
            <div class="col-span-1">
                <label class="block">Nursing Intervention<span class="text-red-500">*</span></label>
            </div>
            <div class="col-span-3">
                <textarea id="nursing_intervention" name="nursing_intervention" class="w-full border-b border-black" rows="5" required></textarea>
            </div>
        </div>
        <div class="grid grid-cols-4 gap-2 py-2">
            <div class="col-span-1">
                <label class="block">Nursing In-charged<span class="text-red-500">*</span></label>
            </div>
            <div class="col-span-3">
                <!-- <select id="nursing_incharged" name="nursing_incharged" class="w-full border-b border-black" required>
                        <option value="" disabled selected>Select Nursing In-charged</option>
                        @foreach(\App\Models\User::where('type', \App\Models\User::STAFF)->get() as $staff)
                        <option value="{{ $staff->id }}">{{ $staff->first_name }} {{ $staff->last_name }}</option>
                        @endforeach
                    </select> -->

                @if(auth()->user()->type == \App\Models\User::ADMIN)
                <!-- Admin can select the nursing in-charged -->
                <select id="nursing_incharged" name="nursing_incharged" class="w-full border-b border-black" required>
                    <option value="" disabled selected>Select Nursing In-charged</option>
                    @foreach(\App\Models\User::where('type', \App\Models\User::STAFF)->get() as $staff)
                    <option value="{{ $staff->id }}">{{ $staff->first_name }} {{ $staff->last_name }}</option>
                    @endforeach
                </select>
                @elseif(auth()->user()->type == \App\Models\User::STAFF)
                <!-- Staff's account is pre-filled -->
                <input type="text" id="nursing_incharged_display" class="w-full border-b border-black" value="{{ auth()->user()->first_name }} {{ auth()->user()->last_name }}" readonly>
                <input type="hidden" id="nursing_incharged" name="nursing_incharged" value="{{ auth()->user()->id }}">
                @endif
            </div>
        </div>

        <!-- Image Upload Section -->
        <div class="grid grid-cols-4 gap-2 py-2">
            <div class="col-span-1">
                <label class="block">Upload Images</label>
                <small class="text-gray-500">Max 5MB per image</small>
            </div>
            <div class="col-span-3">
                <input type="file" id="consultation_images" name="consultation_images[]"
                    class="w-full border border-gray-300 rounded p-2"
                    accept="image/jpeg,image/png,image/jpg,image/gif"
                    multiple>
                <small class="text-gray-500">You can select multiple images (JPEG, PNG, JPG, GIF)</small>

                <!-- Image Preview Container -->
                <div id="image_preview_container" class="mt-4 grid grid-cols-3 gap-4"></div>
            </div>
        </div>

        <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded mt-4">
            Submit
        </button>
    </form>
</div>

<style>
    #complaints,
    #pertinent_exam,
    #assessment,
    #plan,
    #nursing_intervention {
        resize: none;
    }

    .image-preview-wrapper {
        position: relative;
        display: inline-block;
    }

    .image-preview {
        width: 100%;
        height: 150px;
        object-fit: cover;
        border-radius: 8px;
        border: 2px solid #e5e7eb;
    }

    .remove-image-btn {
        position: absolute;
        top: 5px;
        right: 5px;
        background-color: #ef4444;
        color: white;
        border: none;
        border-radius: 50%;
        width: 25px;
        height: 25px;
        cursor: pointer;
        font-size: 14px;
        line-height: 1;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .remove-image-btn:hover {
        background-color: #dc2626;
    }

    .image-size-error {
        color: #ef4444;
        font-size: 12px;
        margin-top: 4px;
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Pre-fill user_id if coming from patient history
        @if(request('user_id') && $patient)
        document.getElementById('user_id').value = '{{ request("user_id") }}';
        @endif

        // Field visibility management based on year_level_id
        const yearLevelSelect = document.getElementById('year_level_id');
        const campusField = document.getElementById('campus_field');
        const collegeField = document.getElementById('college_field');
        const courseYearField = document.getElementById('course_year_field');
        const departmentField = document.getElementById('department_field');
        const officeField = document.getElementById('office_field');

        function updateFieldsVisibility() {
            const yearLevelId = yearLevelSelect ? yearLevelSelect.value : '';

            // Hide all fields first
            campusField.style.display = 'none';
            collegeField.style.display = 'none';
            courseYearField.style.display = 'none';
            departmentField.style.display = 'none';
            officeField.style.display = 'none';

            if (yearLevelId == '1') {
                // Employee - need to determine if Faculty or Staff
                // For now, show both department and office, user can fill what's applicable
                collegeField.style.display = 'block';
                departmentField.style.display = 'block';
                officeField.style.display = 'block';
            } else if (yearLevelId == '8') {
                // Guest - no additional fields needed
            } else if (yearLevelId >= '2' && yearLevelId <= '7') {
                // Student (1st-6th year) - show all student fields
                campusField.style.display = 'block';
                collegeField.style.display = 'block';
                courseYearField.style.display = 'block';
            }
        }

        // Run on page load if year level is already selected
        if (yearLevelSelect) {
            updateFieldsVisibility();
            yearLevelSelect.addEventListener('change', updateFieldsVisibility);
        }

        // Auto-fill PERTINENT EXAM when Complaint/s is filled
        const complaintsField = document.getElementById('complaints');
        const pertinentExamField = document.getElementById('pertinent_exam');

        if (complaintsField && pertinentExamField) {
            complaintsField.addEventListener('input', function() {
                pertinentExamField.value = this.value;
            });
        }

        // Image Upload Preview and Validation
        const imageInput = document.getElementById('consultation_images');
        const previewContainer = document.getElementById('image_preview_container');
        const maxFileSize = 5 * 1024 * 1024; // 5MB in bytes
        let selectedFiles = [];

        if (imageInput) {
            imageInput.addEventListener('change', function(e) {
                const files = Array.from(e.target.files);
                previewContainer.innerHTML = ''; // Clear previous previews
                selectedFiles = []; // Reset selected files

                // Create a new FileList to store valid files
                const dataTransfer = new DataTransfer();

                files.forEach((file, index) => {
                    // Validate file size
                    if (file.size > maxFileSize) {
                        const errorDiv = document.createElement('div');
                        errorDiv.className = 'col-span-3 image-size-error';
                        errorDiv.textContent = `Error: ${file.name} exceeds 5MB limit (${(file.size / 1024 / 1024).toFixed(2)}MB)`;
                        previewContainer.appendChild(errorDiv);
                        return; // Skip this file
                    }

                    // Add valid file to the list
                    selectedFiles.push(file);
                    dataTransfer.items.add(file);

                    // Create preview
                    const reader = new FileReader();
                    reader.onload = function(event) {
                        const wrapper = document.createElement('div');
                        wrapper.className = 'image-preview-wrapper';

                        const img = document.createElement('img');
                        img.src = event.target.result;
                        img.className = 'image-preview';
                        img.alt = file.name;

                        const removeBtn = document.createElement('button');
                        removeBtn.className = 'remove-image-btn';
                        removeBtn.innerHTML = '×';
                        removeBtn.type = 'button';
                        removeBtn.onclick = function() {
                            // Remove from selectedFiles array
                            const fileIndex = selectedFiles.indexOf(file);
                            if (fileIndex > -1) {
                                selectedFiles.splice(fileIndex, 1);
                            }

                            // Update the file input
                            const newDataTransfer = new DataTransfer();
                            selectedFiles.forEach(f => newDataTransfer.items.add(f));
                            imageInput.files = newDataTransfer.files;

                            // Remove preview
                            wrapper.remove();

                            // Show message if no images
                            if (selectedFiles.length === 0) {
                                previewContainer.innerHTML = '<p class="text-gray-500 col-span-3">No images selected</p>';
                            }
                        };

                        const fileInfo = document.createElement('small');
                        fileInfo.className = 'text-gray-600 block mt-1';
                        fileInfo.textContent = `${file.name} (${(file.size / 1024 / 1024).toFixed(2)}MB)`;

                        wrapper.appendChild(img);
                        wrapper.appendChild(removeBtn);
                        wrapper.appendChild(fileInfo);
                        previewContainer.appendChild(wrapper);
                    };

                    reader.readAsDataURL(file);
                });

                // Update the file input with only valid files
                imageInput.files = dataTransfer.files;

                // Show message if no valid files
                if (selectedFiles.length === 0 && files.length > 0) {
                    const noValidFiles = document.createElement('p');
                    noValidFiles.className = 'text-red-500 col-span-3';
                    noValidFiles.textContent = 'No valid images selected. All files exceeded 5MB limit.';
                    previewContainer.appendChild(noValidFiles);
                }
            });
        }

        const userSearchInput = document.getElementById('user_search');
        const userSearchResults = document.getElementById('user_search_results');

        // Set the search route based on user role  
        let searchRoute = '';
        @if(isRole('clinic_admin'))
        searchRoute = '{{ route("search-users") }}';
        @elseif(isRole('staff'))
        searchRoute = '{{ route("staff.request-documents.search-users") }}';
        @elseif(isRole('doctor'))
        searchRoute = '{{ route("doctors.request-documents.search-users") }}';
        @else
        searchRoute = '{{ route("search-users") }}';
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
                                    document.getElementById('name').value = `${patientData.user.first_name} ${patientData.user.last_name}`;
                                    document.getElementById('age').value = calculateAge(patientData.user.dob);
                                    document.getElementById('gender').value = patientData.user.gender === 1 ? 'Male' : 'Female';
                                    document.getElementById('date_of_birth').value = patientData.user.dob || '';
                                    document.getElementById('vaccination_id').value = patientData.user.vaccination_id || '';
                                    document.getElementById('patient_contact').value = patientData.user.contact;
                                    document.getElementById('emergency_contact').value = `${patientData.user.emergency_contact_name}/${patientData.user.emergency_contact_no}${patientData.user.emergency_relationship ? ' (' + patientData.user.emergency_relationship + ')' : ''}`;

                                    // Fill student fields
                                    document.getElementById('campus_id').value = patientData.user.campus_id || '';
                                    document.getElementById('college_id').value = patientData.user.college_id || '';
                                    document.getElementById('course_id').value = patientData.user.course_id || '';
                                    document.getElementById('year_level_id').value = patientData.user.year_level_id || '';

                                    // Fill employee fields
                                    if (document.getElementById('department_id')) {
                                        document.getElementById('department_id').value = patientData.user.department_id || '';
                                    }
                                    if (document.getElementById('office_id')) {
                                        document.getElementById('office_id').value = patientData.user.office_id || '';
                                    }

                                    // Update field visibility based on year level
                                    updateFieldsVisibility();

                                    if (patientData.address) {
                                        document.getElementById('address').value = `${patientData.address.address1}`;
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
    });
</script>