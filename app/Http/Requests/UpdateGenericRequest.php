<?php

namespace App\Http\Requests;

use App\Models\Generic;
use Illuminate\Foundation\Http\FormRequest;

class UpdateGenericRequest extends FormRequest
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
        $rules = Generic::$rules;
        $rules['email'] = 'nullable|email|unique:generics,email,' . $this->route('generic')->id;
        $rules['name'] = 'required|unique:generics,name,' . $this->route('generic')->id;

        return $rules;
    }
}
