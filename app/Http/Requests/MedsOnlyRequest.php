<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * One Meds Only record (a patient who only came for medicine): the day, who, the college / course / year of the day, and
 * one or many drugs. A registered patient (archived ones are refused) or a typed name; the lists are the clinic's own
 * (colleges, courses, year levels, campuses, patient types).
 */
class MedsOnlyRequest extends FormRequest
{
    public function authorize(): bool
    {
        // The route's permission middleware decides who may reach this; the module has no per-record owner rule.
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'bill_date' => 'required|date_format:Y-m-d|after:1899-12-31|before_or_equal:+1 day',
            'patient_id' => ['nullable', 'integer', Rule::exists('patients', 'id')->whereNull('archived_at')],
            'patient_name' => 'nullable|required_without:patient_id|string|max:191',
            'campus_id' => 'nullable|integer|exists:campuses,id',
            'college_id' => 'nullable|integer|exists:colleges,id',
            'course_id' => 'nullable|integer|exists:courses,id',
            'year_level_id' => 'nullable|integer|exists:year_levels,id',
            'patient_type_id' => 'nullable|integer|exists:patient_types,id',
            'note' => 'nullable|string|max:2000',
            'medicines' => 'required|array|min:1|max:30',
            'medicines.*.medicine_id' => 'required|integer|exists:medicines,id',
            'medicines.*.dosage' => 'nullable|string|max:100',
            'medicines.*.quantity' => 'required|integer|min:1|max:100000',
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'patient_name.required_without' => 'Choose a registered patient or type the name.',
            'medicines.required' => 'Add at least one drug.',
            'medicines.min' => 'Add at least one drug.',
            'medicines.*.quantity.min' => 'Each drug needs at least 1 unit.',
            'patient_id.exists' => 'That patient is not registered (or was archived).',
            'bill_date.before_or_equal' => 'The date cannot be in the future.',
        ];
    }
}
