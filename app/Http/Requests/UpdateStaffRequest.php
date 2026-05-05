<?php

namespace App\Http\Requests;

use App\Rules\ValidStaffDesignationStationPair;
use Illuminate\Foundation\Http\FormRequest;

class UpdateStaffRequest extends FormRequest
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
            'email' => 'required|email:filter|unique:users,email,' . $this->route('staff')->id,
            'employee_id' => 'required|string|max:100|unique:users,employee_id,' . $this->route('staff')->id,
            'contact' => 'nullable|unique:users,contact,' . $this->route('staff')->id,
            'password' => 'nullable|same:password_confirmation|min:6',
            'gender' => 'required',
            'role' => 'sometimes|integer|exists:roles,id', // Default value 3 will be set, validate only if provided
            'role_designation_id' => 'required|exists:staff_designations,id',
            'assigned_station_id' => [
                'required',
                'exists:clinic_stations,id',
                new ValidStaffDesignationStationPair($this->input('role_designation_id')),
            ],
            'shift_schedule' => 'required|string',
            'profile' => 'nullable|mimes:jpeg,jpg,png|max:2000',
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
