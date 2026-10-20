<?php

namespace App\Http\Requests;

use App\Http\Requests\Concerns\ChecksArchivedAccounts;
use App\Http\Requests\Concerns\DoctorFormRules;
use App\Http\Requests\Concerns\NormalizesPhilippinePhone;
use App\Rules\PhilippinePhoneNumber;
use Illuminate\Foundation\Http\FormRequest;

class UpdateUserRequest extends FormRequest
{
    use ChecksArchivedAccounts;
    use DoctorFormRules;
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
        $this->normalizePhilippinePhones(['contact' => 'national']);
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return array_merge([
            'first_name' => 'required|string|max:191',
            'last_name' => 'required|string|max:191',
            'email' => ['required', 'email', 'max:191', 'regex:/(.*)@(.*)\.(.*)/', $this->uniqueAmongActiveUsers('email', (int) $this->route('doctor')->user_id)],
            'employee_id' => ['required', 'string', 'max:100', $this->uniqueAmongActiveUsers('employee_id', (int) $this->route('doctor')->user_id)],
            'contact' => ['nullable', new PhilippinePhoneNumber(), $this->uniqueAmongActiveUsers('contact', (int) $this->route('doctor')->user_id)],
            'dob' => 'nullable|date|before_or_equal:today',
            'prc_license_number' => 'required|string|max:100|unique:doctors,prc_license_number,' . $this->route('doctor')->id,
            'ptr_number' => 'required|string|max:100',
            's2_license_number' => 'nullable|string|max:100',
            'consultation_hours' => 'required|string|max:10000',
            'status' => 'nullable',
            'profile' => 'mimes:jpeg,jpg,png|max:2000',
        ], $this->doctorFormRules());
    }

    /**
     * @return string[]
     */
    public function messages(): array
    {
        return [
            'profile.max' => __('messages.profile_size'),
        ];
    }

    public function withValidator($validator): void
    {
        $this->validateArchivedAccountConflicts($validator, [
            'email' => 'email',
            'employee_id' => 'employee_id',
            'contact' => 'contact',
        ], (int) $this->route('doctor')->user_id);
    }
}
