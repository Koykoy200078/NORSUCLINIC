<div>
    @if($user->type != 3)
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
                    <input type="text" id="name_2" name="name" style="width: 400px; text-align: center;" class="border-b border-black" value="{{ $user->type == 3 ? $user->first_name . ' ' . $user->last_name : '' }}" readonly required>,
                    <input type="text" id="age_2" name="age" style="width: 70px; text-align: center;" class="border-b border-black" value="{{ $user->type == 3 ? \Carbon\Carbon::parse($user->dob)->age : '' }}" readonly required> yrs old,
                    <input type="text" id="gender_2" name="gender" style="width: 70px; text-align: center;" class="border-b border-black" value="{{ $user->type == 3 ? ($user->gender == 1 ? 'Male' : 'Female') : '' }}" readonly required> a resident of
                </p>
                <p>
                    <input type="text" id="address_2" name="address" style="width: 470px; text-align: center;" class="border-b border-black" value="{{ $user->type == 3 && $patient->address ? $patient->address->address1 : '' }}" {{ $user->type == 3 ? 'readonly' : '' }} required>
                    , was seen and examined at my clinic on <input type="date" id="examined_on" name="examined_on" style="width: 120px; text-align: center;" class="border-b border-black" value="{{ date('Y-m-d') }}" max="{{ date('Y-m-d') }}" required> with the following
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
<style>
    #complaints_diagnosis,
    #medical_cert_remarks {
        resize: none;
    }
</style>
<script>
    document.addEventListener('DOMContentLoaded', function() {
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
    });
</script>