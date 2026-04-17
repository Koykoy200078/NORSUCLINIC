<?php

namespace App\Http\Requests;

use App\Models\Patient;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Log;

class UpdatePatientRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
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
            $rules['university_id_number'] = 'required|string|max:100|unique:users,university_id_number,' . $patient->user_id;
            $rules['contact'] = 'nullable';
        } else {
            // Fallback - should not reach here if route model binding works
            Log::error('UpdatePatientRequest: Patient parameter is NULL or not instance of Patient', [
                'route_name' => $this->route()->getName(),
                'parameters' => $this->route()->parameters(),
            ]);
            $rules['email'] = 'nullable|email:filter';
            $rules['university_id_number'] = 'required|string|max:100';
            $rules['contact'] = 'nullable';
        }

        $rules['patient_type_id'] = 'required|exists:patient_types,id';
        $rules['nationality_citizenship'] = 'required|string|max:120';
        $rules['immunization_record'] = 'required|string';
        $rules['postal_code'] = 'nullable';
        $rules['profile'] = 'nullable|mimes:jpeg,jpg,png|max:2000';

        return $rules;
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
