@php
    $isEdit = isset($prescription);
    $currentUserDoctorId = optional(optional(getLogInUser())->doctor)->id;
    $selectedDoctorId = old('doctor_id', $isEdit ? $prescription->doctor_id : $currentUserDoctorId);

    $doctorMetaArray = $doctorMeta instanceof \Illuminate\Support\Collection ? $doctorMeta->toArray() : (array) $doctorMeta;
    $initialDoctorLicense = old(
        'doctor_license_s2_number',
        $isEdit
            ? ($prescription->doctor_license_s2_number ?: ($doctorMetaArray[$selectedDoctorId]['license'] ?? ''))
            : ($doctorMetaArray[$selectedDoctorId]['license'] ?? '')
    );

    $consultationDateSource = $isEdit ? $prescription->consultation_date : ($patientSummary['consultation_date'] ?? now());
    $consultationDateValue = old(
        'consultation_date',
        $consultationDateSource ? \Carbon\Carbon::parse($consultationDateSource)->format('Y-m-d') : now()->format('Y-m-d')
    );

    $initialMedicineRows = old('medicines', $medicineRows ?? []);
    if (empty($initialMedicineRows)) {
        $initialMedicineRows = [[
            'medicine_id' => '',
            'dosage' => '',
            'route_of_administration' => \App\Models\Prescription::ROUTE_ORAL,
            'frequency' => 1,
            'duration_value' => 1,
            'duration_unit' => \App\Models\Prescription::DURATION_UNIT_DAY,
            'total_quantity' => 1,
            'instructions' => '',
        ]];
    }

    $medicineCatalog = collect($medicineOptions)
        ->map(function ($medicine) {
            return [
                'id' => (int) $medicine->id,
                'name' => $medicine->name,
                'available_quantity' => (int) ($medicine->available_quantity ?? 0),
            ];
        })
        ->values()
        ->toArray();

    $routeOptions = \App\Models\Prescription::ROUTE_OPTIONS;
    $durationOptions = \App\Models\Prescription::DURATION_UNIT_OPTIONS;

    $patientFullName = trim(optional($patient->user)->first_name . ' ' . optional($patient->user)->last_name);
    $patientUniversityId = optional($patient->user)->university_id_number;

    $weightValue = old('weight_kg', $isEdit ? $prescription->weight_kg : ($patientSummary['weight_kg'] ?? null));
    $pulseValue = old('pulse_rate', $isEdit ? $prescription->pulse_rate : ($patientSummary['pulse_rate'] ?? null));
    $temperatureValue = old('body_temperature', $isEdit ? $prescription->body_temperature : ($patientSummary['body_temperature'] ?? null));
    $bloodPressureValue = old('blood_pressure', $isEdit ? $prescription->blood_pressure : ($patientSummary['blood_pressure'] ?? null));
    $heightValue = old('height_cm', $isEdit ? $prescription->height_cm : ($patientSummary['height_cm'] ?? null));

    $storeRoute = isRole('doctor')
        ? 'doctors.prescriptions.store'
        : (isRole('staff') ? 'staff.prescriptions.store' : (isRole('patient') ? 'patients.prescriptions.store' : 'prescriptions.store'));

    $updateRoute = isRole('doctor')
        ? 'doctors.prescriptions.update'
        : (isRole('staff') ? 'staff.prescriptions.update' : (isRole('patient') ? 'patients.prescriptions.update' : 'prescriptions.update'));

    $formRoute = $isEdit ? [$updateRoute, $prescription->id] : $storeRoute;
    $formMethod = $isEdit ? 'patch' : 'post';
@endphp

<div
    x-data="prescriptionForm(@js([
        'doctorMeta' => $doctorMetaArray,
        'selectedDoctorId' => (string) $selectedDoctorId,
        'initialDoctorLicense' => (string) $initialDoctorLicense,
        'initialRows' => $initialMedicineRows,
        'medicineCatalog' => $medicineCatalog,
        'routeOptions' => $routeOptions,
        'durationOptions' => $durationOptions,
    ]))"
    x-init="init()"
