<?php

namespace App\Http\Requests;

use App\Models\Patient;
use Illuminate\Support\Facades\Log;

/**
 * Editing a patient: the rules of CreatePatientRequest, starting from the model's edit rules (no password: an edit never
 * changes it, User::RECORD_FIELDS), with the patient's own account left out of the unique checks.
 */
class UpdatePatientRequest extends CreatePatientRequest
{
    protected function baseRules(): array
    {
        return Patient::$editRules;
    }

    protected function editedUserId(): ?int
    {
        // Route model binding gives the patient on every panel (admin, staff, doctor)
        $patient = $this->route()->parameter('patient');
        if ($patient instanceof Patient) {
            return (int) $patient->user_id;
        }

        Log::error('UpdatePatientRequest: Patient parameter is NULL or not instance of Patient', [
            'route_name' => $this->route()->getName(),
            'parameters' => $this->route()->parameters(),
        ]);

        return null;
    }

    /**
     * Handle a failed validation attempt.
     */
    protected function failedValidation(\Illuminate\Contracts\Validation\Validator $validator)
    {
        // Which fields were refused, never what was typed: the form holds names, allergies, conditions and the
        // immunization record of a patient, and the log file is not a place for them.
        Log::error('UpdatePatientRequest validation failed', [
            'route_name' => $this->route()->getName(),
            'refused_fields' => array_keys($validator->errors()->toArray()),
        ]);

        parent::failedValidation($validator);
    }
}
