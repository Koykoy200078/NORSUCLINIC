<div>
    @if($user->type != 4)
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
                <input type="text" id="user_id" name="user_id" style="width: 400px; text-align: center;" class="border-b border-black" value="{{ request('user_id') ?? ($user->type == 4 ? $user->id : '') }}" readonly required>
            </div>
            <div class="col-span-1">
                <label class="block text-xs" for="name">NAME<span class="text-red-500">*</span></label>
                <input type="text" id="name" name="name" class="w-full border-b border-black" value="{{ $user->type == 4 ? $user->first_name . ' ' . $user->last_name : '' }}" {{ $user->type == 4 ? 'readonly' : '' }} required>
            </div>
            <div class="col-span-1">
                <label class="block text-xs" for="age">AGE<span class="text-red-500">*</span></label>
                <input type="text" id="age" name="age" class="w-full border-b border-black" value="{{ $user->type == 4 ? \Carbon\Carbon::parse($user->dob)->age : '' }}" {{ $user->type == 4 ? 'readonly' : '' }} required>
            </div>
            <div class="col-span-1">
                <label class="block text-xs" for="gender">GENDER<span class="text-red-500">*</span></label>
                <input type="text" id="gender" name="gender" class="w-full border-b border-black" value="{{ $user->type == 4 ? ($user->gender == 1 ? 'Male' : 'Female') : '' }}" {{ $user->type == 4 ? 'readonly' : '' }} required>
            </div>
            <div class="col-span-1">
                <label class="block text-xs" for="status">STATUS<span class="text-red-500">*</span></label>
                <input type="text" id="status" name="status" class="w-full border-b border-black" required>
            </div>
            <div class="col-span-1">
                <label class="block text-xs" for="date_of_birth">DATE OF BIRTH<span class="text-red-500">*</span></label>
                <input type="date" id="date_of_birth" name="date_of_birth" class="w-full border-b border-black" value="{{ $user->type == 4 ? $user->dob : '' }}" {{ $user->type == 4 ? 'readonly' : '' }} required>
            </div>
            <div class="col-span-1">
                <label class="block text-xs" for="address">ADDRESS<span class="text-red-500">*</span></label>
                <input type="text" id="address" name="address" class="w-full border-b border-black" value="{{ $user->type == 4 && $patient->address ? $patient->address->address1 : '' }}" {{ $user->type == 4 ? 'readonly' : '' }} required>
            </div>
            <div class="col-span-1">
                <label class="block text-xs" for="religion">RELIGION<span class="text-red-500">*</span></label>
                <input type="text" id="religion" name="religion" class="w-full border-b border-black" required>
            </div>
            <div class="col-span-1">
                <label class="block text-xs" for="patient_contact">PATIENT'S CONTACT #<span class="text-red-500">*</span></label>
                <input type="text" id="patient_contact" name="patient_contact" class="w-full border-b border-black" value="{{ $user->type == 4 ? $user->contact : '' }}" {{ $user->type == 4 ? 'readonly' : '' }} required>
            </div>

            <!-- Campus Field (for Students only) - Auto-filled if available -->
            <div class="col-span-1" id="campus_field" style="display: {{ (isset($user->year_level_id) && $user->year_level_id >= 1 && $user->year_level_id <= 6) ? 'block' : 'none' }};">
                <label class="block text-xs" for="campus">CAMPUS</label>
                {{ Form::select('campus_id', $data['campuses'], $user->type == 4 ? $user->campus_id : null, ['id' => 'campus_id', 'class' => 'w-full border-b border-black', 'placeholder' => 'Select Campus']) }}
            </div>

            <!-- College Field (for Students and Faculty) - Auto-filled if available -->
            <div class="col-span-1" id="college_field" style="display: {{ (isset($user->year_level_id) && (($user->year_level_id >= 1 && $user->year_level_id <= 6) || $user->year_level_id == 7)) ? 'block' : 'none' }};">
                <label class="block text-xs" for="college">COLLEGE</label>
                {{ Form::select('college_id', $data['colleges'], $user->type == 4 ? $user->college_id : null, ['id' => 'college_id', 'class' => 'w-full border-b border-black', 'placeholder' => 'Select College']) }}
            </div>

            <!-- Course & Year Field (for Students only) - Auto-filled if available -->
            <div class="col-span-1" id="course_year_field" style="display: {{ (isset($user->year_level_id) && $user->year_level_id >= 1 && $user->year_level_id <= 6) ? 'block' : 'none' }};">
                <label class="block text-xs" for="course_year">COURSE & YEAR</label>
                <div class="grid grid-cols-2 gap-2">
                    {{ Form::select('course_id', $data['courses'], $user->type == 4 ? $user->course_id : null, ['id' => 'course_id', 'class' => 'w-full border-b border-black', 'placeholder' => 'Select Course']) }}
                    {{ Form::select('year_level_id', $data['year_levels'], $user->type == 4 ? $user->year_level_id : null, ['id' => 'year_level_id', 'class' => 'w-full border-b border-black', 'placeholder' => 'Select Year Level']) }}
                </div>
            </div>

            <!-- Department Field (for Faculty only) - Auto-filled if available -->

            <div class="col-span-1" id="department_field" style="display: {{ (isset($user->year_level_id) && $user->year_level_id == 7) ? 'block' : 'none' }};">
                <label class="block text-xs" for="department">DEPARTMENT</label>
                {{ Form::select('department_id', $data['departments'] ?? [], $user->type == 4 ? $user->department_id : null, ['id' => 'department_id', 'class' => 'w-full border-b border-black', 'placeholder' => 'Select Department']) }}
            </div>

            <!-- Office Field (for Staff only) - Auto-filled if available -->
            <div class="col-span-1" id="office_field" style="display: {{ (isset($user->year_level_id) && $user->year_level_id == 8) ? 'block' : 'none' }};">
                <label class="block text-xs" for="office">OFFICE</label>
                {{ Form::select('office_id', $data['offices'] ?? [], $user->type == 4 ? $user->office_id : null, ['id' => 'office_id', 'class' => 'w-full border-b border-black', 'placeholder' => 'Select Office']) }}
            </div>

            <div class="col-span-1">
                <label class="block text-xs" for="informant">INFORMANT</label>
                <input type="text" id="informant" name="informant" class="w-full border-b border-black" value="{{ 
                    $user->type == 4 ? (
                        $user->year_level_id == 7 ? 'Faculty' : 
                        ($user->year_level_id == 8 ? 'Staff' : 
                        ($user->year_level_id == 9 ? 'Guest' : 'Student'))
                    ) : 'Student' 
                }}">
            </div>
            <div class="col-span-4">
                <label class="block text-xs" for="emergency_contact">CONTACT PERSON & NUMBER IN EMERGENCY</label>
                <input type="text" id="emergency_contact" name="emergency_contact" class="w-full border-b border-black" value="{{ $user->type == 4 ? ($user->emergency_contact_name . ' / ' . $user->emergency_contact_no . ($user->emergency_relationship ? ' (' . $user->emergency_relationship . ')' : '')) : '' }}" required>
            </div>
        </div>
        <div class="grid grid-cols-4 gap-2 py-2">
            <div class="col-span-1">
                <label class="block text-xs" for="requested_at">CONSULTATION DATE<span class="text-red-500">*</span></label>
                <input type="date" id="requested_at" name="requested_at" class="w-full border-b border-black" max="{{ date('Y-m-d') }}" required>
            </div>
            <div class="col-span-3">
                <label class="block text-xs" for="complaints">Complaint/s:</label>
                <textarea id="complaints" name="complaints" class="w-full border-b border-black auto-resize-textarea" rows="2"></textarea>
            </div>
            <div class="col-span-1"></div>
            <div class="col-span-3">
                <textarea id="note" name="note" class="w-full border-b border-black auto-resize-textarea" rows="3"></textarea>
            </div>
        </div>
        <div class="grid grid-cols-4 gap-2 py-2">
            <div class="col-span-1">
                <label class="block text-red-500 font-bold">S</label>
                <label class="block text-xs">(Subjective Data)</label>
            </div>
            <div class="col-span-3">
                <div class="grid grid-cols-2 gap-2">
                    <div class="col-span-1">
                        <label class="block text-xs" for="covid_vaccination">COVID Vaccination<span class="text-red-500">*</span></label>
                        {{ Form::select('vaccination_id', $data['vaccination_data'], $user->type == 4 ? $user->vaccination_id : null, ['id' => 'vaccination_id', 'class' => 'w-full border-b border-black', 'placeholder' => 'Select Vaccination Status']) }}
                    </div>
                    <div class="col-span-1">
                        <label class="block text-xs" for="comorbidities">Comorbidities</label>
                        <div class="relative">
                            <input type="text"
                                name="comorbidities_custom"
                                id="comorbidities_input"
                                list="comorbidities_list"
                                class="w-full border-b border-black"
                                placeholder="Select or type custom comorbidity"
                                autocomplete="off">
                            <datalist id="comorbidities_list">
                                <option value="None">
                                    @foreach($data['comorbidities'] as $key => $value)
                                <option value="{{ $value }}">
                                    @endforeach
                            </datalist>
                        </div>
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
                        <label class="block text-xs" for="vital_signs_bp">BP</label>
                        <input type="text" id="vital_signs_bp" name="vital_signs_bp" class="w-full border-b border-black" placeholder="mmHg">
                    </div>
                    <div class="col-span-1">
                        <label class="block text-xs" for="vital_signs_pr">PR</label>
                        <input type="text" id="vital_signs_pr" name="vital_signs_pr" class="w-full border-b border-black" placeholder="bpm">
                    </div>
                    <div class="col-span-1">
                        <label class="block text-xs" for="vital_signs_temp">Temp</label>
                        <input type="text" id="vital_signs_temp" name="vital_signs_temp" class="w-full border-b border-black" placeholder="°C">
                    </div>
                    <div class="col-span-1">
                        <label class="block text-xs" for="vital_signs_rr">RR</label>
                        <input type="text" id="vital_signs_rr" name="vital_signs_rr" class="w-full border-b border-black" placeholder="cycles/min">
                    </div>
                    <div class="col-span-1">
                        <label class="block text-xs" for="vital_signs_o2_sat">O2 Sat</label>
                        <input type="text" id="vital_signs_o2_sat" name="vital_signs_o2_sat" class="w-full border-b border-black" placeholder="%">
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
                    <label class="block text-xs" for="pertinent_exam">PERTINENT EXAM</label>
                    <textarea id="pertinent_exam" name="pertinent_exam" class="w-full border-b border-black auto-resize-textarea" rows="5"></textarea>
                </div>
            </div>
        </div>
        <div class="grid grid-cols-4 gap-2 py-2">
            <div class="col-span-1">
                <label class="block text-red-500 font-bold">A</label>
                <label class="block text-xs">(Assessment)</label>
            </div>
            <div class="col-span-3">
                <textarea id="assessment" name="assessment" class="w-full border-b border-black auto-resize-textarea" rows="5"></textarea>
            </div>
        </div>
        <div class="grid grid-cols-4 gap-2 py-2">
            <div class="col-span-1">
                <label class="block text-red-500 font-bold">P</label>
                <label class="block text-xs">(Plan)</label>
            </div>
            <div class="col-span-3">
                <textarea id="plan" name="plan" class="w-full border-b border-black auto-resize-textarea" rows="5"></textarea>

                <!-- Medicine Selection for Plan -->
                <div class="mt-3">
                    <label class="block text-xs font-semibold mb-2">Add Medicines to Plan:</label>
                    <button type="button" id="add_plan_medicine_btn" class="bg-green-500 text-white px-3 py-1 rounded text-sm">
                        <i class="fas fa-plus"></i> Add Medicine
                    </button>
                    <div id="plan_medicines_container" class="mt-2 space-y-2"></div>
                </div>
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
                <label class="block">Nursing Intervention</label>
            </div>
            <div class="col-span-3">
                <textarea id="nursing_intervention" name="nursing_intervention" class="w-full border-b border-black auto-resize-textarea" rows="5"></textarea>

                <!-- Medicine Selection for Nursing Intervention -->
                <div class="mt-3">
                    <label class="block text-xs font-semibold mb-2">Add Medicines to Nursing Intervention:</label>
                    <button type="button" id="add_nursing_medicine_btn" class="bg-green-500 text-white px-3 py-1 rounded text-sm">
                        <i class="fas fa-plus"></i> Add Medicine
                    </button>
                    <div id="nursing_medicines_container" class="mt-2 space-y-2"></div>
                </div>
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
                <!-- Drag & Drop Zone -->
                <div id="drop_zone" class="border-2 border-dashed border-gray-300 rounded-lg p-6 text-center mb-4 transition-all hover:border-blue-400 hover:bg-blue-50">
                    <i class="fas fa-cloud-upload-alt text-4xl text-gray-400 mb-2"></i>
                    <p class="text-gray-600 font-semibold mb-1">Drag & Drop Images Here</p>
                    <p class="text-gray-500 text-sm mb-3">or</p>
                    <label for="consultation_images" class="bg-blue-500 text-white px-4 py-2 rounded cursor-pointer hover:bg-blue-600 inline-block">
                        <i class="fas fa-folder-open"></i> Browse Files
                    </label>
                    <input type="file" id="consultation_images" name="consultation_images[]"
                        class="hidden"
                        accept="image/jpeg,image/png,image/jpg,image/gif"
                        multiple>
                    <p class="text-gray-500 text-xs mt-3">Supported: JPEG, PNG, JPG, GIF (Max 5MB each)</p>
                </div>

                <!-- Image URL Input -->
                <div class="mb-4">
                    <label class="block text-sm font-semibold mb-2">
                        <i class="fas fa-link"></i> Or paste image URL to auto-download:
                    </label>
                    <div class="flex gap-2">
                        <input type="text" id="image_url_input" 
                            class="flex-1 border border-gray-300 rounded px-3 py-2" 
                            placeholder="https://example.com/image.jpg">
                        <button type="button" id="download_from_url_btn" 
                            class="bg-green-500 text-white px-4 py-2 rounded hover:bg-green-600">
                            <i class="fas fa-download"></i> Download
                        </button>
                    </div>
                    <small class="text-gray-500">Paste an image URL and click Download to add it to your uploads</small>
                </div>

                <!-- Image Preview Container -->
                <div id="image_preview_container" class="mt-4 grid grid-cols-3 gap-4"></div>
            </div>
        </div>

        <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded mt-4 hover:bg-blue-600">
            Submit
        </button>
    </form>
