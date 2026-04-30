<?php

namespace App\Http\Requests;

use App\Models\Patient;
use Illuminate\Foundation\Http\FormRequest;

class CreatePatientRequest extends FormRequest
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
        $rules = Patient::$rules;

        // Guest (id=4) does not require university_id_number; Student, Staff, Faculty do.
        $rules['university_id_number'] = 'nullable|string|max:100|unique:users,university_id_number';
        $rules['patient_type_id'] = 'required|exists:patient_types,id';
        $rules['nationality_citizenship'] = 'required|string|max:120';
        $rules['immunization_record'] = 'required|string';
        $rules['postal_code'] = 'nullable|numeric';

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
}
