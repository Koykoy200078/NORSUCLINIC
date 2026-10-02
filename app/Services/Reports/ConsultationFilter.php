<?php

namespace App\Services\Reports;

use App\Models\ServiceType;
use App\Support\SearchTerm;
use Illuminate\Database\Eloquent\Builder;

/**
 * The patient / visit filters of the Report Generation screen, applied to a query on `document_issuances`.
 * The Patient Visits tab, its CSV and the ACCOMPLISHMENT REPORT (screen, PDF, Excel, CSV) all go through here, so
 * they always agree about which consultations are included.
 */
final class ConsultationFilter
{
    /** Text that means "nothing" in the pregnancy and comorbidity boxes. */
    private const NOTHING = "('NO', 'N/A', 'NA', 'NONE', '-', 'NOT PREGNANT', 'NEGATIVE', 'NIL')";

    public static function apply(Builder $query, ReportFilters $filters): Builder
    {
        $query->where('document_issuances.document_type', 'consultation_form');

        // The date of the visit is the consultation date; an old row without one falls back to the day it was typed in.
        $visitDate = ServiceRules::visitDate();
        if ($filters->dateFrom) {
            $query->whereRaw("{$visitDate} >= ?", [$filters->dateFrom]);
        }
        if ($filters->dateTo) {
            $query->whereRaw("{$visitDate} <= ?", [$filters->dateTo]);
        }

        foreach ([
            'campus_id' => $filters->campusId,
            'college_id' => $filters->collegeId,
            'course_id' => $filters->courseId,
            'year_level_id' => $filters->yearLevelId,
            'department_id' => $filters->departmentId,
            'office_id' => $filters->officeId,
            'patient_type_id' => $filters->patientTypeId,
        ] as $column => $value) {
            if ($value !== null) {
                $query->where("document_issuances.{$column}", $value);
            }
        }

        if ($filters->gender !== null) {
            $query->where('document_issuances.gender', $filters->gender);
        }

        if ($filters->consultMode !== 'all') {
            $query->where('document_issuances.consult_mode', $filters->consultMode);
        }

        if ($filters->ageGroup !== null) {
            [, $youngest, $oldest] = ReportFilters::AGE_GROUPS[$filters->ageGroup];
            $query->whereBetween('document_issuances.age', [$youngest, $oldest]);
        }

        if ($filters->staffId !== null) {
            $query->where(fn ($inner) => $inner
                ->where('document_issuances.nursing_incharged_id', $filters->staffId)
                ->orWhere('document_issuances.document_creator_id', $filters->staffId));
        }

        self::pregnancy($query, $filters);
        self::chronic($query, $filters);
        self::illness($query, $filters);

        if ($filters->serviceId !== null && ($service = ServiceType::find($filters->serviceId))) {
            ServiceRules::matching($query, $service);
        }

        if ($filters->medicineId !== null) {
            $query->whereExists(
                fn ($sub) => $sub->selectRaw('1')->from('consultation_medicines as cm_filter')
                    ->whereColumn('cm_filter.request_document_id', 'document_issuances.id')
                    ->where('cm_filter.medicine_id', $filters->medicineId)
            );
        }

        return $query;
    }

    private static function pregnancy(Builder $query, ReportFilters $filters): void
    {
        if ($filters->pregnancy === 'all') {
            return;
        }

        $notPregnant = "(NULLIF(TRIM(document_issuances.pregnancy_status), '') IS NULL OR UPPER(TRIM(document_issuances.pregnancy_status)) IN " . self::NOTHING . ')';

        $query->whereRaw($filters->pregnancy === 'pregnant' ? "NOT {$notPregnant}" : $notPregnant);
    }

    private static function chronic(Builder $query, ReportFilters $filters): void
    {
        $none = "(NULLIF(TRIM(document_issuances.comorbidities), '') IS NULL OR UPPER(TRIM(document_issuances.comorbidities)) IN " . self::NOTHING . ')';

        if ($filters->chronic === 'with') {
            $query->whereRaw("NOT {$none}");
        } elseif ($filters->chronic === 'none') {
            $query->whereRaw($none);
        }

        if ($filters->chronicText !== null) {
            SearchTerm::whereAllWords($query, $filters->chronicText, ['document_issuances.comorbidities']);
        }
    }

    private static function illness(Builder $query, ReportFilters $filters): void
    {
        if ($filters->unclassified) {
            $query->whereRaw('NOT ' . ServiceRules::hasIllness());

            return;
        }

        if ($filters->illnessSystemId !== null || $filters->illnessId !== null) {
            $query->whereExists(function ($sub) use ($filters) {
                $sub->selectRaw('1')->from('consultation_illnesses as ci_filter')
                    ->join('illnesses as i_filter', 'i_filter.id', '=', 'ci_filter.illness_id')
                    ->whereColumn('ci_filter.document_issuance_id', 'document_issuances.id');

                if ($filters->illnessSystemId !== null) {
                    $sub->where('i_filter.illness_system_id', $filters->illnessSystemId);
                }
                if ($filters->illnessId !== null) {
                    $sub->where('i_filter.id', $filters->illnessId);
                }
            });
        }
    }
}
