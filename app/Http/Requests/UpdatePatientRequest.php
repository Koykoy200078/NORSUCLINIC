<?php

namespace App\Http\Requests;

use App\Http\Requests\Concerns\NormalizesPhilippinePhone;
use App\Models\Patient;
use App\Rules\PhilippinePhoneNumber;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Log;

class UpdatePatientRequest extends FormRequest
{
    use NormalizesPhilippinePhone;

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->normalizePhilippinePhones(['contact' => 'national', 'emergency_contact_no' => 'e164']);
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        // Get the patient instance from route model binding
        // This works for all routes: admin, staff, and doctor
        $patient = $this->route()->parameter('patient');

        $rules = Patient::$editRules;

        if ($patient instanceof Patient) {
            $rules['email'] = 'nullable|email:filter|unique:users,email,' . $patient->user_id;
            $rules['university_id_number'] = 'nullable|string|max:100|unique:users,university_id_number,' . $patient->user_id;
        } else {
            // Fallback - should not reach here if route model binding works
            Log::error('UpdatePatientRequest: Patient parameter is NULL or not instance of Patient', [
                'route_name' => $this->route()->getName(),
                'parameters' => $this->route()->parameters(),
            ]);
            $rules['email'] = 'nullable|email:filter';
            $rules['university_id_number'] = 'nullable|string|max:100';
        }

        $rules['contact'] = ['nullable', new PhilippinePhoneNumber()];
        $rules['emergency_contact_no'] = ['nullable', new PhilippinePhoneNumber()];

        $rules['patient_type_id'] = 'required|exists:patient_types,id';
        $rules['nationality_citizenship'] = 'required|string|max:120';
        $rules['immunization_record'] = 'required|string';
        $rules['postal_code'] = 'nullable|numeric';
        $rules['profile'] = 'nullable|mimes:jpeg,jpg,png|max:2000';

        return $rules;
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $patientTypeId = $this->input('patient_type_id');
            $universityId = $this->input('university_id_number');

            // Guest (id=4) is exempt; all other types require university_id_number
            if ($patientTypeId != '4' && (empty($universityId) || trim($universityId) === '')) {
                $validator->errors()->add('university_id_number', 'The university id number field is required.');
            }
        });
    }

    public function messages(): array
    {
        return [
            'profile.max' => __('messages.profile_size'),
        ];
    }

    /**
     * Handle a failed validation attempt.
     */
    protected function failedValidation(\Illuminate\Contracts\Validation\Validator $validator)
    {
        Log::error('UpdatePatientRequest validation failed', [
            'route_name' => $this->route()->getName(),
            'errors' => $validator->errors()->toArray(),
            'input' => $this->except(['profile', 'password']),
        ]);

        parent::failedValidation($validator);
    }
}
