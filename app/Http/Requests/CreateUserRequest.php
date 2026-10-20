<?php

namespace App\Http\Requests;

use App\Http\Requests\Concerns\ChecksArchivedAccounts;
use App\Http\Requests\Concerns\DoctorFormRules;
use App\Http\Requests\Concerns\NormalizesPhilippinePhone;
use App\Rules\PhilippinePhoneNumber;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;

class CreateUserRequest extends FormRequest
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
        $rules = array_merge(User::$rules, $this->doctorFormRules());

        // A doctor signs in with the e-mail address and the password: the account cannot work without either.
        $rules['email'] = ['required', 'email', 'max:191', 'regex:/(.*)@(.*)\.(.*)/', $this->uniqueAmongActiveUsers('email')];
        $rules['password'] = 'required|same:password_confirmation|min:6';
        $rules['contact'] = ['nullable', new PhilippinePhoneNumber(), $this->uniqueAmongActiveUsers('contact')];
        $rules['employee_id'] = ['required', 'string', 'max:100', $this->uniqueAmongActiveUsers('employee_id')];
        $rules['prc_license_number'] = 'required|string|max:100|unique:doctors,prc_license_number';
        $rules['ptr_number'] = 'required|string|max:100';
        $rules['s2_license_number'] = 'nullable|string|max:100';
        $rules['consultation_hours'] = 'required|string|max:10000';

        return $rules;
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
        ]);
    }
}
