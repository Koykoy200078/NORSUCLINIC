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

        $rules['university_id_number'] = 'required|string|max:100|unique:users,university_id_number';
        $rules['patient_type_id'] = 'required|exists:patient_types,id';
        $rules['nationality_citizenship'] = 'required|string|max:120';
        $rules['campus_address'] = 'required|string';
        $rules['permanent_address'] = 'required|string';
        $rules['immunization_record'] = 'required|string';

        return $rules;
    }

    public function messages(): array
    {
        return [
            'profile.max' => __('messages.profile_size'),
        ];
    }
}
