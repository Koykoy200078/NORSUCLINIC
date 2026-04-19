<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateMedicineRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation()
    {
        $this->sanitize();
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        $medicine = $this->route('medicine');
        $medicineId = is_object($medicine) ? $medicine->id : $medicine;

        return [
            'generic_name' => ['required', 'string', 'max:255'],
            'brand_name' => ['nullable', 'string', 'max:255'],
            'category' => ['required', 'string', 'max:255'],
            'dosage' => ['required', 'string', 'max:100'],
            'uom' => ['required', 'string', 'max:50'],
            'description' => ['nullable', 'string'],
            'sku' => ['nullable', 'string', 'max:100', Rule::unique('medicines', 'sku')->ignore($medicineId)],
            'reorder_level' => ['nullable', 'integer', 'min:0'],
            'minimum_stock_alert' => ['nullable', 'integer', 'min:0'],
            'stock_alert_percentage' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'initial_stock_quantity' => ['nullable', 'integer', 'min:0'],
            'batch_number' => [
                Rule::requiredIf(fn() => (int) $this->input('initial_stock_quantity', 0) > 0),
                'nullable',
                'string',
                'max:100',
            ],
            'manufacturing_date' => ['nullable', 'date'],
            'expiration_date' => ['nullable', 'date', 'after_or_equal:manufacturing_date'],
            'supplier_name' => ['nullable', 'string', 'max:255'],
            'unit_cost' => ['nullable', 'numeric', 'min:0'],
        ];
    }

    public function messages(): array
    {
        return [
            'category.required' => __('messages.common.category_required'),
            'generic_name.required' => __('messages.common.generic_required'),
            'batch_number.required' => 'Batch number is required when stock quantity is provided.',
        ];
    }

    public function sanitize()
    {
        $input = $this->all();
        $this->replace($input);
    }
}
