@extends('layouts.app')
@section('title')
{{__('messages.request.create_request')}}
@endsection
@section('content')
<div class="p-4">
    <div class="mb-4">
        <label class="block text-xs" for="user_search">Search User</label>
        <input type="text" id="user_search" class="w-full border-b border-black" placeholder="Search by name or email">
        <div id="user_search_results" class="absolute bg-white border border-gray-300 w-full hidden z-10"></div>
    </div>

    <form action="{{ route('request-documents.store') }}" method="POST">
        @csrf
        <div class="grid grid-cols-4 gap-2 pb-2">
            <div class="col-span-1">
                <label class="block text-xs" for="name">NAME</label>
                <input type="text" id="name" name="name" class="w-full border-b border-black" required>
            </div>
            <div class="col-span-1">
                <label class="block text-xs" for="age">AGE</label>
                <input type="text" id="age" name="age" class="w-full border-b border-black" required>
            </div>
            <div class="col-span-1">
                <label class="block text-xs" for="gender">GENDER</label>
                <input type="text" id="gender" name="gender" class="w-full border-b border-black" required>
            </div>
            <div class="col-span-1">
                <label class="block text-xs" for="status">STATUS</label>
                <input type="text" id="status" name="status" class="w-full border-b border-black" required>
            </div>
            <div class="col-span-1">
                <label class="block text-xs" for="date_of_birth">DATE OF BIRTH</label>
                <input type="date" id="date_of_birth" name="date_of_birth" class="w-full border-b border-black" required>
            </div>
            <div class="col-span-1">
                <label class="block text-xs" for="address">ADDRESS</label>
                <input type="text" id="address" name="address" class="w-full border-b border-black" required>
            </div>
            <div class="col-span-1">
                <label class="block text-xs" for="religion">RELIGION</label>
                <input type="text" id="religion" name="religion" class="w-full border-b border-black">
            </div>
            <div class="col-span-1">
                <label class="block text-xs" for="patient_contact">PATIENT'S CONTACT #</label>
                <input type="text" id="patient_contact" name="patient_contact" class="w-full border-b border-black">
            </div>
            <div class="col-span-1">
                <label class="block text-xs" for="campus">CAMPUS</label>
                {{ Form::select('campus', $data['campuses'], null, ['id' => 'campus', 'class' => 'w-full border-b border-black', 'placeholder' => 'Select Campus']) }}
            </div>
            <div class="col-span-1">
                <label class="block text-xs" for="college">COLLEGE</label>
                {{ Form::select('college', $data['colleges'], null, ['id' => 'college', 'class' => 'w-full border-b border-black', 'placeholder' => 'Select College']) }}
            </div>
            <div class="col-span-1">
                <label class="block text-xs" for="course_year">COURSE & YEAR</label>
                <div class="grid grid-cols-2 gap-2">
                    {{ Form::select('course', $data['courses'], null, ['id' => 'course', 'class' => 'w-full border-b border-black', 'placeholder' => 'Select Course']) }}
                    {{ Form::select('year_level', $data['year_levels'], null, ['id' => 'year_level', 'class' => 'w-full border-b border-black', 'placeholder' => 'Select Year Level']) }}
                </div>
            </div>
            <div class="col-span-1">
                <label class="block text-xs" for="informant">INFORMANT</label>
                <input type="text" id="informant" name="informant" class="w-full border-b border-black">
            </div>
            <div class="col-span-4">
                <label class="block text-xs" for="emergency_contact">CONTACT PERSON & NUMBER IN EMERGENCY</label>
                <input type="text" id="emergency_contact" name="emergency_contact" class="w-full border-b border-black">
            </div>
        </div>
        <div class="grid grid-cols-4 gap-2 py-2">
            <div class="col-span-1">
                <label class="block text-xs" for="requested_at">DATE</label>
                <input type="date" id="requested_at" name="requested_at" class="w-full border-b border-black">
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
                        <label class="block text-xs" for="covid_vaccination">COVID Vaccination</label>
                        {{ Form::select('vaccination_id', $data['vaccination_data'], null, ['id' => 'vaccination_id', 'class' => 'w-full border-b border-black', 'placeholder' => 'Select Vaccination Status']) }}
                    </div>
                    <div class="col-span-1">
                        <label class="block text-xs" for="comorbidities">Comorbidities:</label>
                        <input type="text" id="comorbidities" name="comorbidities" class="w-full border-b border-black">
                    </div>
                    <div class="col-span-1">
                        <label class="block text-xs" for="allergies">Allergies:</label>
                        <input type="text" id="allergies" name="allergies" class="w-full border-b border-black">
                    </div>
                    <div class="col-span-1">
                        <label class="block text-xs" for="admissions_surgeries">Pertinent Admissions or Surgeries:</label>
                        <input type="text" id="admissions_surgeries" name="admissions_surgeries" class="w-full border-b border-black">
                    </div>
                    <div class="col-span-1">
                        <label class="block text-xs" for="maintenance">Maintenance:</label>
                        <input type="text" id="maintenance" name="maintenance" class="w-full border-b border-black">
                    </div>
                    <div class="col-span-1">
                        <label class="block text-xs" for="pregnancy_status">Pregnant or Not?</label>
                        <input type="text" id="pregnancy_status" name="pregnancy_status" class="w-full border-b border-black">
                    </div>
                    <div class="col-span-1">
                        <label class="block text-xs" for="lmp_aog">If YES, LMP/AOG</label>
                        <input type="text" id="lmp_aog" name="lmp_aog" class="w-full border-b border-black">
                    </div>
                </div>
            </div>
        </div>
        <!-- Objective Data -->
        <div class="grid grid-cols-4 gap-2 py-2 border-t border-red-500">
            <div class="col-span-1">
                <label class="block text-red-500 font-bold">O</label>
                <label class="block text-xs">(Objective Data)</label>
            </div>
            <div class="col-span-3">
                <div class="grid grid-cols-6 gap-2">
                    <div class="col-span-1">
                        <label class="block text-xs" for="vital_signs_bp">BP</label>
                        <input type="text" id="vital_signs_bp" name="vital_signs_bp" class="w-full border-b border-black">
                    </div>
                    <div class="col-span-1">
                        <label class="block text-xs" for="vital_signs_pr">PR</label>
                        <input type="text" id="vital_signs_pr" name="vital_signs_pr" class="w-full border-b border-black">
                    </div>
                    <div class="col-span-1">
                        <label class="block text-xs" for="vital_signs_temp">Temp</label>
                        <input type="text" id="vital_signs_temp" name="vital_signs_temp" class="w-full border-b border-black">
                    </div>
                    <div class="col-span-1">
                        <label class="block text-xs" for="vital_signs_rr">RR</label>
                        <input type="text" id="vital_signs_rr" name="vital_signs_rr" class="w-full border-b border-black">
                    </div>
                    <div class="col-span-1">
                        <label class="block text-xs" for="vital_signs_o2_sat">O2 Sat</label>
                        <input type="text" id="vital_signs_o2_sat" name="vital_signs_o2_sat" class="w-full border-b border-black">
                    </div>
                    <div class="col-span-1">
                        <label class="block text-xs" for="vital_signs_weight">Wt</label>
                        <input type="text" id="vital_signs_weight" name="vital_signs_weight" class="w-full border-b border-black">
                    </div>
                    <div class="col-span-5">
                        <label class="block text-xs" for="pertinent_exam">PERTINENT EXAM</label>
                        <input type="text" id="pertinent_exam" name="pertinent_exam" class="w-full border-b border-black">
                    </div>
                </div>
            </div>
        </div>
        <!-- Assessment -->
        <div class="grid grid-cols-4 gap-2 py-2 border-t border-red-500">
            <div class="col-span-1">
                <label class="block text-red-500 font-bold">A</label>
                <label class="block text-xs">(Assessment)</label>
            </div>
            <div class="col-span-3">
                <textarea id="assessment" name="assessment" class="w-full border-b border-black" rows="5"></textarea>
            </div>
        </div>
        <!-- Plan -->
        <div class="grid grid-cols-4 gap-2 py-2 border-t border-red-500">
            <div class="col-span-1">
                <label class="block text-red-500 font-bold">P</label>
                <label class="block text-xs">(Plan)</label>
            </div>
            <div class="col-span-3">
                <textarea id="plan" name="plan" class="w-full border-b border-black" rows="5"></textarea>
            </div>
        </div>
        <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded mt-4">
            Submit
        </button>
    </form>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const userSearchInput = document.getElementById('user_search');
        const userSearchResults = document.getElementById('user_search_results');

        userSearchInput.addEventListener('input', function() {
            const query = userSearchInput.value;

            if (query.length > 1) {
                fetch(`{{ route('search-users') }}?query=${query}`)
                    .then(response => response.json())
                    .then(data => {
                        userSearchResults.innerHTML = '';
                        userSearchResults.classList.remove('hidden');

                        data.forEach(user => {
                            const option = document.createElement('div');
                            option.classList.add('p-2', 'cursor-pointer', 'hover:bg-gray-200');
                            option.textContent = `${user.first_name} ${user.last_name}`;
                            option.dataset.user = JSON.stringify(user);

                            option.addEventListener('click', function() {
                                const userData = JSON.parse(this.dataset.user);
                                console.log(userData);

                                // Fill out the form fields
                                document.getElementById('name').value = `${userData.first_name} ${userData.last_name}`;
                                document.getElementById('age').value = calculateAge(userData.dob); // Ensure format is YYYY-MM-DD Ex. 2000-10-07
                                document.getElementById('gender').value = userData.gender === 1 ? 'Male' : "Female";
                                document.getElementById('date_of_birth').value = userData.dob || '';
                                document.getElementById('patient_contact').value = userData.contact;
                                document.getElementById('campus').value = userData.campus_id;
                                document.getElementById('college').value = userData.college_id;
                                document.getElementById('course').value = userData.course_id;
                                document.getElementById('year_level').value = userData.year_level_id;
                                document.getElementById('vaccination_id').value = userData.vaccination_id;

                                userSearchResults.classList.add('hidden');
                            });

                            userSearchResults.appendChild(option);
                        });
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

        function calculateAge(dob) {
            if (!dob) return ''; // Return an empty string if dob is undefined or null
            const birthDate = new Date(dob);
            if (isNaN(birthDate)) return ''; // Return an empty string if dob is invalid
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
@endsection