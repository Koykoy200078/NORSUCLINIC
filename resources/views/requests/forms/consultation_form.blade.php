<div>
    @if($user->type != 3)
    <div class="mb-10">
        <label class="block text-xs" for="user_search">Search User</label>
        <input type="text" id="user_search" class="w-full border-b border-black" placeholder="Search by name or email">
        <div id="user_search_results" class="absolute bg-white border border-gray-300 w-full hidden z-10"></div>
    </div>
    @endif

    <form action="{{ route('request-documents.store') }}" method="POST">
        @csrf
        <div class="form-group mb-5 d-none">
            <label for="document_type">Document Type</label>
            <select name="document_type" id="document_type" class="form-control" required>
                <option value="consultation_form" selected>Consultation Form</option>
            </select>
        </div>

        <div class="grid grid-cols-4 gap-2 pb-2">
            <div class="col-span-1 d-none">
                <label class="block text-xs" for="name">ID<span class="text-red-500">*</span></label>
                <input type="text" id="document_creator_id" name="document_creator_id" style="width: 400px; text-align: center;" class="border-b border-black" value="{{ auth()->user()->id }}" readonly required>
                <input type="text" id="user_id" name="user_id" style="width: 400px; text-align: center;" class="border-b border-black" readonly required>
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
            <div class="col-span-1">
                <label class="block text-xs" for="campus">CAMPUS<span class="text-red-500">*</span></label>
                {{ Form::select('campus_id', $data['campuses'], $user->type == 3 ? $user->campus_id : null, ['id' => 'campus_id', 'class' => 'w-full border-b border-black', 'placeholder' => 'Select Campus', 'required']) }}
            </div>
            <div class="col-span-1">
                <label class="block text-xs" for="college">COLLEGE<span class="text-red-500">*</span></label>
                {{ Form::select('college_id', $data['colleges'], $user->type == 3 ? $user->college_id : null, ['id' => 'college_id', 'class' => 'w-full border-b border-black', 'placeholder' => 'Select College', 'required']) }}
            </div>
            <div class="col-span-1">
                <label class="block text-xs" for="course_year">COURSE & YEAR<span class="text-red-500">*</span></label>
                <div class="grid grid-cols-2 gap-2">
                    {{ Form::select('course_id', $data['courses'], $user->type == 3 ? $user->course_id : null, ['id' => 'course_id', 'class' => 'w-full border-b border-black', 'placeholder' => 'Select Course', 'required']) }}
                    {{ Form::select('year_level_id', $data['year_levels'], $user->type == 3 ? $user->year_level_id : null, ['id' => 'year_level_id', 'class' => 'w-full border-b border-black', 'placeholder' => 'Select Year Level', 'required']) }}
                </div>
            </div>
            <div class="col-span-1">
                <label class="block text-xs" for="informant">INFORMANT</label>
                <input type="text" id="informant" name="informant" class="w-full border-b border-black" value="Student">
            </div>
            <div class="col-span-4">
                <label class="block text-xs" for="emergency_contact">CONTACT PERSON & NUMBER IN EMERGENCY</label>
                <input type="text" id="emergency_contact" name="emergency_contact" class="w-full border-b border-black" required>
            </div>
        </div>
        <div class="grid grid-cols-4 gap-2 py-2">
            <div class="col-span-1">
                <label class="block text-xs" for="requested_at">REQUEST DATE<span class="text-red-500">*</span></label>
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
                        {{ Form::select('comorbidities_id', $data['comorbidities'], null, ['id' => 'comorbidities_id', 'class' => 'w-full border-b border-black', 'placeholder' => 'Select Comorbidities']) }}
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
                        <input type="text" id="vital_signs_bp" name="vital_signs_bp" class="w-full border-b border-black" required>
                    </div>
                    <div class="col-span-1">
                        <label class="block text-xs" for="vital_signs_pr">PR<span class="text-red-500">*</span></label>
                        <input type="text" id="vital_signs_pr" name="vital_signs_pr" class="w-full border-b border-black" required>
                    </div>
                    <div class="col-span-1">
                        <label class="block text-xs" for="vital_signs_temp">Temp<span class="text-red-500">*</span></label>
                        <input type="text" id="vital_signs_temp" name="vital_signs_temp" class="w-full border-b border-black" required>
                    </div>
                    <div class="col-span-1">
                        <label class="block text-xs" for="vital_signs_rr">RR<span class="text-red-500">*</span></label>
                        <input type="text" id="vital_signs_rr" name="vital_signs_rr" class="w-full border-b border-black" required>
                    </div>
                    <div class="col-span-1">
                        <label class="block text-xs" for="vital_signs_o2_sat">O2 Sat<span class="text-red-500">*</span></label>
                        <input type="text" id="vital_signs_o2_sat" name="vital_signs_o2_sat" class="w-full border-b border-black" required>
                    </div>
                    <div class="col-span-1">
                        <label class="block text-xs" for="vital_signs_weight">Weight<span class="text-red-500">*</span></label>
                        <input type="text" id="vital_signs_weight" name="vital_signs_weight" class="w-full border-b border-black" required>
                    </div>
                    <div class="col-span-1">
                        <label class="block text-xs" for="vital_signs_height">Height<span class="text-red-500">*</span></label>
                        <input type="text" id="vital_signs_height" name="vital_signs_height" class="w-full border-b border-black" required>
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
</style>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const userSearchInput = document.getElementById('user_search');
        const userSearchResults = document.getElementById('user_search_results');

        if (userSearchInput) {
            userSearchInput.addEventListener('input', function() {
                const query = userSearchInput.value;

                if (query.length > 1) {
                    fetch(`{{ route('search-users') }}?query=${query}`)
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
                                    console.log('Patient Data:', patientData);
                                    document.getElementById('user_id').value = patientData.user.id;
                                    document.getElementById('name').value = `${patientData.user.first_name} ${patientData.user.last_name}`;
                                    document.getElementById('age').value = calculateAge(patientData.user.dob);
                                    document.getElementById('gender').value = patientData.user.gender === 1 ? 'Male' : 'Female';
                                    document.getElementById('date_of_birth').value = patientData.user.dob || '';
                                    document.getElementById('vaccination_id').value = patientData.user.vaccination_id || '';
                                    document.getElementById('patient_contact').value = patientData.user.contact;
                                    document.getElementById('emergency_contact').value = `${patientData.user.emergency_contact_name}/${patientData.user.emergency_contact_no}`;
                                    document.getElementById('campus_id').value = patientData.user.campus_id;
                                    document.getElementById('college_id').value = patientData.user.college_id;
                                    document.getElementById('course_id').value = patientData.user.course_id;
                                    document.getElementById('year_level_id').value = patientData.user.year_level_id;

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