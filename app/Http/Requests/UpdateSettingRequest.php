<?php

namespace App\Http\Requests;

use App\Http\Requests\Concerns\NormalizesPhilippinePhone;
use App\Rules\PhilippinePhoneNumber;
use App\Support\PhilippinePhone;
use Illuminate\Foundation\Http\FormRequest;

class UpdateSettingRequest extends FormRequest
{
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
        // The clinic works with Philippine numbers only: the country is fixed, not selectable.
        $this->normalizePhilippinePhones(['contact_no' => 'national']);
        $this->merge(['default_country_code' => PhilippinePhone::DEFAULT_COUNTRY_ISO]);
        if ($this->input('sectionName') === 'contact_information') {
            $this->merge(['sectionName' => 'contact-information']);
        }
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        // File-type guards must ALWAYS apply: SettingRepository processes the logo/favicon
        // uploads regardless of the client-supplied `sectionName`, so gating these rules on
        // sectionName let an attacker upload an arbitrary file type to the public disk. E-SEC-5.
        $rules = [
            'sectionName' => 'required|in:general,contact-information,contact_information',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg',
            'favicon' => 'nullable|image|mimes:png|dimensions:width=32,height=32',
        ];

        if ($this->input('sectionName') === 'contact-information') {
            return array_merge($rules, [
                'country_id' => 'required|integer|exists:countries,id',
                'state_id' => 'required|integer|exists:states,id',
                'city_id' => 'required|integer|exists:cities,id',
                'address_one' => 'required|string|max:191',
                'address_two' => 'required|string|max:191',
                'postal_code' => 'required|string|max:20',
            ]);
        }

        if ($this->input('sectionName') === 'general') {
            return array_merge($rules, [
                'email' => 'required|email:filter',
                'specialties' => 'required|array|min:1',
                'specialties.*' => 'integer|exists:specializations,id',
                'clinic_name' => 'required|string|max:191',
                'landline_no' => 'nullable|string|max:30',
                'email_verified' => 'sometimes|boolean',
                'default_country_code' => 'required|in:ph',
                'contact_no' => ['required', new PhilippinePhoneNumber()],
                // The General form has no language picker any more (the controller only acts on a non-empty value),
                // so requiring it made every save fail with "The language field is required".
                'language' => 'nullable|string|max:10',
                // Signs the ACCOMPLISHMENT REPORT ("Noted by").
                'university_physician_name' => 'nullable|string|max:150',
                'university_physician_title' => 'nullable|string|max:100',
            ]);
        }

        return $rules;
    }

    /**
     * @return string[]
     */
    public function messages(): array
    {
        return [
            'country_id.required' => __('messages.country_required'),
            'state_id.required' => __('messages.state_required'),
            'city_id.required' => __('messages.city_required'),
            'address_one.required' => __('messages.address_1_required'),
            'address_two.required' => __('messages.address_2_required'),
            'logo.dimensions' => __('messages.logo_size'),
            'favicon.dimensions' => __('messages.favicon_size'),
        ];
    }
}
