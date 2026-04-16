<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateUserRequest extends FormRequest
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
        return [
            'first_name' => 'required',
            'last_name' => 'required',
            'email' => 'required|email|regex:/(.*)@(.*)\.(.*)/|unique:users,email,' . $this->route('doctor')->user_id,
            'institutional_email' => 'required|email:filter|unique:users,institutional_email,' . $this->route('doctor')->user_id,
            'employee_id' => 'required|string|max:100|unique:users,employee_id,' . $this->route('doctor')->user_id,
            'contact' => 'nullable|unique:users,contact,' . $this->route('doctor')->user_id,
            'pager_extension' => 'nullable|string|max:60',
            'dob' => 'nullable|date',
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
}
