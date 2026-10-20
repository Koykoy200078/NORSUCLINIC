<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateStateRequest extends FormRequest
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
        $rules['name'] = 'required|string|max:191|unique:states,name,'.$this->route('state')->id;
        $rules['country_id'] = 'sometimes|required|integer|exists:countries,id';

        return $rules;
    }
}
