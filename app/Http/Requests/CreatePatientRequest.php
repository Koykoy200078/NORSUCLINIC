<?php

namespace App\Http\Requests;

use App\Http\Requests\Concerns\ChecksArchivedAccounts;
use App\Http\Requests\Concerns\NormalizesPhilippinePhone;
use App\Models\Patient;
use App\Rules\PhilippinePhoneNumber;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Adding a patient. UpdatePatientRequest uses the same rules (re-audit 2026-11-13, DRY: the two requests were copies and the
 * e-mail rule had drifted apart; both use "email:filter" now, the owner's decision) and changes only what differs on an edit.
 */
class CreatePatientRequest extends FormRequest
{
    use ChecksArchivedAccounts;
    use NormalizesPhilippinePhone;

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->normalizePhilippinePhones(['contact' => 'national', 'emergency_contact_no' => 'e164']);
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        $rules = $this->baseRules();

        // Guest (id=4) does not require university_id_number; Student, Staff, Faculty do.
        // Unique among ACTIVE accounts; a match with an archived one gets its own clear message (see withValidator). M-13.
        $rules['email'] = ['nullable', 'email:filter', 'max:191', $this->uniqueAmongActiveUsers('email', $this->editedUserId())];
        $rules['university_id_number'] = ['nullable', 'string', 'max:100', $this->uniqueAmongActiveUsers('university_id_number', $this->editedUserId())];
        $rules['patient_type_id'] = 'required|exists:patient_types,id';
        $rules['nationality_citizenship'] = 'required|string|max:120';
        $rules['immunization_record'] = 'required|string|max:10000';
        $rules['postal_code'] = 'nullable|numeric';
        $rules['contact'] = ['nullable', new PhilippinePhoneNumber()];
        $rules['emergency_contact_no'] = ['nullable', new PhilippinePhoneNumber()];
        $rules['profile'] = 'nullable|mimes:jpeg,jpg,png|max:2000';

        return $rules;
    }

    public function withValidator($validator)
    {
        $this->validateArchivedAccountConflicts($validator, [
            'email' => 'email',
            'university_id_number' => 'university_id_number',
        ], $this->editedUserId());

        $validator->after(function ($validator) {
            $patientTypeId = $this->input('patient_type_id');
            $universityId = $this->input('university_id_number');

            // Guest is exempt; all other types require university_id_number. Looked up by code, not by the id the
            // seeder happened to give it. R3-L14.
            $guestTypeId = (string) \App\Models\PatientType::where('code', 'guest')->value('id');
            if ((string) $patientTypeId !== $guestTypeId && (empty($universityId) || trim($universityId) === '')) {
                $validator->errors()->add('university_id_number', 'The university id number field is required.');
            }
        });
    }

    public function messages(): array
    {
        return [
            'profile.max' => __('messages.profile_size'),
        ];
    }

    /** The model's rule set the form starts from (a new patient may set a password; an edit never changes it). */
    protected function baseRules(): array
    {
        return Patient::$rules;
    }

    /** The patient's user being edited, left out of the unique checks; none when a patient is added. */
    protected function editedUserId(): ?int
    {
        return null;
    }
}
