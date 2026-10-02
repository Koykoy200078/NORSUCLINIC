<?php

namespace App\Http\Requests;

use App\Models\Patient;
use Illuminate\Foundation\Http\FormRequest;

class StorePatientQueueRequest extends FormRequest
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
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'patient_id' => ['required', 'integer', function (string $attribute, mixed $value, \Closure $fail) {
                // Patient::find() skips archived patients, so only live, active patients can be queued.
                $patient = Patient::with('user')->find($value);

                if (! $patient) {
                    $fail('The selected patient does not exist or has been archived.');
                } elseif (! $patient->user || (int) $patient->user->status !== 1) {
                    $fail('This patient account is not active and cannot be added to the queue.');
                }
            }],
            'room_number' => 'nullable|string|max:50',
            'is_priority' => 'boolean',
            'notes' => 'nullable|string|max:500',
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'patient_id.required' => 'Please select a patient.',
            'patient_id.exists' => 'The selected patient does not exist.',
            'room_number.max' => 'Room number cannot exceed 50 characters.',
            'notes.max' => 'Notes cannot exceed 500 characters.',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Convert checkbox value to boolean
        if ($this->has('is_priority')) {
            $this->merge([
                'is_priority' => $this->boolean('is_priority'),
            ]);
        }
    }
}
