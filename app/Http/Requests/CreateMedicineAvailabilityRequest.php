<?php

namespace App\Http\Requests;

use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class CreateMedicineAvailabilityRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $normalizeDateArray = function (array $values): array {
            return array_map(function ($value) {
                if (! is_string($value)) {
                    return $value;
                }

                $value = trim($value);

                if (preg_match('/^(\d{4}-\d{2}-\d{2})[ T]\d{2}:\d{2}:\d{2}$/', $value, $matches) === 1) {
                    return $matches[1];
                }

                return $value;
            }, $values);
        };

        $manufacturingDates = $this->input('manufacturing_date', []);
        $expiryDates = $this->input('expiry_date', []);

        $this->merge([
            'manufacturing_date' => is_array($manufacturingDates) ? $normalizeDateArray($manufacturingDates) : $manufacturingDates,
            'expiry_date' => is_array($expiryDates) ? $normalizeDateArray($expiryDates) : $expiryDates,
        ]);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'availability_no' => ['nullable', 'string', 'max:255'],
            'medicine' => ['required', 'array', 'min:1'],
            'medicine.*' => ['required', 'integer', 'exists:medicines,id'],
            'dosage' => ['nullable', 'array'],
            'dosage.*' => ['nullable', 'string', 'max:100'],

            'manufacturing_date' => ['required', 'array', 'min:1'],
            'manufacturing_date.*' => [
                'required',
                'string',
                'regex:/^\d{4}-(0[1-9]|1[0-2])(?:-(0[1-9]|[12]\d|3[01]))?$/',
            ],
            'expiry_date' => ['required', 'array', 'min:1'],
            'expiry_date.*' => [
                'required',
                'string',
                'regex:/^\d{4}-(0[1-9]|1[0-2])(?:-(0[1-9]|[12]\d|3[01]))?$/',
            ],

            'manufacturing_format' => ['nullable', 'array'],
            'manufacturing_format.*' => ['nullable', Rule::in(['Y-m', 'Y-m-d'])],
            'expiry_format' => ['nullable', 'array'],
            'expiry_format.*' => ['nullable', Rule::in(['Y-m', 'Y-m-d'])],

            'quantity' => ['required', 'array', 'min:1'],
            'quantity.*' => ['required', 'integer', 'min:1'],

            // Used during edit flow when updating existing lines.
            'purchased_medicine_id' => ['sometimes', 'array'],
            'purchased_medicine_id.*' => ['nullable', 'integer', 'exists:purchased_medicines,id'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $medicineRows = (array) $this->input('medicine', []);
            $manufacturingRows = (array) $this->input('manufacturing_date', []);
            $expiryRows = (array) $this->input('expiry_date', []);
            $quantityRows = (array) $this->input('quantity', []);

            $expectedRows = count($medicineRows);
            if ($expectedRows === 0) {
                return;
            }

            if (count($manufacturingRows) !== $expectedRows) {
                $validator->errors()->add('manufacturing_date', 'Manufacturing date rows must match medicine rows.');
            }

            if (count($expiryRows) !== $expectedRows) {
                $validator->errors()->add('expiry_date', 'Expiry date rows must match medicine rows.');
            }

            if (count($quantityRows) !== $expectedRows) {
                $validator->errors()->add('quantity', 'Quantity rows must match medicine rows.');
            }

            if ($validator->errors()->isNotEmpty()) {
                return;
            }

            foreach ($medicineRows as $index => $medicineId) {
                $rowNumber = $index + 1;
                $manufacturingRaw = (string) ($manufacturingRows[$index] ?? '');
                $expiryRaw = (string) ($expiryRows[$index] ?? '');

                $manufacturingDate = $this->parseFlexibleDate($manufacturingRaw, false);
                $expiryDate = $this->parseFlexibleDate($expiryRaw, true);

                if (! $manufacturingDate) {
                    $validator->errors()->add("manufacturing_date.$index", "Row {$rowNumber}: Invalid manufacturing date format.");
                    continue;
                }

                if (! $expiryDate) {
                    $validator->errors()->add("expiry_date.$index", "Row {$rowNumber}: Invalid expiry date format.");
                    continue;
                }

                if ($expiryDate->lt($manufacturingDate)) {
                    $validator->errors()->add(
                        "expiry_date.$index",
                        "Row {$rowNumber}: Expiry date must be after or equal to manufacturing date."
                    );
                }
            }
        });
    }

    public function messages(): array
    {
        return [
            'medicine.required' => 'At least one medicine row is required.',
            'medicine.*.exists' => 'Selected medicine is invalid.',
            'manufacturing_date.required' => 'Manufacturing date is required for each row.',
            'manufacturing_date.*.regex' => 'Manufacturing date must be in YYYY-MM or YYYY-MM-DD format.',
            'expiry_date.required' => 'Expiry date is required for each row.',
            'expiry_date.*.regex' => 'Expiry date must be in YYYY-MM or YYYY-MM-DD format.',
            'quantity.*.min' => 'Quantity should be greater than 0.',
        ];
    }

    private function parseFlexibleDate(string $value, bool $endOfPeriod): ?Carbon
    {
        $value = trim($value);
        if ($value === '') {
            return null;
        }

        if (preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', $value) === 1) {
            $date = Carbon::createFromFormat('Y-m', $value);
            if (! $date || $date->format('Y-m') !== $value) {
                return null;
            }

            return $endOfPeriod
                ? $date->endOfMonth()->startOfDay()
                : $date->startOfMonth()->startOfDay();
        }

        if (preg_match('/^\d{4}-(0[1-9]|1[0-2])-(0[1-9]|[12]\d|3[01])$/', $value) === 1) {
            $date = Carbon::createFromFormat('Y-m-d', $value);
            if (! $date || $date->format('Y-m-d') !== $value) {
                return null;
            }

            return $date->startOfDay();
        }

        return null;
    }
}