>
    {{ Form::model($isEdit ? $prescription : null, ['route' => $formRoute, 'method' => $formMethod, 'id' => $isEdit ? 'editPrescription' : 'createPrescription']) }}

    <input type="hidden" name="patient_id" value="{{ $patient->id }}">

    <div class="card mb-5">
        <div class="card-header">
            <h3 class="card-title mb-0">Patient Summary</h3>
        </div>
        <div class="card-body">
            <div class="row g-4">
                <div class="col-md-4">
                    <label class="form-label">Patient Name</label>
                    <input type="text" class="form-control" value="{{ $patientFullName ?: 'N/A' }}" readonly>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Patient Number</label>
                    <input type="text" class="form-control" value="{{ $patientUniversityId ?: 'N/A' }}" readonly>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Patient Record ID</label>
                    <input type="text" class="form-control" value="{{ $patient->id }}" readonly>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Allergies</label>
                    <textarea class="form-control" rows="2" readonly>{{ $patientSummary['allergies'] ?: 'No data provided' }}</textarea>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Chronic Conditions / Comorbidities</label>
                    <textarea class="form-control" rows="2" readonly>{{ $patientSummary['comorbidities'] ?: 'No data provided' }}</textarea>
                </div>
                <div class="col-md-12">
                    <label class="form-label">Maintenance Medications</label>
                    <textarea class="form-control" rows="2" readonly>{{ $patientSummary['maintenance'] ?: 'No data provided' }}</textarea>
                </div>
            </div>
        </div>
    </div>

    <div class="card mb-5">
        <div class="card-header">
            <h3 class="card-title mb-0">Consultation Details</h3>
        </div>
        <div class="card-body">
            <div class="row g-4">
                <div class="col-md-4">
                    <label for="doctor_id" class="form-label required">Attending Doctor</label>
                    <select
                        id="doctor_id"
                        name="doctor_id"
                        class="form-select @error('doctor_id') is-invalid @enderror"
                        x-model="selectedDoctorId"
                        @change="syncDoctorLicense"
                        required
                    >
                        <option value="">Select doctor</option>
                        @foreach ($doctors as $doctorId => $doctorName)
                            <option value="{{ $doctorId }}">{{ $doctorName }}</option>
                        @endforeach
                    </select>
                    @error('doctor_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-4">
                    <label for="doctor_license_s2_number" class="form-label">Doctor License / S2</label>
                    <input
                        type="text"
                        id="doctor_license_s2_number"
                        name="doctor_license_s2_number"
                        class="form-control @error('doctor_license_s2_number') is-invalid @enderror"
                        x-model="doctorLicense"
                        readonly
                    >
                    @error('doctor_license_s2_number')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-4">
                    <label for="consultation_date" class="form-label required">Consultation Date</label>
                    <input
                        type="date"
                        id="consultation_date"
                        name="consultation_date"
                        class="form-control @error('consultation_date') is-invalid @enderror"
                        value="{{ $consultationDateValue }}"
                        required
                    >
                    @error('consultation_date')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-6">
                    <label for="icd10_diagnosis_id" class="form-label">ICD-10 Diagnosis</label>
                    <select id="icd10_diagnosis_id" name="icd10_diagnosis_id" class="form-select @error('icd10_diagnosis_id') is-invalid @enderror">
                        <option value="">Select ICD-10 diagnosis</option>
                        @foreach ($diagnosisOptions as $diagnosisId => $diagnosisLabel)
                            <option
                                value="{{ $diagnosisId }}"
                                {{ (string) old('icd10_diagnosis_id', $isEdit ? $prescription->icd10_diagnosis_id : '') === (string) $diagnosisId ? 'selected' : '' }}
                            >
                                {{ $diagnosisLabel }}
                            </option>
                        @endforeach
                    </select>
                    @error('icd10_diagnosis_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-6">
                    <label for="next_visit_days" class="form-label">Next Visit (days)</label>
                    <input
                        type="number"
                        id="next_visit_days"
                        name="next_visit_days"
                        min="0"
                        class="form-control @error('next_visit_days') is-invalid @enderror"
                        value="{{ old('next_visit_days', $isEdit ? $prescription->next_visit_days : null) }}"
                        placeholder="e.g. 7"
                    >
                    @error('next_visit_days')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-12">
                    <label for="problem_description" class="form-label">Problem / Chief Complaint</label>
                    <textarea
                        id="problem_description"
                        name="problem_description"
                        class="form-control @error('problem_description') is-invalid @enderror"
                        rows="3"
                        placeholder="Describe the patient concern"
                    >{{ old('problem_description', $isEdit ? $prescription->problem_description : null) }}</textarea>
                    @error('problem_description')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-12">
                    <label for="advice" class="form-label">Advice / Plan</label>
                    <textarea
                        id="advice"
                        name="advice"
                        class="form-control @error('advice') is-invalid @enderror"
                        rows="3"
                        placeholder="Doctor advice and reminders"
                    >{{ old('advice', $isEdit ? $prescription->advice : null) }}</textarea>
                    @error('advice')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
        </div>
    </div>

    <div class="card mb-5">
        <div class="card-header d-flex align-items-center justify-content-between">
            <h3 class="card-title mb-0">Medicines</h3>
            <button type="button" class="btn btn-primary" @click="addRow">Add Medicine Row</button>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table align-middle">
                    <thead>
                        <tr>
                            <th style="min-width: 210px;">Medicine</th>
                            <th style="min-width: 140px;">Dosage</th>
                            <th style="min-width: 150px;">Route</th>
                            <th style="min-width: 110px;">Frequency / day</th>
                            <th style="min-width: 110px;">Duration</th>
                            <th style="min-width: 120px;">Duration Unit</th>
                            <th style="min-width: 120px;">Total Qty</th>
                            <th style="min-width: 200px;">Instructions</th>
                            <th style="min-width: 70px;">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <template x-for="(row, index) in medicines" :key="index">
                            <tr>
                                <td>
                                    <select
                                        class="form-select"
                                        :name="'medicines[' + index + '][medicine_id]'"
                                        x-model="row.medicine_id"
                                        @change="onMedicineChanged(index)"
                                        required
                                    >
                                        <option value="">Select medicine</option>
                                        <template x-for="medicine in medicineCatalog" :key="medicine.id">
                                            <option
                                                :value="medicine.id"
                                                :disabled="isSelectedElsewhere(medicine.id, index)"
                                                x-text="medicine.name + ' (Stock: ' + medicine.available_quantity + ')'"
                                            ></option>
                                        </template>
                                    </select>
                                    <div class="text-danger fs-7 mt-1" x-show="isDuplicate(index)">Duplicate medicine is not allowed.</div>
                                    <div class="text-muted fs-8 mt-1" x-show="stockFor(row.medicine_id) !== null">
                                        Available stock: <span x-text="stockFor(row.medicine_id)"></span>
                                    </div>
                                </td>
                                <td>
                                    <input
                                        type="text"
                                        class="form-control"
                                        :name="'medicines[' + index + '][dosage]'"
                                        x-model="row.dosage"
                                        placeholder="e.g. 1 tablet"
                                        required
                                    >
                                </td>
                                <td>
                                    <select
                                        class="form-select"
                                        :name="'medicines[' + index + '][route_of_administration]'"
                                        x-model="row.route_of_administration"
                                        required
                                    >
                                        <template x-for="(label, value) in routeOptions" :key="value">
                                            <option :value="value" x-text="label"></option>
                                        </template>
                                    </select>
                                </td>
                                <td>
                                    <input
                                        type="number"
                                        class="form-control"
                                        :name="'medicines[' + index + '][frequency]'"
                                        x-model.number="row.frequency"
                                        min="1"
                                        max="24"
                                        @input="recalculateRow(index)"
                                        required
                                    >
                                </td>
                                <td>
                                    <input
                                        type="number"
                                        class="form-control"
                                        :name="'medicines[' + index + '][duration_value]'"
                                        x-model.number="row.duration_value"
                                        min="1"
                                        max="365"
                                        @input="recalculateRow(index)"
                                        required
                                    >
                                </td>
                                <td>
                                    <select
                                        class="form-select"
                                        :name="'medicines[' + index + '][duration_unit]'"
                                        x-model="row.duration_unit"
                                        @change="recalculateRow(index)"
                                        required
                                    >
                                        <template x-for="(label, value) in durationOptions" :key="value">
                                            <option :value="value" x-text="label"></option>
                                        </template>
                                    </select>
                                </td>
                                <td>
                                    <input
                                        type="number"
                                        class="form-control"
                                        :name="'medicines[' + index + '][total_quantity]'"
                                        x-model.number="row.total_quantity"
                                        min="1"
                                        readonly
                                        required
                                    >
                                </td>
                                <td>
                                    <textarea
                                        class="form-control"
                                        rows="2"
                                        :name="'medicines[' + index + '][instructions]'"
                                        x-model="row.instructions"
                                        placeholder="Optional instruction"
                                    ></textarea>
                                </td>
                                <td>
                                    <button
                                        type="button"
                                        class="btn btn-sm btn-light-danger"
                                        @click="removeRow(index)"
                                        :disabled="medicines.length === 1"
                                    >
                                        Remove
                                    </button>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>
            @error('medicines')
                <div class="text-danger mt-2">{{ $message }}</div>
            @enderror
        </div>
    </div>

    <div class="card mb-5">
        <div class="card-header">
            <h3 class="card-title mb-0">Vitals Snapshot</h3>
        </div>
        <div class="card-body">
            <div class="row g-4">
                <div class="col-md-3">
                    <label for="weight_kg" class="form-label">Weight (kg)</label>
                    <input type="number" step="0.01" min="0" id="weight_kg" name="weight_kg" class="form-control @error('weight_kg') is-invalid @enderror" value="{{ $weightValue }}">
                    @error('weight_kg')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-3">
                    <label for="pulse_rate" class="form-label">Pulse Rate</label>
                    <input type="text" id="pulse_rate" name="pulse_rate" class="form-control @error('pulse_rate') is-invalid @enderror" value="{{ $pulseValue }}" placeholder="e.g. 72 bpm">
                    @error('pulse_rate')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-3">
                    <label for="body_temperature" class="form-label">Temperature (C)</label>
                    <input type="number" step="0.1" min="20" max="50" id="body_temperature" name="body_temperature" class="form-control @error('body_temperature') is-invalid @enderror" value="{{ $temperatureValue }}">
                    @error('body_temperature')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-3">
                    <label for="blood_pressure" class="form-label">Blood Pressure</label>
                    <input type="text" id="blood_pressure" name="blood_pressure" class="form-control @error('blood_pressure') is-invalid @enderror" value="{{ $bloodPressureValue }}" placeholder="e.g. 120/80">
                    @error('blood_pressure')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-3">
                    <label for="height_cm" class="form-label">Height (cm)</label>
                    <input type="number" step="0.01" min="0" id="height_cm" name="height_cm" class="form-control @error('height_cm') is-invalid @enderror" value="{{ $heightValue }}">
                    @error('height_cm')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
        </div>
    </div>

    <div class="d-flex justify-content-end gap-3">
        <a href="{{ url()->previous() }}" class="btn btn-outline-primary">{{ __('messages.common.cancel') }}</a>
        <button type="submit" class="btn btn-primary btnPrescriptionSave">{{ $isEdit ? __('messages.common.save') : __('messages.common.save') }}</button>
    </div>

    {{ Form::close() }}
</div>

<script>
    function prescriptionForm(config) {
        return {
            doctorMeta: config.doctorMeta || {},
            selectedDoctorId: config.selectedDoctorId || '',
            doctorLicense: config.initialDoctorLicense || '',
            medicineCatalog: config.medicineCatalog || [],
            routeOptions: config.routeOptions || {},
            durationOptions: config.durationOptions || {},
            medicines: Array.isArray(config.initialRows) ? config.initialRows : [],

            init() {
                if (!this.medicines.length) {
                    this.medicines = [this.emptyRow()];
                }

                this.medicines = this.medicines.map((row) => this.normalizeRow(row));
                this.syncDoctorLicense();
                this.medicines.forEach((_, index) => this.recalculateRow(index));
            },

            emptyRow() {
                return {
                    medicine_id: '',
                    dosage: '',
                    route_of_administration: 'oral',
                    frequency: 1,
                    duration_value: 1,
                    duration_unit: 'day',
                    total_quantity: 1,
                    instructions: '',
                };
            },

            normalizeRow(row) {
                return {
                    medicine_id: row.medicine_id ? String(row.medicine_id) : '',
                    dosage: row.dosage || '',
                    route_of_administration: row.route_of_administration || 'oral',
                    frequency: Number(row.frequency || 1),
                    duration_value: Number(row.duration_value || 1),
                    duration_unit: row.duration_unit || 'day',
                    total_quantity: Number(row.total_quantity || 1),
                    instructions: row.instructions || '',
                };
            },

            syncDoctorLicense() {
                const meta = this.doctorMeta[this.selectedDoctorId];
                this.doctorLicense = meta && meta.license ? meta.license : '';
            },

            addRow() {
                this.medicines.push(this.emptyRow());
            },

            removeRow(index) {
                if (this.medicines.length === 1) {
                    return;
                }

                this.medicines.splice(index, 1);
            },

            daysFor(row) {
                const durationValue = Math.max(1, Number(row.duration_value || 1));

                if (row.duration_unit === 'week') {
                    return durationValue * 7;
                }

                if (row.duration_unit === 'month') {
                    return durationValue * 30;
                }

                return durationValue;
            },

            recalculateRow(index) {
                const row = this.medicines[index];
                const frequency = Math.max(1, Number(row.frequency || 1));
                const days = this.daysFor(row);
                row.total_quantity = Math.max(1, frequency * days);
            },

            onMedicineChanged(index) {
                this.recalculateRow(index);
            },

            stockFor(medicineId) {
                const selected = this.medicineCatalog.find((medicine) => String(medicine.id) === String(medicineId));
                return selected ? selected.available_quantity : null;
            },

            isSelectedElsewhere(medicineId, index) {
                return this.medicines.some((row, rowIndex) => rowIndex !== index && String(row.medicine_id) === String(medicineId));
            },

            isDuplicate(index) {
                const current = this.medicines[index];
                if (!current || !current.medicine_id) {
                    return false;
                }

                return this.medicines.filter((row) => String(row.medicine_id) === String(current.medicine_id)).length > 1;
            },
        };
    }
</script>
