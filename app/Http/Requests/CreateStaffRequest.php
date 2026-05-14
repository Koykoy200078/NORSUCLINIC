<?php

namespace App\Http\Requests;

use App\Models\StaffDesignation;
use App\Rules\ValidStaffDesignationStationPair;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreateStaffRequest extends FormRequest
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
        $roleDesignationId = $this->input('role_designation_id');
        $isClinicHead = $this->isClinicHeadDesignation($roleDesignationId);

        return [
            'first_name' => 'required',
            'last_name' => 'required',
            'email' => 'required|email:filter|unique:users,email',
            'employee_id' => 'required|string|max:100|unique:users,employee_id',
            'contact' => 'nullable|unique:users,contact',
            'password' => 'required|same:password_confirmation|min:6',
            'gender' => 'required',
            'role' => 'sometimes|integer|exists:roles,id', // Default value 3 will be set, validate only if provided
            'role_designation_id' => 'required|exists:staff_designations,id',
            'assigned_station_id' => [
                Rule::requiredIf(! $isClinicHead),
                'nullable',
                'exists:clinic_stations,id',
                new ValidStaffDesignationStationPair($roleDesignationId),
            ],
            'shift_schedule' => 'required|string',
            'profile' => 'nullable|mimes:jpeg,jpg,png|max:2000',
        ];
    }

    private function isClinicHeadDesignation($designationId): bool
    {
        if (! is_numeric($designationId)) {
            return false;
        }

        return StaffDesignation::query()
            ->whereKey((int) $designationId)
            ->value('code') === 'clinic_head';
    }

    /**
     * @return string[]
     */
    public function messages(): array
    {
        return [
            'profile.max' => __('messages.profile_size'),
            'assigned_station_id.required' => 'Assigned station is required unless role designation is Clinic Head.',
        ];
    }
}
