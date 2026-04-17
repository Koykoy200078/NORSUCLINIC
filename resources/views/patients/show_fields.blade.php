<div class="col-md-6 d-flex flex-column mb-md-10 mb-5">
    <label class="pb-2 fs-4 text-gray-600">{{ __('messages.patient.blood_type')  }}</label>
    <span class="fs-4 text-gray-800">{{ !empty($patient->user->blood_type) ? \App\Models\Patient::BLOOD_TYPE_ARRAY[$patient->user->blood_type] : __('messages.common.n/a') }}</span>
</div>
<div class="col-md-6 d-flex flex-column mb-md-10 mb-5">
    <label class="pb-2 fs-4 text-gray-600">{{ __('messages.user.gender')  }}</label>
    <span class="fs-4 text-gray-800">{{ ($patient->user->gender == 1) ? __('messages.doctor.male') : __('messages.doctor.female') }}</span>
</div>
<div class="col-md-6 d-flex flex-column mb-md-10 mb-5">
    <label class="pb-2 fs-4 text-gray-600">{{ __('messages.doctor.dob')  }}</label>
    <span class="fs-4 text-gray-800">{{ !empty($patient->user->dob) ? \Carbon\Carbon::parse($patient->user->dob)->isoFormat('DD MMM YYYY') : __('messages.common.n/a') }}</span>
</div>
<div class="col-md-6 d-flex flex-column mb-md-10 mb-5">
    <label class="pb-2 fs-4 text-gray-600">{{ __('messages.setting.address')  }}</label>
    <span class="fs-4 text-gray-800">{{ !empty($patient->address->address1) ? $patient->address->address1 : __('messages.common.n/a') }}</span>
</div>
<div class="col-md-6 d-flex flex-column mb-md-10 mb-5">
    <label class="pb-2 fs-4 text-gray-600">{{ __('University ID Number') }}</label>
    <span class="fs-4 text-gray-800">{{ !empty($patient->user->university_id_number) ? $patient->user->university_id_number : __('messages.common.n/a') }}</span>
</div>
<div class="col-md-6 d-flex flex-column mb-md-10 mb-5">
    <label class="pb-2 fs-4 text-gray-600">{{ __('Patient Type') }}</label>
    <span class="fs-4 text-gray-800">{{ !empty($patient->patientType) ? $patient->patientType->name : __('messages.common.n/a') }}</span>
</div>
<div class="col-md-6 d-flex flex-column mb-md-10 mb-5">
    <label class="pb-2 fs-4 text-gray-600">{{ __('Nationality/Citizenship') }}</label>
    <span class="fs-4 text-gray-800">{{ !empty($patient->user->nationality_citizenship) ? $patient->user->nationality_citizenship : __('messages.common.n/a') }}</span>
</div>
<div class="col-md-12 d-flex flex-column mb-md-10 mb-5">
    <label class="pb-2 fs-4 text-gray-600">{{ __('Immunization Record') }}</label>
    <span class="fs-4 text-gray-800">{{ !empty($patient->immunization_record) ? $patient->immunization_record : __('messages.common.n/a') }}</span>
</div>
<div class="col-md-6 d-flex flex-column mb-md-10 mb-5">
    <label class="pb-2 fs-4 text-gray-600">{{ __('Health Insurance Provider') }}</label>
    <span class="fs-4 text-gray-800">{{ !empty($patient->insuranceProvider) ? $patient->insuranceProvider->name : __('messages.common.n/a') }}</span>
</div>
<div class="col-md-6 d-flex flex-column mb-md-10 mb-5">
    <label class="pb-2 fs-4 text-gray-600">{{ __('Insurance Policy Number') }}</label>
    <span class="fs-4 text-gray-800">{{ !empty($patient->insurance_policy_number) ? $patient->insurance_policy_number : __('messages.common.n/a') }}</span>
</div>
<div class="col-md-6 d-flex flex-column mb-md-10 mb-5">
    <label class="pb-2 fs-4 text-gray-600">{{ __('Primary Care Physician (PCP)') }}</label>
    <span class="fs-4 text-gray-800">{{ !empty($patient->primary_care_physician_name) ? $patient->primary_care_physician_name : __('messages.common.n/a') }}</span>
</div>
<div class="col-md-6 d-flex flex-column mb-md-10 mb-5">
    <label class="pb-2 fs-4 text-gray-600">{{ __('PCP Contact') }}</label>
    <span class="fs-4 text-gray-800">{{ !empty($patient->primary_care_physician_contact) ? $patient->primary_care_physician_contact : __('messages.common.n/a') }}</span>
</div>
<div class="col-md-6 d-flex flex-column mb-md-10 mb-5">
    <label class="pb-2 fs-4 text-gray-600">{{ __('PCP Email') }}</label>
    <span class="fs-4 text-gray-800">{{ !empty($patient->primary_care_physician_email) ? $patient->primary_care_physician_email : __('messages.common.n/a') }}</span>
</div>
<div class="col-md-6 d-flex flex-column mb-md-10 mb-5">
    <label class="pb-2 fs-4 text-gray-600">{{ __('messages.patient.registered_on')  }}</label>
    <span class="fs-4 text-gray-800">{{$patient->user->created_at->diffForHumans()}}</span>
</div>
<div class="col-md-6 d-flex flex-column mb-md-10 mb-5">
    <label class="pb-2 fs-4 text-gray-600">{{ __('messages.patient.last_updated')  }}</label>
    <span class="fs-4 text-gray-800">{{$patient->user->updated_at->diffForHumans()}}</span>
</div>