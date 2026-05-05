<?php

namespace App\Rules;

use App\Models\ClinicStation;
use App\Models\StaffDesignation;
use Illuminate\Contracts\Validation\Rule;

class ValidStaffDesignationStationPair implements Rule
{
    private ?int $designationId;

    public function __construct($designationId)
    {
        $this->designationId = is_numeric($designationId) ? (int) $designationId : null;
    }

    public function passes($attribute, $value): bool
    {
        if (! $this->designationId || ! is_numeric($value)) {
            return true;
        }

        $designation = StaffDesignation::query()->select(['id', 'code'])->find($this->designationId);
        $station = ClinicStation::query()->select(['id', 'code'])->find((int) $value);

        if (! $designation || ! $station) {
            return true;
        }

        return canStaffDesignationWorkAtStation((string) $designation->code, (string) $station->code);
    }

    public function message(): string
    {
        return 'The selected assigned station is not allowed for the selected role designation.';
    }
}
