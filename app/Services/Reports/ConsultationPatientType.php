<?php

namespace App\Services\Reports;

/** The visit's own labels determine its report type; later patient-profile edits do not rewrite history. */
final class ConsultationPatientType
{
    public static function codeSql(): string
    {
        $informant = 'LOWER(TRIM(document_issuances.informant))';

        return "CASE WHEN {$informant} IN ('guest', 'faculty', 'staff') THEN {$informant}"
            . ' ELSE (SELECT LOWER(TRIM(report_patient_type.code)) FROM patient_types AS report_patient_type'
            . ' WHERE report_patient_type.id = document_issuances.patient_type_id) END';
    }
}
