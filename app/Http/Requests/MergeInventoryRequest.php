<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class MergeInventoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasRole('clinic_admin') && $this->user()->can('manage_medicines');
    }

    public function rules(): array
    {
        return [
            'kind' => ['required', 'in:medicine,category,generic'],
            'source_id' => ['required', 'integer', 'min:1', 'different:target_id'],
            'target_id' => ['required', 'integer', 'min:1'],
            'preview_token' => ['required', 'string', 'max:8192'],
            'confirmed' => ['required', 'accepted'],
        ];
    }
}
