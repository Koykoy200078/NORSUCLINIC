<?php

namespace App\Http\Requests;

use App\Models\PatientQueue;
use Illuminate\Foundation\Http\FormRequest;

class UpdatePatientQueueRequest extends FormRequest
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
        $statuses = implode(',', [
            PatientQueue::STATUS_WAITING,
            PatientQueue::STATUS_IN_PROGRESS,
            PatientQueue::STATUS_COMPLETED,
            PatientQueue::STATUS_CANCELLED,
        ]);

        return [
            'room_number' => 'nullable|string|max:50',
            'is_priority' => 'boolean',
            'notes' => 'nullable|string|max:500',
            'status' => "nullable|in:{$statuses}",
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
            'room_number.max' => 'Room number cannot exceed 50 characters.',
            'notes.max' => 'Notes cannot exceed 500 characters.',
            'status.in' => 'Invalid queue status selected.',
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
