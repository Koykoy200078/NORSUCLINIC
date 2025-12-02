<?php

namespace App\Http\Requests;

use App\Models\Generic;
use Illuminate\Foundation\Http\FormRequest;

class CreateGenericRequest extends FormRequest
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
        return Generic::$rules;
    }
}
