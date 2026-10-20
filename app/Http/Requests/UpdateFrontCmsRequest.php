<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateFrontCmsRequest extends FormRequest
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
        return [
            'about_image_1' => 'mimes:jpg,jpeg,png',
            'about_image_2' => 'mimes:jpg,jpeg,png',
            'about_image_3' => 'mimes:jpg,jpeg,png',
            'about_title' => 'required|string|max:191',
            'about_experience' => 'required|numeric|min:0',
            'about_short_description' => 'required|string',
            'terms_conditions' => $this->richTextRules(),
            'privacy_policy' => $this->richTextRules(),
        ];
    }

    private function richTextRules(): array
    {
        return ['bail', 'required', 'string', 'json', function (string $attribute, mixed $value, \Closure $fail): void {
            if (! is_string(json_decode($value, true))) {
                $fail('The '.str_replace('_', ' ', $attribute).' must contain text.');
            }
        }];
    }
}
