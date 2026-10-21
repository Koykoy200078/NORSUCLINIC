<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StockControlRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasAnyRole(['clinic_admin', 'staff']) && $this->user()->can('manage_medicines');
    }

    public function rules(): array
    {
        if ($this->routeIs('*.store', 'stock-control.store')) {
            return ['preview_token' => ['required', 'string', 'max:131072'], 'confirmed' => ['required', 'accepted']];
        }

        return ['medicine_id' => ['required', 'integer', 'min:1'], 'mode' => ['required', 'in:count,expired,damaged'],
            'reason' => ['required', 'string', 'min:3', 'max:500'], 'entries' => ['required', 'array', 'min:1', 'max:1000'],
            'entries.*' => ['nullable', 'integer', 'between:0,2147483647']];
    }
}
