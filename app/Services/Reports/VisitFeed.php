<?php

namespace App\Services\Reports;

use App\Models\DispenseRecord;
use App\Models\DocumentIssuance;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * The Patient Visits list when its "Visit type" is Meds only or All: the consultations and the patients who only came for
 * medicine in one list, newest first (a consultation before a Meds Only visit of the same day, then the newest record).
 *
 * query() is a plain query of (kind, source_id, visit_date) over the two kinds of record, each with the filters both have
 * (ReportFilters::forVisitType) and its own word search, so the screen pages it and the CSV chunks it like any other tab.
 * hydrate() then fills in the 20 (or 200) rows of a page from their records, so the page never loads more than it shows.
 */
final class VisitFeed
{
    public const CONSULTATION = 'consultation';

    public const MEDS_ONLY = 'meds_only';

    public const HEADINGS = ['Date', 'Type', 'Patient Name', 'Patient Type', 'Campus', 'College', 'Course', 'Year Level', 'Illness / Services / Drugs', 'Recorded By'];

    private const LABELS = [self::CONSULTATION => 'Consultation', self::MEDS_ONLY => 'Meds only'];

    public static function query(ReportFilters $filters): QueryBuilder
    {
        $filters = $filters->forVisitType();

        $feed = ReportQueries::medsOnlySearched($filters)->toBase()
            ->selectRaw("'" . self::MEDS_ONLY . "' as kind, medicine_bills.id as source_id, DATE(medicine_bills.bill_date) as visit_date");

        if ($filters->visitType === 'all') {
            $consultations = ReportQueries::visitsSearched($filters)->toBase()
                ->selectRaw("'" . self::CONSULTATION . "' as kind, document_issuances.id as source_id, " . ServiceRules::visitDate() . ' as visit_date');
            $feed = $consultations->unionAll($feed);
        }

        return DB::query()->fromSub($feed, 'visit_feed')
            ->orderByDesc('visit_date')->orderBy('kind')->orderByDesc('source_id');
    }

    /**
     * @param  iterable<int, object>  $rows  (kind, source_id, visit_date) rows of query()
     * @return Collection<int, object> in the same order: key, kind, type, id, date, name, patient_type, campus, college, course,
     *                                 year_level, summary, recorded_by, record (the consultation or the Meds Only record)
     */
    public static function hydrate(iterable $rows): Collection
    {
        $rows = collect($rows);

        $consultations = DocumentIssuance::with(['creator', 'illnesses.system', 'services'])
            ->whereKey($rows->where('kind', self::CONSULTATION)->pluck('source_id')->all())->get()->keyBy('id');
        $records = DispenseRecord::with(['dispenseItems.medicine:id,name,dosage,uom', 'recorder', 'patientType'])
            ->whereKey($rows->where('kind', self::MEDS_ONLY)->pluck('source_id')->all())->get()->keyBy('id');

        return $rows->map(function (object $row) use ($consultations, $records): ?object {
            $date = $row->visit_date ? Carbon::parse($row->visit_date)->format('Y-m-d') : '';

            if ($row->kind === self::CONSULTATION) {
                $visit = $consultations->get($row->source_id);

                return $visit === null ? null : (object) [
                    'key' => self::CONSULTATION . ':' . $visit->id, 'kind' => self::CONSULTATION, 'type' => self::LABELS[self::CONSULTATION],
                    'id' => (int) $visit->id, 'date' => $date, 'name' => (string) $visit->name, 'patient_type' => (string) $visit->informant,
                    'campus' => (string) $visit->campus, 'college' => (string) $visit->college, 'course' => (string) $visit->course, 'year_level' => (string) $visit->year_level,
                    'summary' => implode('; ', array_merge($visit->illnessLabels(), $visit->serviceLabels())),
                    'recorded_by' => (string) ($visit->creator->full_name ?? 'System'), 'record' => $visit,
                ];
            }

            $record = $records->get($row->source_id);

            return $record === null ? null : (object) [
                'key' => self::MEDS_ONLY . ':' . $record->id, 'kind' => self::MEDS_ONLY, 'type' => self::LABELS[self::MEDS_ONLY],
                'id' => (int) $record->id, 'date' => $date, 'name' => (string) $record->patient_name, 'patient_type' => (string) ($record->patientType?->name ?? ''),
                'campus' => (string) $record->campus, 'college' => (string) $record->college, 'course' => (string) $record->course, 'year_level' => (string) $record->year_level,
                'summary' => $record->medicineSummary(),
                'recorded_by' => (string) ($record->recorder?->full_name ?? ''), 'record' => $record,
            ];
        })->filter()->values();
    }

    /**
     * One visit as a line of the CSV (the columns of HEADINGS).
     *
     * @return array<int, string>
     */
    public static function csvRow(object $visit): array
    {
        return [$visit->date, $visit->type, $visit->name, $visit->patient_type, $visit->campus, $visit->college, $visit->course, $visit->year_level, $visit->summary, $visit->recorded_by];
    }
}
