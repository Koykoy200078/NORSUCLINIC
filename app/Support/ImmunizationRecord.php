<?php

namespace App\Support;

/**
 * The patient's immunization record carries one "COVID-19 Vaccination Status: ..." line, kept in step with the COVID
 * vaccination typed on the patient form (PatientRepository) and on a consultation (ConsultationPatientService). One rule
 * for both (it used to be two copies of the same method).
 */
final class ImmunizationRecord
{
    /** Statuses that say nothing, so they never replace what the record already holds. */
    private const UNKNOWN = ['unknown vaccination', 'unknown', 'n/a', 'na'];

    /**
     * The record with its COVID line set to $covidStatus: the first existing COVID line (any spelling) is replaced and any
     * further ones dropped, the other lines keep their order, a missing line is added at the end. A blank or unknown
     * status returns the record unchanged; blank lines are dropped.
     */
    public static function withCovidStatus(?string $record, ?string $covidStatus): ?string
    {
        $record = ConsultationInput::nullableString($record);
        $covidStatus = ConsultationInput::nullableString($covidStatus);

        if ($covidStatus === null || in_array(strtolower($covidStatus), self::UNKNOWN, true)) {
            return $record;
        }

        $covidLine = 'COVID-19 Vaccination Status: ' . $covidStatus;
        $lines = array_values(array_filter(
            array_map(fn ($line) => trim((string) $line), preg_split('/\R+/', (string) ($record ?? '')) ?: []),
            fn ($line) => $line !== ''
        ));

        $updated = [];
        $hasCovidLine = false;
        foreach ($lines as $line) {
            if (preg_match('/^covid(?:-19)?\s+vaccination\s+status\s*:/i', $line)) {
                if (! $hasCovidLine) {
                    $updated[] = $covidLine;
                    $hasCovidLine = true;
                }

                continue;
            }

            $updated[] = $line;
        }

        if (! $hasCovidLine) {
            $updated[] = $covidLine;
        }

        return implode(PHP_EOL, $updated);
    }
}
