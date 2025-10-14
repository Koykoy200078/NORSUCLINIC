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
     */
    public function rules(): array
    {
        $rules = PatientQueue::$rules;

        return $rules;
    }

    /**
     * @return array|string[]
     */
    public function messages(): array
    {
        return [
            'service_id.required' => __('messages.appointment.ServiceRequired'),
        ];
    }
}
