<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePrescriptionRequest extends FormRequest
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
            'patient_id' => 'required|integer|exists:patients,id',
            'doctor_id' => 'required|integer|exists:doctors,id',
            'doctor_license_s2_number' => 'nullable|string|max:100',
            // Staff write a prescription only on a doctor's verbal / phone order, and must say so.
            'verbal_order' => isRole('staff') ? ['accepted'] : ['nullable', 'boolean'],
            'consultation_date' => 'required|date',
            'icd10_diagnosis_id' => 'nullable|integer|exists:diagnoses,id',
            'problem_description' => 'nullable|string|max:2000',
            'advice' => 'nullable|string|max:2000',
            'next_visit_days' => 'nullable|integer|min:0|max:365',
            'weight_kg' => 'nullable|numeric|min:0|max:500',
            'pulse_rate' => 'nullable|string|max:50',
            'body_temperature' => 'nullable|numeric|min:20|max:50',
            'blood_pressure' => 'nullable|string|max:50',
            'height_cm' => 'nullable|numeric|min:0|max:300',
            'medicines' => 'required|array|min:1',
            'medicines.*.medicine_id' => 'required|integer|exists:medicines,id',
            'medicines.*.dosage' => 'required|string|max:255',
            'medicines.*.route_of_administration' => 'required|string|in:oral,iv,im,topical,subcutaneous,inhalation,other',
            'medicines.*.frequency' => 'required|integer|min:1|max:24',
            'medicines.*.duration_value' => 'required|integer|min:1|max:365',
            'medicines.*.duration_unit' => 'required|string|in:day,week,month',
            'medicines.*.total_quantity' => 'required|integer|min:1|max:10000',
            'medicines.*.instructions' => 'nullable|string|max:1000',
        ];
    }

    public function messages(): array
    {
        return [
            'verbal_order.accepted' => 'Tick the verbal / phone order box to confirm that the doctor ordered this prescription.',
            'medicines.required' => 'Add at least one medicine item.',
            'medicines.*.medicine_id.required' => 'Medicine selection is required for each row.',
            'medicines.*.dosage.required' => 'Dosage is required for each selected medicine.',
            'medicines.*.route_of_administration.required' => 'Route of administration is required for each selected medicine.',
            'medicines.*.frequency.required' => 'Frequency is required for each selected medicine.',
            'medicines.*.duration_value.required' => 'Duration value is required for each selected medicine.',
            'medicines.*.duration_unit.required' => 'Duration unit is required for each selected medicine.',
            'medicines.*.total_quantity.required' => 'Total quantity is required for each selected medicine.',
        ];
    }
}
