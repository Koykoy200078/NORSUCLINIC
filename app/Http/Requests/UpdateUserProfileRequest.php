<?php

namespace App\Http\Requests;

use App\Http\Requests\Concerns\NormalizesPhilippinePhone;
use App\Rules\PhilippinePhoneNumber;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class UpdateUserProfileRequest extends FormRequest
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
        $id = Auth::id();

        return [
            'first_name' => 'required|string|max:191',
            'middle_name' => 'nullable|string|max:191',
            'last_name' => 'required|string|max:191',
            'email' => 'required|email|max:191|unique:users,email,' . $id . '|regex:/^[a-zA-Z0-9._-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/i',
            'contact' => ['nullable', 'string', new PhilippinePhoneNumber()],
            'country_code' => 'nullable|string|max:191',
            'emergency_contact_name' => 'nullable|string|max:191',
            'emergency_contact_no' => ['nullable', 'string', 'max:191', new PhilippinePhoneNumber()],
            'time_zone' => 'required|string|max:191',
            'gender' => 'nullable|integer|in:1,2',
            'dob' => 'nullable|date|before_or_equal:today',
            'blood_type' => 'nullable|string|max:191',
            'vaccination_id' => 'nullable|integer|exists:vaccinations,id',
            'address1' => 'nullable|string|max:191',
            'address2' => 'nullable|string|max:191',
            'country_id' => 'nullable|integer|exists:countries,id',
            'state_id' => 'nullable|integer',
            'city_id' => 'nullable|integer',
            'postal_code' => 'nullable|string|max:20',
            'campus_id' => 'nullable|integer|exists:campuses,id',
            'college_id' => 'nullable|integer|exists:colleges,id',
            'course_id' => 'nullable|integer|exists:courses,id',
            'year_level_id' => 'nullable|integer|exists:year_levels,id',
            'patient_type_id' => 'nullable|integer|exists:patient_types,id',
            'department_id' => 'nullable|integer',
            'office_id' => 'nullable|integer',
            'image' => 'nullable|mimes:jpeg,jpg,png|max:2000',
        ];
    }

    public function messages(): array
    {
        return [
            'contact.required' => __('messages.contact_required'),
            'image.max' => __('messages.avatar_size'),
        ];
    }

    /**
     * Handle a failed validation attempt.
     */
    protected function failedValidation(\Illuminate\Contracts\Validation\Validator $validator)
    {
        Log::error('Profile Update Validation Failed:', [
            'errors' => $validator->errors()->toArray(),
            'input' => $this->except(['password', 'image'])
        ]);

        parent::failedValidation($validator);
    }
}
