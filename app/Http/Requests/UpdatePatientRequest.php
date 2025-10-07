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
            $rules['patient_unique_id'] = 'required|regex:/^\S*$/u|unique:patients,patient_unique_id,' . $patient->id;
            $rules['email'] = 'nullable|email:filter|unique:users,email,' . $patient->user_id;
            $rules['contact'] = 'nullable|unique:users,contact,' . $patient->user_id;
        } else {
            // Fallback - should not reach here if route model binding works
            Log::error('UpdatePatientRequest: Patient parameter is NULL or not instance of Patient', [
                'route_name' => $this->route()->getName(),
                'parameters' => $this->route()->parameters(),
            ]);
            $rules['patient_unique_id'] = 'required|regex:/^\S*$/u';
            $rules['email'] = 'nullable|email:filter';
            $rules['contact'] = 'nullable';
        }

        $rules['postal_code'] = 'nullable';
        $rules['profile'] = 'nullable|mimes:jpeg,jpg,png|max:2000';

        return $rules;
    }

    public function messages(): array
    {
        return [
            'patient_unique_id.regex' => __('messages.common.space_not_allowed_in_unique_id_field'),
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
