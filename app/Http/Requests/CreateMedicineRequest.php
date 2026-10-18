<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreateMedicineRequest extends FormRequest
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
        return [
            'generic_name' => ['required', 'string', 'max:191'],
            'brand_name' => ['nullable', 'string', 'max:191'],
            'category' => ['required', 'string', 'max:191'],
            'dosage' => ['required', 'string', 'max:100'],
            'uom' => ['required', 'string', 'max:50'],
            'description' => ['nullable', 'string', 'max:10000'],
            'sku' => ['nullable', 'string', 'max:100', Rule::unique('medicines', 'sku')],
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
            'manufacturing_date' => ['nullable', 'date', 'after:1989-12-31', 'before_or_equal:today'],
            'expiration_date' => ['nullable', 'date', 'after_or_equal:manufacturing_date', 'after:1989-12-31', 'before:2101-01-01'],
            'supplier_name' => ['nullable', 'string', 'max:191'],
        ];
    }

    public function messages(): array
    {
        return [
            'manufacturing_date.after' => 'The manufacturing date cannot be before 1990.',
            'manufacturing_date.before_or_equal' => 'The manufacturing date cannot be in the future.',
            'expiration_date.after' => 'The expiration date cannot be before 1990.',
            'expiration_date.before' => 'The expiration date cannot be after the year 2100.',
            'category.required' => __('messages.common.category_required'),
            'generic_name.required' => __('messages.common.generic_required'),
            'batch_number.required' => 'Batch number is required when initial stock is provided.',
        ];
    }

    public function sanitize()
    {
        $input = $this->all();
        $this->replace($input);
    }
}
