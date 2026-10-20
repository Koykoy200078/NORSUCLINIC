<?php

namespace App\Http\Requests\Concerns;

use App\Models\Doctor;
use Illuminate\Validation\Rule;

/**
 * The rules of the doctor form that the create and the edit request share. Each one matches what the column can hold or
 * what the row points to: a value the database would refuse used to reach it and come back as a bare "422" page with the
 * SQL error text in it.
 */
trait DoctorFormRules
{
    /** @return array<string, mixed> */
    protected function doctorFormRules(): array
    {
        return [
            'gender' => ['required', 'integer', 'in:1,2'],
            'blood_type' => ['nullable', 'string', 'max:191', Rule::in(array_keys(Doctor::BLOOD_TYPE_ARRAY))],
            'experience' => ['nullable', 'numeric', 'min:0', 'max:80'],
            'specializations' => ['required', 'array', 'min:1'],
            'specializations.*' => ['integer', Rule::exists('specializations', 'id')],
            'address1' => ['nullable', 'string', 'max:191'],
            'address2' => ['nullable', 'string', 'max:191'],
            'postal_code' => ['nullable', 'string', 'max:20'],
            'country_id' => ['nullable', 'integer', Rule::exists('countries', 'id')],
            'state_id' => ['nullable', 'integer', Rule::exists('states', 'id')],
            'city_id' => ['nullable', 'integer', Rule::exists('cities', 'id')],
            'barangay_id' => ['nullable', 'integer', Rule::exists('barangays', 'id')],
            'qualifications' => ['nullable', 'string', function (string $attribute, mixed $value, \Closure $fail) {
                $items = json_decode($value, true);
                if (! is_array($items)) {
                    $fail('The qualifications could not be read.');

                    return;
                }
                foreach ($items as $item) {
                    if ($item === null) {
                        continue;
                    }
                    if (! is_array($item)) {
                        $fail('The qualifications could not be read.');

                        return;
                    }
                    foreach (['degree', 'university', 'year'] as $key) {
                        $text = $item[$key] ?? null;
                        $given = $text !== null && is_scalar($text) && trim((string) $text) !== '';
                        if (($given && mb_strlen((string) $text) > 191) || (! $given && ! isset($item['id']))) {
                            $fail("Each qualification needs its {$key} (191 characters at most).");

                            return;
                        }
                    }
                }
            }],
        ];
    }
}
