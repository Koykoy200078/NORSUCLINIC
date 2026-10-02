<?php

namespace App\Services\Reports;

use App\Models\ServiceType;
use Illuminate\Database\Eloquent\Builder;

/**
 * How the system recognises a service of the ACCOMPLISHMENT REPORT without the nurse ticking it: from the vital
 * signs recorded on the consultation, the medicines given, and the certificate or laboratory request made for the
 * patient on the day of the visit. A line can also be ticked by hand; the two never count a consultation twice
 * (see matching()).
 *
 * Every condition is a SQL fragment on the `document_issuances` row of the consultation.
 */
final class ServiceRules
{
    private const VISIT_DATE = 'COALESCE(document_issuances.requested_at, DATE(document_issuances.created_at))';

    /** The vital-sign columns, by measurement. */
    private const VITALS = [
        'bp' => 'vital_signs_bp',
        'pulse' => 'vital_signs_pr',
        'resp' => 'vital_signs_rr',
        'temp' => 'vital_signs_temp',
        'o2' => 'vital_signs_o2_sat',
        'height' => 'vital_signs_height',
        'weight' => 'vital_signs_weight',
    ];

    /** The visit's own date, as SQL. */
    public static function visitDate(): string
    {
        return self::VISIT_DATE;
    }

    /** SQL that is true when the (text) column holds a real value, not blank / N/A / none / a dash. */
    public static function recorded(string $column): string
    {
        $column = "document_issuances.{$column}";

        return "(NULLIF(TRIM({$column}), '') IS NOT NULL AND UPPER(TRIM({$column})) NOT IN ('N/A', 'NA', 'NONE', '-', 'NULL'))";
    }

    /** SQL that is true when the (text) column is blank / N/A / none / a dash, i.e. nothing real was entered. */
    public static function blank(string $column): string
    {
        return 'NOT ' . self::recorded($column);
    }

    /** SQL that is true when the consultation has at least one illness picked. */
    public static function hasIllness(): string
    {
        return 'EXISTS (SELECT 1 FROM consultation_illnesses ci_rule WHERE ci_rule.document_issuance_id = document_issuances.id)';
    }

    /** The SQL condition for an automatic rule, or null for a service that is only ever ticked. */
    public static function condition(?string $rule): ?string
    {
        $recorded = fn (string ...$keys) => '(' . implode(' OR ', array_map(fn ($key) => self::recorded(self::VITALS[$key]), $keys)) . ')';
        $none = fn (string ...$keys) => '(' . implode(' AND ', array_map(fn ($key) => self::blank(self::VITALS[$key]), $keys)) . ')';

        return match ($rule) {
            'medicine_given' => 'EXISTS (SELECT 1 FROM consultation_medicines cm_rule WHERE cm_rule.request_document_id = document_issuances.id)',
            'medical_certificate' => "EXISTS (SELECT 1 FROM document_issuances mc_rule WHERE mc_rule.document_type = 'medical_certificate' AND mc_rule.deleted_at IS NULL"
                . ' AND mc_rule.user_id = document_issuances.user_id'
                . ' AND COALESCE(mc_rule.requested_at, DATE(mc_rule.created_at)) = ' . self::VISIT_DATE . ')',
            'lab_request' => 'EXISTS (SELECT 1 FROM lab_requests lr_rule WHERE lr_rule.patient_user_id = document_issuances.user_id'
                . ' AND lr_rule.requested_at = ' . self::VISIT_DATE
                . " AND lr_rule.status NOT IN ('cancelled', 'rejected'))",
            'height_weight' => $recorded('height', 'weight'),
            // "...only": that measurement and nothing else was done, and the visit was not for an illness.
            'bp_only' => '(' . $recorded('bp') . ' AND ' . $none('pulse', 'resp', 'temp', 'o2', 'height', 'weight') . ' AND NOT ' . self::hasIllness() . ')',
            'vitals_only' => '(' . $recorded('pulse', 'resp', 'temp') . ' AND ' . $none('bp', 'o2', 'height', 'weight') . ' AND NOT ' . self::hasIllness() . ')',
            'o2_only' => '(' . $recorded('o2') . ' AND ' . $none('bp', 'pulse', 'resp', 'temp', 'height', 'weight') . ' AND NOT ' . self::hasIllness() . ')',
            default => null,
        };
    }

    /**
     * Keep only the consultations that count for a service: ticked by hand, or recognised by its rule.
     */
    public static function matching(Builder $query, ServiceType $service): Builder
    {
        return $query->where(function ($inner) use ($service) {
            $inner->whereExists(
                fn ($sub) => $sub->selectRaw('1')->from('consultation_services as cs_pick')
                    ->whereColumn('cs_pick.document_issuance_id', 'document_issuances.id')
                    ->where('cs_pick.service_type_id', $service->id)
            );

            if ($condition = self::condition($service->auto_rule)) {
                $inner->orWhereRaw($condition);
            }
        });
    }
}
