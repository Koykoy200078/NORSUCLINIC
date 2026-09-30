<?php

namespace App\Http\Requests\Concerns;

use App\Support\PhilippinePhone;

/**
 * Philippine-only phone handling for form requests.
 *
 * Call {@see normalizePhilippinePhones()} from prepareForValidation(): every number that can be read
 * as a Philippine number (+63 917..., 0917..., 917..., landline with area code) is rewritten to the
 * form the database stores, so the "unique contact" checks compare like with like and the
 * PhilippinePhoneNumber rule only fails for values that really are not Philippine numbers.
 * The country code is always forced to 63 - the browser no longer sends one.
 */
trait NormalizesPhilippinePhone
{
    /**
     * @param  array<string, string>  $fields  input key => 'national' (digits only) | 'e164' (+63...)
     */
    protected function normalizePhilippinePhones(array $fields, bool $forceCountryCode = true): void
    {
        $merge = [];

        foreach ($fields as $key => $mode) {
            if (! $this->has($key)) {
                continue;
            }

            $raw = $this->input($key);

            if ($raw === null || (is_string($raw) && trim($raw) === '')) {
                $merge[$key] = null;

                continue;
            }

            $national = PhilippinePhone::national($raw);

            if ($national !== null) {
                $merge[$key] = $mode === 'e164' ? '+' . PhilippinePhone::COUNTRY_CODE . $national : $national;
            }
        }

        if ($forceCountryCode) {
            $merge['country_code'] = PhilippinePhone::COUNTRY_CODE;
        }

        $this->merge($merge);
    }
}