</div>

<style>
    .auto-resize-textarea {
        resize: none;
        overflow: hidden;
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

    /* Medicine selection styles */
    .medicine-row {
        display: grid;
        grid-template-columns: 2fr 1.5fr 1fr 2fr auto;
        gap: 0.5rem;
        padding: 0.5rem;
        background-color: #f9fafb;
        border-radius: 0.375rem;
        align-items: center;
    }

    .medicine-row select,
    .medicine-row input {
        padding: 0.375rem 0.5rem;
        border: 1px solid #d1d5db;
        border-radius: 0.25rem;
        font-size: 0.875rem;
    }

    .medicine-row select optgroup {
        font-weight: bold;
        font-style: normal;
        background-color: #e5e7eb;
    }

    .medicine-row select option {
        padding: 0.25rem;
    }

    .remove-medicine-btn {
        background-color: #ef4444;
        color: white;
        border: none;
        border-radius: 0.25rem;
        padding: 0.375rem 0.75rem;
        cursor: pointer;
        font-size: 0.875rem;
    }

    .remove-medicine-btn:hover {
        background-color: #dc2626;
    }

    .medicine-stock-info {
        font-size: 0.75rem;
        color: #6b7280;
        margin-top: 0.125rem;
    }

    .medicine-stock-warning {
        color: #ef4444;
        font-weight: 600;
    }

    /* Drag and Drop Zone Styles */
    #drop_zone {
        cursor: pointer;
    }

    #drop_zone.drag-over {
        border-color: #3b82f6;
        background-color: #dbeafe;
        transform: scale(1.02);
    }

    #drop_zone.drag-over i {
        color: #3b82f6;
        transform: scale(1.1);
    }

    .image-loading {
        position: relative;
        opacity: 0.6;
    }

    .image-loading::after {
        content: '';
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        width: 30px;
        height: 30px;
        border: 3px solid #f3f3f3;
        border-top: 3px solid #3b82f6;
        border-radius: 50%;
        animation: spin 1s linear infinite;
    }

    @keyframes spin {
        0% { transform: translate(-50%, -50%) rotate(0deg); }
        100% { transform: translate(-50%, -50%) rotate(360deg); }
    }

    .url-downloading {
        position: relative;
    }

    .url-downloading::after {
        content: 'Downloading...';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(59, 130, 246, 0.9);
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 12px;
        font-weight: bold;
        border-radius: 0.375rem;
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

            if (yearLevelId == '7') {
                // Faculty (ID 7) - show college and department
                collegeField.style.display = 'block';
                departmentField.style.display = 'block';
            } else if (yearLevelId == '8') {
                // Staff (ID 8) - show office only
                officeField.style.display = 'block';
            } else if (yearLevelId == '9') {
                // Guest (ID 9) - no additional fields needed
            } else if (yearLevelId >= '1' && yearLevelId <= '6') {
                // Student (IDs 1-6) - show campus, college, course & year
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
        // const complaintsField = document.getElementById('complaints');
        // const pertinentExamField = document.getElementById('pertinent_exam');

        // if (complaintsField && pertinentExamField) {
        //     complaintsField.addEventListener('input', function() {
        //         pertinentExamField.value = this.value;
        //     });
        // }

        // Auto-resize textarea functionality for all textareas with auto-resize-textarea class
        function autoResizeTextarea(textarea) {
            // Reset height to auto to get the correct scrollHeight
            textarea.style.height = 'auto';
            // Set height based on scrollHeight
            textarea.style.height = textarea.scrollHeight + 'px';
        }

        // Initialize auto-resize for all textareas with the class
        const autoResizeTextareas = document.querySelectorAll('.auto-resize-textarea');
        autoResizeTextareas.forEach(function(textarea) {
            // Add input event listener
            textarea.addEventListener('input', function() {
                autoResizeTextarea(this);
            });
            // Initialize height on page load
            autoResizeTextarea(textarea);
        });

        // ==================== IMAGE UPLOAD WITH DRAG & DROP AND URL DOWNLOAD ====================
        const imageInput = document.getElementById('consultation_images');
        const previewContainer = document.getElementById('image_preview_container');
        const dropZone = document.getElementById('drop_zone');
        const imageUrlInput = document.getElementById('image_url_input');
        const downloadUrlBtn = document.getElementById('download_from_url_btn');
        const maxFileSize = 5 * 1024 * 1024; // 5MB in bytes
        let selectedFiles = [];

        // Function to validate and add files
        function processFiles(files) {
            const filesArray = Array.from(files);
            const dataTransfer = new DataTransfer();

            // Keep existing files
            selectedFiles.forEach(f => dataTransfer.items.add(f));

            filesArray.forEach((file) => {
                // Validate file type
                if (!file.type.match('image.*')) {
                    showError(`${file.name} is not a valid image file.`);
                    return;
                }

                // Validate file size
                if (file.size > maxFileSize) {
                    showError(`${file.name} exceeds 5MB limit (${(file.size / 1024 / 1024).toFixed(2)}MB)`);
                    return;
                }

                // Add valid file to the list
                selectedFiles.push(file);
                dataTransfer.items.add(file);

                // Create preview
                createImagePreview(file);
            });

            // Update the file input with all files
            imageInput.files = dataTransfer.files;
        }

        // Function to create image preview
        function createImagePreview(file) {
            const reader = new FileReader();
            reader.onload = function(event) {
                const wrapper = document.createElement('div');
                wrapper.className = 'image-preview-wrapper';
                wrapper.dataset.filename = file.name;

                const img = document.createElement('img');
                img.src = event.target.result;
                img.className = 'image-preview';
                img.alt = file.name;

                const removeBtn = document.createElement('button');
                removeBtn.className = 'remove-image-btn';
                removeBtn.innerHTML = '×';
                removeBtn.type = 'button';
                removeBtn.onclick = function() {
                    removeImage(file, wrapper);
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
        }

        // Function to remove image
        function removeImage(file, wrapper) {
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
        }

        // Function to show error
        function showError(message) {
            const errorDiv = document.createElement('div');
            errorDiv.className = 'col-span-3 image-size-error';
            errorDiv.textContent = `⚠️ ${message}`;
            previewContainer.appendChild(errorDiv);

            // Auto-remove error after 5 seconds
            setTimeout(() => {
                errorDiv.remove();
            }, 5000);
        }

        // File input change event
        if (imageInput) {
            imageInput.addEventListener('change', function(e) {
                processFiles(e.target.files);
            });
        }

        // ==================== DRAG & DROP FUNCTIONALITY ====================
        if (dropZone) {
            // Prevent default drag behaviors
            ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
                dropZone.addEventListener(eventName, preventDefaults, false);
                document.body.addEventListener(eventName, preventDefaults, false);
            });

            function preventDefaults(e) {
                e.preventDefault();
                e.stopPropagation();
            }

            // Highlight drop zone when item is dragged over it
            ['dragenter', 'dragover'].forEach(eventName => {
                dropZone.addEventListener(eventName, highlight, false);
            });

            ['dragleave', 'drop'].forEach(eventName => {
                dropZone.addEventListener(eventName, unhighlight, false);
            });

            function highlight(e) {
                dropZone.classList.add('drag-over');
            }

            function unhighlight(e) {
                dropZone.classList.remove('drag-over');
            }

            // Handle dropped files
            dropZone.addEventListener('drop', handleDrop, false);

            function handleDrop(e) {
                const dt = e.dataTransfer;
                const files = dt.files;

                processFiles(files);
            }

            // Click to browse
            dropZone.addEventListener('click', function(e) {
                if (e.target.id !== 'consultation_images' && !e.target.closest('label')) {
                    imageInput.click();
                }
            });
        }

        // ==================== IMAGE URL DOWNLOAD FUNCTIONALITY ====================
        if (downloadUrlBtn && imageUrlInput) {
            downloadUrlBtn.addEventListener('click', async function() {
                const imageUrl = imageUrlInput.value.trim();

                if (!imageUrl) {
                    alert('Please enter an image URL');
                    return;
                }

                // Validate URL format
                try {
                    new URL(imageUrl);
                } catch (e) {
                    alert('Please enter a valid URL');
                    return;
                }

                // Show loading state
                downloadUrlBtn.disabled = true;
                downloadUrlBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Downloading...';
                downloadUrlBtn.classList.add('url-downloading');

                try {
                    // Fetch the image
                    const response = await fetch(imageUrl);
                    
                    if (!response.ok) {
                        throw new Error(`Failed to download image: ${response.statusText}`);
                    }

                    const blob = await response.blob();

                    // Validate if it's an image
                    if (!blob.type.match('image.*')) {
                        throw new Error('The URL does not point to a valid image file');
                    }

                    // Validate file size
                    if (blob.size > maxFileSize) {
                        throw new Error(`Image exceeds 5MB limit (${(blob.size / 1024 / 1024).toFixed(2)}MB)`);
                    }

                    // Extract filename from URL or generate one
                    let filename = imageUrl.split('/').pop().split('?')[0];
                    if (!filename || !filename.match(/\\.(jpg|jpeg|png|gif)$/i)) {
                        const ext = blob.type.split('/')[1];
                        filename = `downloaded_image_${Date.now()}.${ext}`;
                    }

                    // Create File object from blob
                    const file = new File([blob], filename, { type: blob.type });

                    // Add to selected files
                    selectedFiles.push(file);

                    // Update file input
                    const dataTransfer = new DataTransfer();
                    selectedFiles.forEach(f => dataTransfer.items.add(f));
                    imageInput.files = dataTransfer.files;

                    // Create preview
                    createImagePreview(file);

                    // Clear input
                    imageUrlInput.value = '';

                    // Show success message
                    const successDiv = document.createElement('div');
                    successDiv.className = 'col-span-3 text-green-600 font-semibold';
                    successDiv.innerHTML = `✓ Image downloaded successfully: ${filename}`;
                    previewContainer.appendChild(successDiv);

                    setTimeout(() => {
                        successDiv.remove();
                    }, 3000);

                } catch (error) {
                    console.error('Error downloading image:', error);
                    alert(`Error downloading image: ${error.message}`);
                } finally {
                    // Reset button state
                    downloadUrlBtn.disabled = false;
                    downloadUrlBtn.innerHTML = '<i class="fas fa-download"></i> Download';
                    downloadUrlBtn.classList.remove('url-downloading');
                }
            });

            // Allow Enter key to trigger download
            imageUrlInput.addEventListener('keypress', function(e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    downloadUrlBtn.click();
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

                                    // Update informant field based on year_level_id
                                    let informantValue = 'Student'; // Default
                                    if (patientData.user.year_level_id == 7) {
                                        informantValue = 'Faculty';
                                    } else if (patientData.user.year_level_id == 8) {
                                        informantValue = 'Staff';
                                    } else if (patientData.user.year_level_id == 9) {
                                        informantValue = 'Guest';
                                    }
                                    document.getElementById('informant').value = informantValue;

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

        // ==================== MEDICINE SELECTION FUNCTIONALITY ====================

        // Fetch medicines from API (grouped by category with dosages)
        let medicinesData = [];

        async function fetchMedicines() {
            try {
                const response = await fetch('{{ route("medicines.by.category") }}');
                const result = await response.json();

                if (result.success) {
                    medicinesData = result.data;
                } else {
                    console.error('Error fetching medicines:', result.message);
                }
            } catch (error) {
                console.error('Error fetching medicines:', error);
            }
        }

        // Call on page load
        fetchMedicines();

        let planMedicineCounter = 0;
        let nursingMedicineCounter = 0;

        // Add medicine for Plan
        document.getElementById('add_plan_medicine_btn').addEventListener('click', function() {
            addMedicineRow('plan', planMedicineCounter++);
        });

        // Add medicine for Nursing Intervention
        document.getElementById('add_nursing_medicine_btn').addEventListener('click', function() {
            addMedicineRow('nursing', nursingMedicineCounter++);
        });

        function addMedicineRow(type, index) {
            const container = type === 'plan' ?
                document.getElementById('plan_medicines_container') :
                document.getElementById('nursing_medicines_container');

            const row = document.createElement('div');
            row.className = 'medicine-row';
            row.dataset.type = type;
            row.dataset.index = index;

            // Medicine select (grouped by category)
            const medicineSelect = document.createElement('select');
            medicineSelect.name = `medicines[${type}][${index}][medicine_id]`;
            medicineSelect.className = 'medicine-select';
            medicineSelect.required = true;

            const defaultOption = document.createElement('option');
            defaultOption.value = '';
            defaultOption.textContent = 'Select Medicine';
            medicineSelect.appendChild(defaultOption);

            // Populate medicines grouped by category
            medicinesData.forEach(category => {
                const optgroup = document.createElement('optgroup');
                optgroup.label = category.name;

                category.medicines.forEach(medicine => {
                    const option = document.createElement('option');
                    option.value = medicine.id;
                    option.textContent = `${medicine.name}`;
                    option.dataset.medicineId = medicine.id;
                    option.dataset.medicineName = medicine.name;
                    option.dataset.dosages = JSON.stringify(medicine.dosages);
                    option.dataset.totalStock = medicine.available_quantity;
                    optgroup.appendChild(option);
                });

                medicineSelect.appendChild(optgroup);
            });

            // Dosage select (populated when medicine is selected)
            const dosageSelect = document.createElement('select');
            dosageSelect.name = `medicines[${type}][${index}][dosage]`;
            dosageSelect.className = 'dosage-select';
            dosageSelect.required = true;
            dosageSelect.disabled = true;

            const dosageDefaultOption = document.createElement('option');
            dosageDefaultOption.value = '';
            dosageDefaultOption.textContent = 'Select Dosage';
            dosageSelect.appendChild(dosageDefaultOption);

            // Quantity input
            const quantityInput = document.createElement('input');
            quantityInput.type = 'number';
            quantityInput.name = `medicines[${type}][${index}][quantity]`;
            quantityInput.placeholder = 'Qty';
            quantityInput.min = '1';
            quantityInput.value = '1';
            quantityInput.required = true;
            quantityInput.disabled = true;

            // Dosage instructions
            const dosageInstructions = document.createElement('input');
            dosageInstructions.type = 'text';
            dosageInstructions.name = `medicines[${type}][${index}][dosage_instructions]`;
            dosageInstructions.placeholder = 'Instructions (e.g., 1 tablet 3x a day)';

            // Remove button
            const removeBtn = document.createElement('button');
            removeBtn.type = 'button';
            removeBtn.className = 'remove-medicine-btn';
            removeBtn.innerHTML = '<i class="fas fa-trash"></i>';
            removeBtn.addEventListener('click', function() {
                row.remove();
            });

            // Medicine selection handler - populate dosages
            medicineSelect.addEventListener('change', function() {
                const selectedOption = this.options[this.selectedIndex];

                // Clear previous dosages
                dosageSelect.innerHTML = '';
                dosageSelect.appendChild(dosageDefaultOption.cloneNode(true));
                dosageSelect.disabled = true;
                quantityInput.disabled = true;
                quantityInput.value = '1';

                // Remove existing warnings
                const existingWarning = row.querySelector('.medicine-stock-info');
                if (existingWarning) {
                    existingWarning.remove();
                }

                if (!selectedOption.value) return;

                const dosages = JSON.parse(selectedOption.dataset.dosages || '[]');

                if (dosages.length === 0) {
                    const warning = document.createElement('div');
                    warning.className = 'medicine-stock-info medicine-stock-warning';
                    warning.textContent = `⚠️ No dosages available for this medicine!`;
                    row.appendChild(warning);
                    return;
                }

                // Populate dosage options
                dosages.forEach(dosageItem => {
                    const option = document.createElement('option');
                    option.value = dosageItem.dosage;
                    option.textContent = `${dosageItem.dosage} (Available: ${dosageItem.available_quantity})`;
                    option.dataset.availableQty = dosageItem.available_quantity;
                    dosageSelect.appendChild(option);
                });

                dosageSelect.disabled = false;
            });

            // Dosage selection handler - enable quantity and set max
            dosageSelect.addEventListener('change', function() {
                const selectedOption = this.options[this.selectedIndex];

                // Remove existing warnings
                const existingWarning = row.querySelector('.medicine-stock-info');
                if (existingWarning) {
                    existingWarning.remove();
                }

                if (!selectedOption.value) {
                    quantityInput.disabled = true;
                    quantityInput.value = '1';
                    return;
                }

                const availableQty = parseInt(selectedOption.dataset.availableQty || 0);
                quantityInput.max = availableQty;
                quantityInput.disabled = false;

                // Show stock warning if low
                if (availableQty <= 0) {
                    const warning = document.createElement('div');
                    warning.className = 'medicine-stock-info medicine-stock-warning';
                    warning.textContent = `⚠️ This dosage is out of stock!`;
                    row.appendChild(warning);
                    dosageSelect.value = '';
                    quantityInput.disabled = true;
                } else if (availableQty < 10) {
                    const warning = document.createElement('div');
                    warning.className = 'medicine-stock-info medicine-stock-warning';
                    warning.textContent = `⚠️ Low stock: Only ${availableQty} units available`;
                    row.appendChild(warning);
                }
            });

            // Quantity validation
            quantityInput.addEventListener('input', function() {
                const selectedDosageOption = dosageSelect.options[dosageSelect.selectedIndex];
                const availableQty = parseInt(selectedDosageOption.dataset.availableQty || 0);
                const quantity = parseInt(this.value || 0);

                if (quantity > availableQty) {
                    this.value = availableQty;
                    alert(`Only ${availableQty} units available for this dosage.`);
                }
            });

            row.appendChild(medicineSelect);
            row.appendChild(dosageSelect);
            row.appendChild(quantityInput);
            row.appendChild(dosageInstructions);
            row.appendChild(removeBtn);

            container.appendChild(row);
        }

        // ==================== LOAD PAST DATA FUNCTIONALITY ====================

        const loadPastDataBtn = document.getElementById('load_past_data_btn');

        if (loadPastDataBtn) {
            loadPastDataBtn.addEventListener('click', async function() {
                const userId = document.getElementById('user_id').value;

                if (!userId) {
                    alert('Please select a patient first.');
                    return;
                }

                // Show loading state
                loadPastDataBtn.disabled = true;
                loadPastDataBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Loading...';

                try {
                    // Set the search route based on user role
                    let getLastConsultationRoute = '';
                    @if(isRole('clinic_admin'))
                    getLastConsultationRoute = '{{ route("get-last-consultation") }}';
                    @elseif(isRole('staff'))
                    getLastConsultationRoute = '{{ route("staff.request-documents.get-last-consultation") }}';
                    @elseif(isRole('doctor'))
                    getLastConsultationRoute = '{{ route("doctors.request-documents.get-last-consultation") }}';
                    @else
                    getLastConsultationRoute = '{{ route("get-last-consultation") }}';
                    @endif

                    const response = await fetch(`${getLastConsultationRoute}?user_id=${userId}`);
                    const result = await response.json();

                    if (result.success) {
                        const data = result.data;

                        // Populate the fields
                        if (data.status) document.getElementById('status').value = data.status;
                        if (data.religion) document.getElementById('religion').value = data.religion;
                        if (data.allergies) document.getElementById('allergies').value = data.allergies;
                        if (data.admissions_surgeries) document.getElementById('admissions_surgeries').value = data.admissions_surgeries;
                        if (data.maintenance) document.getElementById('maintenance').value = data.maintenance;
                        if (data.pregnancy_status) document.getElementById('pregnancy_status').value = data.pregnancy_status;
                        if (data.lmp_aog) document.getElementById('lmp_aog').value = data.lmp_aog;
                        if (data.vital_signs_bp) document.getElementById('vital_signs_bp').value = data.vital_signs_bp;
                        if (data.vital_signs_pr) document.getElementById('vital_signs_pr').value = data.vital_signs_pr;
                        if (data.vital_signs_temp) document.getElementById('vital_signs_temp').value = data.vital_signs_temp;
                        if (data.vital_signs_rr) document.getElementById('vital_signs_rr').value = data.vital_signs_rr;
                        if (data.vital_signs_o2_sat) document.getElementById('vital_signs_o2_sat').value = data.vital_signs_o2_sat;
                        if (data.vital_signs_weight) document.getElementById('vital_signs_weight').value = data.vital_signs_weight;
                        if (data.vital_signs_height) document.getElementById('vital_signs_height').value = data.vital_signs_height;

                        alert('Past data loaded successfully!');
                    } else {
                        alert(result.error || 'No previous consultation found for this patient.');
                    }
                } catch (error) {
                    console.error('Error loading past data:', error);
                    alert('An error occurred while loading past data. Please try again.');
                } finally {
                    // Reset button state
                    loadPastDataBtn.disabled = false;
                    loadPastDataBtn.innerHTML = '<i class="fas fa-history"></i> Load Past Data';
                }
            });
        }
    });
</script>