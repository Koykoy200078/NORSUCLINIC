<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateMedicineBillRequest extends FormRequest
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
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'patient_id' => 'required|integer|exists:patients,id',
            'bill_date' => 'required|date',
            'category_id' => 'required|array|min:1',
            'category_id.*' => 'required|integer|exists:categories,id',
            'medicine' => 'required|array|min:1',
            'medicine.*' => 'required|integer|exists:medicines,id',
            'dosage' => 'required|array|min:1',
            'dosage.*' => 'required|string|max:100',
            'quantity' => 'required|array|min:1',
            'quantity.*' => 'required|integer|min:1',
            'expiry_date' => 'nullable|array',
            'expiry_date.*' => 'nullable|date',
            'note' => 'nullable|string|max:2000',
        ];
    }
}
