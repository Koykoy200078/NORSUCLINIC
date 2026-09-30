<?php

namespace App\Http\Requests;

use App\Models\StaffDesignation;
use App\Rules\ValidStaffDesignationStationPair;
use App\Http\Requests\Concerns\ChecksArchivedAccounts;
use App\Http\Requests\Concerns\NormalizesPhilippinePhone;
use App\Rules\PhilippinePhoneNumber;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreateStaffRequest extends FormRequest
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
        $roleDesignationId = $this->input('role_designation_id');
        $isClinicHead = $this->isClinicHeadDesignation($roleDesignationId);

        return [
            'first_name' => 'required',
            'last_name' => 'required',
            'email' => ['required', 'email:filter', $this->uniqueAmongActiveUsers('email')],
            'employee_id' => ['required', 'string', 'max:100', $this->uniqueAmongActiveUsers('employee_id')],
            'contact' => ['nullable', new PhilippinePhoneNumber(), $this->uniqueAmongActiveUsers('contact')],
            'password' => 'required|same:password_confirmation|min:6',
            'gender' => 'required',
            // Staff accounts may only be given the staff or nurse role (default: staff). Assigning
            // clinic_admin / doctor / patient here produced accounts that crash their own pages. H-15.
            'role' => ['sometimes', 'nullable', 'integer', Rule::exists('roles', 'id')->whereIn('name', ['staff', 'nurse'])],
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

    public function withValidator($validator): void
    {
        $this->validateArchivedAccountConflicts($validator, [
            'email' => 'email',
            'employee_id' => 'employee_id',
            'contact' => 'contact',
        ]);
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
