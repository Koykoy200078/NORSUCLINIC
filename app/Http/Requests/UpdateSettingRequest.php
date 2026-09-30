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
            'logo' => 'nullable|image|mimes:jpeg,png,jpg',
            'favicon' => 'nullable|image|mimes:png|dimensions:width=32,height=32',
        ];

        if ($this->request->get('sectionName') == 'contact-information') {
            return array_merge($rules, [
                'country_id' => 'required',
                'state_id' => 'required',
                'city_id' => 'required',
                'address_one' => 'required',
                'address_two' => 'required',
                'postal_code' => 'required',
            ]);
        }

        if ($this->request->get('sectionName') == 'general') {
            return array_merge($rules, [
                'email' => 'required|email:filter',
                'specialties' => 'required',
                'clinic_name' => 'required',
                'contact_no' => ['required', new PhilippinePhoneNumber()],
                'language' => 'required',
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
