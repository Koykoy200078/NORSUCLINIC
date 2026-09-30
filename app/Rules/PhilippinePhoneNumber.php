<?php

namespace App\Rules;

use App\Support\PhilippinePhone;
use Illuminate\Contracts\Validation\Rule;

/**
 * Passes for an empty value (combine with "required" when the field is mandatory) or for any
 * valid Philippine mobile / landline number - see {@see PhilippinePhone::national()}.
 */
class PhilippinePhoneNumber implements Rule
{
    public function passes($attribute, $value): bool
    {
        if ($value === null || $value === '') {
            return true;
        }

        return PhilippinePhone::isValid($value);
    }

    public function message(): string
    {
        return __('messages.invalid_ph_number');
    }
}
