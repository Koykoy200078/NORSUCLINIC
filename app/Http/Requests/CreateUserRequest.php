<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;

class CreateUserRequest extends FormRequest
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
        $rules = User::$rules;

        $rules['employee_id'] = 'required|string|max:100|unique:users,employee_id';
        $rules['prc_license_number'] = 'required|string|max:100|unique:doctors,prc_license_number';
        $rules['ptr_number'] = 'required|string|max:100';
        $rules['s2_license_number'] = 'nullable|string|max:100';
        $rules['consultation_hours'] = 'required|string';

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
}
