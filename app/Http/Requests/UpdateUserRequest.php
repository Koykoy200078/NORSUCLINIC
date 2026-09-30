<?php

namespace App\Http\Requests;

use App\Http\Requests\Concerns\ChecksArchivedAccounts;
use Illuminate\Foundation\Http\FormRequest;

class UpdateUserRequest extends FormRequest
{
    use ChecksArchivedAccounts;

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
        return [
            'first_name' => 'required',
            'last_name' => 'required',
            'email' => ['required', 'email', 'regex:/(.*)@(.*)\.(.*)/', $this->uniqueAmongActiveUsers('email', (int) $this->route('doctor')->user_id)],
            'employee_id' => ['required', 'string', 'max:100', $this->uniqueAmongActiveUsers('employee_id', (int) $this->route('doctor')->user_id)],
            'contact' => ['nullable', $this->uniqueAmongActiveUsers('contact', (int) $this->route('doctor')->user_id)],
            'dob' => 'nullable|date|before_or_equal:today',
            'experience' => 'nullable|numeric',
            'prc_license_number' => 'required|string|max:100|unique:doctors,prc_license_number,' . $this->route('doctor')->id,
            'ptr_number' => 'required|string|max:100',
            's2_license_number' => 'nullable|string|max:100',
            'consultation_hours' => 'required|string',
            'specializations' => 'required',
            'gender' => 'required',
            'status' => 'nullable',
            'postal_code' => 'nullable',
            'profile' => 'mimes:jpeg,jpg,png|max:2000',
        ];
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
