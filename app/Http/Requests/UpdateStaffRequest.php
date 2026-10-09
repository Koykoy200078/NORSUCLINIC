<?php

namespace App\Http\Requests;

use App\Http\Requests\Concerns\ChecksArchivedAccounts;
use App\Http\Requests\Concerns\NormalizesPhilippinePhone;
use App\Rules\PhilippinePhoneNumber;
use App\Support\PhilippinePhone;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateStaffRequest extends FormRequest
{
    use ChecksArchivedAccounts;
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
        return [
            'first_name' => 'required',
            'last_name' => 'required',
            'email' => ['required', 'email:filter', $this->uniqueAmongActiveUsers('email', (int) $this->route('staff')->id)],
            'employee_id' => ['required', 'string', 'max:100', $this->uniqueAmongActiveUsers('employee_id', (int) $this->route('staff')->id)],
            'country_code' => ['nullable', 'in:'.PhilippinePhone::COUNTRY_CODE],
            'contact' => ['nullable', new PhilippinePhoneNumber(), $this->uniqueAmongActiveUsers('contact', (int) $this->route('staff')->id)],
            'password' => 'nullable|same:password_confirmation|min:6',
            'gender' => 'required',
            'role' => ['sometimes', 'nullable', 'integer', Rule::exists('roles', 'id')->where('name', 'staff')],
            'profile' => 'nullable|mimes:jpeg,jpg,png|max:2000',
        ];
    }

    public function withValidator($validator): void
    {
        $this->validateArchivedAccountConflicts($validator, [
            'email' => 'email',
            'employee_id' => 'employee_id',
            'contact' => 'contact',
        ], (int) $this->route('staff')->id);
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
}
