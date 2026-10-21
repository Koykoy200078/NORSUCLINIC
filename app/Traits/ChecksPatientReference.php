<?php

namespace App\Traits;

use App\Models\Patient;
use Illuminate\Database\Eloquent\Model;

trait ChecksPatientReference
{
    public static function bootChecksPatientReference(): void
    {
        static::saving(function (Model $record): void {
            $column = match ($record->getTable()) {
                'document_issuances' => 'user_id',
                'lab_requests' => 'patient_user_id',
                default => 'patient_id',
            };
            // An unchanged historical association is allowed; selecting a new association must use a live patient.
            if ($record->$column && (! $record->exists || $record->isDirty($column))) {
                Patient::lockForClinicalWrite((int) $record->$column, str_contains($column, 'user_id'));
            }
        });
    }
}
