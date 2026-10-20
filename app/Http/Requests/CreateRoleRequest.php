<?php

namespace App\Http\Requests;

use App\Models\Role;
use Illuminate\Foundation\Http\FormRequest;

class CreateRoleRequest extends FormRequest
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
        $rules = Role::$rules;
        $rules['display_name'] = [
            'required', 'string', 'max:191', 'unique:roles,display_name',
            function (string $attribute, mixed $value, \Closure $fail): void {
                if (is_string($value) && Role::where('name', str_replace(' ', '_', strtolower($value)))->exists()) {
                    $fail(__('messages.common.name_already_taken'));
                }
            },
        ];

        return $rules;
    }

    /**
     * @return string[]
     */
    public function messages(): array
    {
        return [
            'display_name.required' => __('messages.common.name_required'),
            'display_name.unique' => __('messages.common.name_already_taken'),
            'permission_id.required' => __('messages.common.any_one_permission'),
        ];
    }
}
