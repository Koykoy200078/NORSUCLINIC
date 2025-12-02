<?php

namespace App\Http\Requests;

use App\Models\Medicine;
use Illuminate\Foundation\Http\FormRequest;

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
        return Medicine::$rules;
    }

    public function messages(): array
    {
        return [
            'category_id.required' => __('messages.common.category_required'),
            'generic_id.required' => __('messages.common.generic_required'),
        ];
    }

    public function sanitize()
    {
        $input = $this->all();
        $this->replace($input);
    }
}
