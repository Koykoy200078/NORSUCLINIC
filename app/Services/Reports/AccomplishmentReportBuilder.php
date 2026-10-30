<?php

namespace App\Services\Reports;

use App\Models\Campus;
use App\Models\College;
use App\Models\Course;
use App\Models\Department;
use App\Models\DocumentIssuance;
use App\Models\Illness;
use App\Models\IllnessSystem;
use App\Models\Medicine;
use App\Models\Office;
use App\Models\PatientType;
use App\Models\ServiceType;
use App\Models\User;
use App\Models\YearLevel;
use App\Support\OtherName;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;

/**
 * Builds the ACCOMPLISHMENT REPORT: how many consultations there were for each illness (by body system) and for
 * each service, per college column. The consultations come from ConsultationFilter, the same filters the Patient
 * Visits tab uses, so the matrix always agrees with the list.
 *
 * Column of a visit: a student is counted under the college they were in on the day; faculty and staff under
 * F&S; a guest under Guest (always a column); a student with no college under Unspecified (only when used).
 * One count per consultation and picked illness (a visit with two illnesses adds to both rows); a service counts
 * a consultation once, whether it was ticked by hand, recognised by its rule, or both (ServiceRules).
 *
 * An "Others" row also lists the names typed on the consultation form as sub-rows ('others'), each with its own count per
 * column; names that differ only by case, extra spaces or end punctuation are one sub-row (OtherName). The sub-rows add
 * up to the "Others" row, which is what the section and grand totals count, so they are never counted twice.
 *
 * Under the services comes one more line of its own, "Medicine only (no consultation)": the patients who only came for medicine
 * (Meds Only records). It counts one per visit, in the same columns (by the college, F&S, Guest, Unspecified or quarter of the
 * record, by its date), follows the filters that fit such a record (ReportQueries::medsOnlyFiltered), is left out when a filter
 * that cannot match it is on or when nobody took medicine only in the selection, and is never part of the consultation, illness
 * or service totals.
 *
 * The result is plain arrays so the screen, the PDF, the Excel file and the CSV all render the same data.
 */
final class AccomplishmentReportBuilder
{
    public const FACULTY_STAFF = 'fs';

    public const GUEST = 'guest';

    public const UNSPECIFIED = 'unspecified';

    /** The sub-row of an "Others" line for consultations saved without a name (saved before the name was required). */
    public const NO_NAME = '(no name given)';

    /** The name of the extra line for the patients who only took medicine. */
    public const MEDICINE_ONLY = 'Medicine only (no consultation)';

    /**
     * @return array<string, mixed>
     */
    public function build(ReportFilters $filters, ?User $preparedBy = null): array
    {
        // The yearly report (Q1-Q4 and the year total) counts the same consultations with the same rules and filters;
        // only the columns differ and the dates are the whole year.
        $yearly = $filters->year !== null;
        if ($yearly) {
            $filters = $this->wholeYear($filters);
        }

        $columns = $yearly ? $this->quarterColumns() : $this->collegeColumns();
        $known = array_flip(array_column($columns, 'key'));
        if (! $yearly) {
            $known[self::FACULTY_STAFF] = true;
            $known[self::GUEST] = true;
        }
        $bucketSql = $yearly ? $this->quarterBucketSql() : $this->bucketSql();

        $illnessCounts = $this->illnessCounts($filters, $known, $bucketSql);
        $serviceCounts = $this->serviceCounts($filters, $known, $bucketSql);

        $illnessSections = $this->illnessSections($filters, $illnessCounts, $this->illnessOtherNames($filters, $known, $bucketSql));
        $serviceSections = $this->serviceSections($filters, $serviceCounts, $this->serviceOtherNames($filters, $known, $bucketSql));

        $illnessTotals = $this->sumRows(collect($illnessSections)->pluck('counts'));
        $serviceTotals = $this->sumRows(collect($serviceSections)->pluck('counts'));
        $medicineOnly = $this->medicineOnly($filters, $known, $yearly);

        if (! $yearly) {
            $columns[] = ['key' => self::FACULTY_STAFF, 'label' => 'F&S', 'name' => 'Faculty and staff'];

            // Guest is always a column, even when nobody is in it (a report handed in with no guest still has it, at 0).
            $columns[] = ['key' => self::GUEST, 'label' => 'Guest', 'name' => 'Guests and others'];

            // Visits whose college was not recorded only get a column when the report has someone in it.
            if (($illnessTotals[self::UNSPECIFIED] ?? 0) + ($serviceTotals[self::UNSPECIFIED] ?? 0) + ($medicineOnly['counts'][self::UNSPECIFIED] ?? 0) > 0) {
                $columns[] = ['key' => self::UNSPECIFIED, 'label' => 'Unspecified', 'name' => 'College not recorded'];
            }
        }

        $consultations = $this->base($filters)->count();

        return [
            'columns' => $columns,
            'total_label' => $yearly ? 'YEAR TOTAL' : 'TOTAL',
            'basis' => $yearly ? 'by quarter' : 'as per college/department',
            'year' => $yearly ? $filters->year : null,
            'illness_sections' => $illnessSections,
            'illness_total' => $this->total($illnessTotals),
            'service_sections' => $serviceSections,
            'service_total' => $this->total($serviceTotals),
            'medicine_only' => $medicineOnly,
            'consultations' => $consultations,
            'unclassified' => $this->base($filters)->whereRaw('NOT ' . ServiceRules::hasIllness())->count(),
            'period' => $yearly ? 'for the year ' . $filters->year : $this->periodLabel($filters),
            'filter_summary' => $this->filterSummary($filters),
            'prepared_by' => $this->preparedBy($preparedBy),
            'noted_by' => [
                'name' => (string) getSettingValue('university_physician_name'),
                'title' => (string) (getSettingValue('university_physician_title') ?: 'University Physician'),
            ],
            'generated_at' => now(),
        ];
    }

    /** The consultations of the report, with the filters applied. */
    private function base(ReportFilters $filters): Builder
    {
        return ConsultationFilter::apply(DocumentIssuance::query(), $filters);
    }

    /** The column a consultation is counted in, using the same patient-type rule as the report filters. */
    private function bucketSql(): string
    {
        $type = '('.ConsultationPatientType::codeSql().')';

        return "CASE WHEN {$type} IN ('faculty', 'staff') THEN '" . self::FACULTY_STAFF . "'"
            . " WHEN {$type} = 'guest' THEN '" . self::GUEST . "'"
            . " WHEN document_issuances.college_id IS NOT NULL THEN CONCAT('c', document_issuances.college_id)"
            . " ELSE '" . self::UNSPECIFIED . "' END";
    }

    /** The filters of the yearly report: the whole calendar year replaces whatever dates were typed on the screen. */
    private function wholeYear(ReportFilters $filters): ReportFilters
    {
        $yearly = clone $filters;
        $yearly->dateFrom = $filters->year . '-01-01';
        $yearly->dateTo = $filters->year . '-12-31';

        return $yearly;
    }

    /** The four columns of the yearly report. */
    private function quarterColumns(): array
    {
        return [
            ['key' => 'q1', 'label' => 'Q1', 'name' => 'January - March'],
            ['key' => 'q2', 'label' => 'Q2', 'name' => 'April - June'],
            ['key' => 'q3', 'label' => 'Q3', 'name' => 'July - September'],
            ['key' => 'q4', 'label' => 'Q4', 'name' => 'October - December'],
        ];
    }

    /** The quarter a consultation is counted in (by its visit date), as SQL: q1 .. q4. */
    private function quarterBucketSql(): string
    {
        return "CONCAT('q', QUARTER(" . ServiceRules::visitDate() . '))';
    }

    /** One column per college of the system (abbreviation as heading); Graduate School goes last. */
    private function collegeColumns(): array
    {
        $colleges = College::orderBy('id')->get();
        $graduate = $colleges->filter(fn (College $college) => $college->abbreviation === 'GS');

        return $colleges->reject(fn (College $college) => $graduate->contains('id', $college->id))
            ->concat($graduate)
            ->map(fn (College $college) => ['key' => 'c' . $college->id, 'label' => $college->abbreviation, 'name' => $college->college_name])
            ->values()
            ->all();
    }

    /**
     * @param  array<string, true>  $known  column keys that exist
     * @return array<int, array<string, int>>  illness id => column key => count
     */
    private function illnessCounts(ReportFilters $filters, array $known, string $bucketSql): array
    {
        $query = $this->base($filters)
            ->join('consultation_illnesses as ci', 'ci.document_issuance_id', '=', 'document_issuances.id')
            ->join('illnesses as i', 'i.id', '=', 'ci.illness_id');

        // A filter on an illness or a body system also narrows the rows, not only the visits.
        if ($filters->illnessSystemId !== null) {
            $query->where('i.illness_system_id', $filters->illnessSystemId);
        }
        if ($filters->illnessId !== null) {
            $query->where('i.id', $filters->illnessId);
        }

        $rows = $query->toBase()
            ->selectRaw('ci.illness_id as item_id, ' . $bucketSql . ' as bucket, COUNT(*) as total')
            ->groupBy('ci.illness_id', 'bucket')
            ->get();

        return $this->countsByItem($rows, $known);
    }

    /**
     * @param  array<string, true>  $known
     * @return array<int, array<string, int>>  service id => column key => count
     */
    private function serviceCounts(ReportFilters $filters, array $known, string $bucketSql): array
    {
        $counts = [];

        $services = ServiceType::query()->ordered();
        if ($filters->serviceId !== null) {
            $services->whereKey($filters->serviceId);
        }

        foreach ($services->get() as $service) {
            $query = $this->base($filters);

            // The service filter itself already narrowed the visits to this service; the other lines still count.
            ServiceRules::matching($query, $service);

            $rows = $query->toBase()
                ->selectRaw($service->id . ' as item_id, ' . $bucketSql . ' as bucket, COUNT(*) as total')
                ->groupBy('bucket')
                ->get();

            $counts += $this->countsByItem($rows, $known);
        }

        return $counts;
    }

    /**
     * The "Medicine only (no consultation)" line: how many Meds Only visits there were per column, or null when the line is not
     * shown (a filter that cannot match such a visit is on, or nobody took medicine only in the selection).
     *
     * @param  array<string, true>  $known  column keys that exist
     * @return array{name: string, counts: array<string, int>, total: int}|null
     */
    private function medicineOnly(ReportFilters $filters, array $known, bool $yearly): ?array
    {
        if ($this->medicineOnlyCannotMatch($filters)) {
            return null;
        }

        $rows = ReportQueries::medsOnlyFiltered($filters)->toBase()
            ->selectRaw($this->medicineOnlyBucketSql($yearly) . ' as bucket, COUNT(*) as total')
            ->groupBy('bucket')
            ->get();

        $counts = [];
        foreach ($rows as $row) {
            // A college that is no longer in the list is shown under Unspecified rather than disappearing.
            $bucket = isset($known[$row->bucket]) ? $row->bucket : self::UNSPECIFIED;
            $counts[$bucket] = ($counts[$bucket] ?? 0) + (int) $row->total;
        }

        if ($counts === []) {
            return null;
        }

        return ['name' => self::MEDICINE_ONLY, 'counts' => $counts, 'total' => array_sum($counts)];
    }

    /**
     * Filters on something a Meds Only record does not have (an illness, a service, pregnancy, a chronic condition, age, gender,
     * a department or office, the nurse, a virtual consultation): the line is left out rather than shown for people it does not describe.
     */
    private function medicineOnlyCannotMatch(ReportFilters $filters): bool
    {
        return $filters->illnessSystemId !== null || $filters->illnessId !== null || $filters->unclassified
            || $filters->serviceId !== null || $filters->staffId !== null || $filters->ageGroup !== null || $filters->gender !== null
            || $filters->departmentId !== null || $filters->officeId !== null
            || $filters->pregnancy !== 'all' || $filters->chronic !== 'all' || $filters->chronicText !== null
            // Somebody who comes for medicine is seen at the clinic; walk-in and all are the same for this line.
            || $filters->consultMode === 'virtual';
    }

    /** The column of a Meds Only visit: the same rule as a consultation, from the patient type and college of the record. */
    private function medicineOnlyBucketSql(bool $yearly): string
    {
        if ($yearly) {
            return "CONCAT('q', QUARTER(medicine_bills.bill_date))";
        }

        $type = '(SELECT LOWER(TRIM(meds_only_type.code)) FROM patient_types AS meds_only_type WHERE meds_only_type.id = medicine_bills.patient_type_id)';

        return "CASE WHEN {$type} IN ('faculty', 'staff') THEN '" . self::FACULTY_STAFF . "'"
            . " WHEN {$type} = 'guest' THEN '" . self::GUEST . "'"
            . " WHEN medicine_bills.college_id IS NOT NULL THEN CONCAT('c', medicine_bills.college_id)"
            . " ELSE '" . self::UNSPECIFIED . "' END";
    }

    /**
     * The names typed on the "Others" illness lines: illness id => sub-rows.
     *
     * @param  array<string, true>  $known
     * @return array<int, array<int, array<string, mixed>>>
     */
    private function illnessOtherNames(ReportFilters $filters, array $known, string $bucketSql): array
    {
        $query = $this->base($filters)
            ->join('consultation_illnesses as ci', 'ci.document_issuance_id', '=', 'document_issuances.id')
            ->join('illnesses as i', 'i.id', '=', 'ci.illness_id')
            ->where('i.is_other', true);

        if ($filters->illnessSystemId !== null) {
            $query->where('i.illness_system_id', $filters->illnessSystemId);
        }
        if ($filters->illnessId !== null) {
            $query->where('i.id', $filters->illnessId);
        }

        // BINARY: the column's collation ignores case, and the report picks the most common spelling.
        $rows = $query->toBase()
            ->selectRaw('ci.illness_id as item_id, BINARY ci.other_text as other_text, ' . $bucketSql . ' as bucket, COUNT(*) as total, MIN(document_issuances.id) as first_id')
            ->groupBy('ci.illness_id', DB::raw('BINARY ci.other_text'), 'bucket')
            ->get();

        return $this->otherNames($rows, $known);
    }

    /**
     * The names typed on the "Others" service lines: service id => sub-rows.
     *
     * @param  array<string, true>  $known
     * @return array<int, array<int, array<string, mixed>>>
     */
    private function serviceOtherNames(ReportFilters $filters, array $known, string $bucketSql): array
    {
        $names = [];

        $services = ServiceType::query()->where('is_other', true);
        if ($filters->serviceId !== null) {
            $services->whereKey($filters->serviceId);
        }

        foreach ($services->get() as $service) {
            $rows = $this->base($filters)
                ->join('consultation_services as cs', function ($join) use ($service) {
                    $join->on('cs.document_issuance_id', '=', 'document_issuances.id')->where('cs.service_type_id', '=', $service->id);
                })
                ->toBase()
                ->selectRaw($service->id . ' as item_id, BINARY cs.other_text as other_text, ' . $bucketSql . ' as bucket, COUNT(*) as total, MIN(document_issuances.id) as first_id')
                ->groupBy(DB::raw('BINARY cs.other_text'), 'bucket')
                ->get();

            $names += $this->otherNames($rows, $known);
        }

        return $names;
    }

    /**
     * Turns the (name as typed, column, count) rows of an "Others" line into sub-rows: names are merged by OtherName::key(),
     * the spelling shown is the most common one (a tie goes to the one typed first), the busiest names come first and
     * the consultations saved without a name are the last line.
     *
     * @param  Collection<int, object>  $rows
     * @param  array<string, true>  $known
     * @return array<int, array<int, array<string, mixed>>>  item id => [['name', 'counts', 'total'], ...]
     */
    private function otherNames(Collection $rows, array $known): array
    {
        $merged = [];

        foreach ($rows as $row) {
            $text = $row->other_text === null ? null : OtherName::clean((string) $row->other_text);
            $key = $text === null ? '' : OtherName::key($text);
            $bucket = isset($known[$row->bucket]) ? $row->bucket : self::UNSPECIFIED;
            $item = (int) $row->item_id;

            $line = &$merged[$item][$key];
            $line['counts'][$bucket] = ($line['counts'][$bucket] ?? 0) + (int) $row->total;
            if ($text !== null) {
                $spelling = &$line['spellings'][$text];
                $spelling['count'] = ($spelling['count'] ?? 0) + (int) $row->total;
                $spelling['first'] = min($spelling['first'] ?? PHP_INT_MAX, (int) $row->first_id);
                unset($spelling);
            }
            unset($line);
        }

        $result = [];
        foreach ($merged as $item => $lines) {
            $subRows = [];
            foreach ($lines as $key => $line) {
                $name = self::NO_NAME;
                if ($key !== '') {
                    $spellings = $line['spellings'];
                    uksort($spellings, fn ($a, $b) => [$spellings[$b]['count'], $spellings[$a]['first'], $a] <=> [$spellings[$a]['count'], $spellings[$b]['first'], $b]);
                    $name = (string) array_key_first($spellings);
                }
                $subRows[] = ['name' => $name, 'counts' => $line['counts'], 'total' => array_sum($line['counts']), 'unnamed' => $key === ''];
            }

            usort($subRows, fn ($a, $b) => [$a['unnamed'], -$a['total'], mb_strtolower($a['name'])] <=> [$b['unnamed'], -$b['total'], mb_strtolower($b['name'])]);
            $result[$item] = array_map(fn ($sub) => ['name' => $sub['name'], 'counts' => $sub['counts'], 'total' => $sub['total']], $subRows);
        }

        return $result;
    }

    /**
     * @param  Collection<int, object>  $rows
     * @param  array<string, true>  $known
     * @return array<int, array<string, int>>
     */
    private function countsByItem(Collection $rows, array $known): array
    {
        $counts = [];

        foreach ($rows as $row) {
            // A college that is no longer in the list is shown under Unspecified rather than disappearing.
            $bucket = isset($known[$row->bucket]) ? $row->bucket : self::UNSPECIFIED;
            $counts[(int) $row->item_id][$bucket] = ($counts[(int) $row->item_id][$bucket] ?? 0) + (int) $row->total;
        }

        return $counts;
    }

    /**
     * @param  array<int, array<int, array<string, mixed>>>  $otherNames  illness id => sub-rows of its "Others" line
     * @return array<int, array<string, mixed>>
     */
    private function illnessSections(ReportFilters $filters, array $counts, array $otherNames = []): array
    {
        $sections = [];

        foreach (IllnessSystem::ordered()->with('illnesses')->get() as $system) {
            $groups = [];
            $systemCounts = [];

            foreach ($system->illnesses as $illness) {
                $rowCounts = $counts[$illness->id] ?? [];
                $total = array_sum($rowCounts);

                // A line the clinic switched off stays out of the report unless it has history.
                if (! $illness->is_active && $total === 0) {
                    continue;
                }
                if ($this->rowHidden($filters, $illness, $total)) {
                    continue;
                }

                $row = [
                    'name' => $illness->name,
                    'is_other' => (bool) $illness->is_other,
                    'counts' => $rowCounts,
                    'total' => $total,
                ];
                if ($illness->is_other) {
                    $row['others'] = $otherNames[$illness->id] ?? [];
                }
                $groups[$illness->group_label ?? ''][] = $row;
                $systemCounts[] = $rowCounts;
            }

            if ($groups === []) {
                continue;
            }

            $sections[] = [
                'name' => $system->name,
                'groups' => collect($groups)->map(fn ($rows, $label) => ['label' => $label !== '' ? $label : null, 'rows' => $rows])->values()->all(),
                'counts' => $this->sumRows(collect($systemCounts)),
                'total' => array_sum($this->sumRows(collect($systemCounts))),
            ];
        }

        return $sections;
    }

    /** With an illness / body-system filter on, only the matching lines are listed. */
    private function rowHidden(ReportFilters $filters, Illness $illness, int $total): bool
    {
        if ($filters->illnessId !== null) {
            return $illness->id !== $filters->illnessId;
        }
        if ($filters->illnessSystemId !== null) {
            return $illness->illness_system_id !== $filters->illnessSystemId;
        }

        return false;
    }

    /**
     * @param  array<int, array<int, array<string, mixed>>>  $otherNames  service id => sub-rows of its "Others" line
     * @return array<int, array<string, mixed>>
     */
    private function serviceSections(ReportFilters $filters, array $counts, array $otherNames = []): array
    {
        $sections = [];

        foreach (ServiceType::CATEGORY_LABELS as $category => $label) {
            $rows = [];
            $categoryCounts = [];

            $services = ServiceType::where('category', $category)->ordered();
            if ($filters->serviceId !== null) {
                $services->whereKey($filters->serviceId);
            }

            foreach ($services->get() as $service) {
                $rowCounts = $counts[$service->id] ?? [];
                $total = array_sum($rowCounts);

                if (! $service->is_active && $total === 0) {
                    continue;
                }

                $row = ['name' => $service->name, 'is_other' => (bool) $service->is_other, 'auto' => $service->auto_rule !== null, 'counts' => $rowCounts, 'total' => $total];
                if ($service->is_other) {
                    $row['others'] = $otherNames[$service->id] ?? [];
                }
                $rows[] = $row;
                $categoryCounts[] = $rowCounts;
            }

            if ($rows === []) {
                continue;
            }

            $sections[] = [
                'name' => $label,
                'rows' => $rows,
                'counts' => $this->sumRows(collect($categoryCounts)),
                'total' => array_sum($this->sumRows(collect($categoryCounts))),
            ];
        }

        return $sections;
    }

    /**
     * @param  Collection<int, array<string, int>>  $rows
     * @return array<string, int>
     */
    private function sumRows(Collection $rows): array
    {
        $sum = [];

        foreach ($rows as $row) {
            foreach ($row as $key => $count) {
                $sum[$key] = ($sum[$key] ?? 0) + $count;
            }
        }

        return $sum;
    }

    /** @param  array<string, int>  $counts */
    private function total(array $counts): array
    {
        return ['counts' => $counts, 'total' => array_sum($counts)];
    }

    /** "from January 1, 2020 to December 31, 2020" - as written under the title of the report. */
    private function periodLabel(ReportFilters $filters): string
    {
        $from = $filters->dateFrom ? Carbon::parse($filters->dateFrom) : null;
        $to = $filters->dateTo ? Carbon::parse($filters->dateTo) : null;

        return match (true) {
            $from && $to && $from->isSameDay($to) => 'on ' . $from->format('F j, Y'),
            $from && $to => 'from ' . $from->format('F j, Y') . ' to ' . $to->format('F j, Y'),
            (bool) $from => 'from ' . $from->format('F j, Y') . ' onwards',
            (bool) $to => 'up to ' . $to->format('F j, Y'),
            default => 'for all dates',
        };
    }

    /**
     * The filters in words, for the line under the title ("Campus: Main Campus, Consult mode: Walk-in").
     *
     * @return array<int, string>
     */
    public function filterSummary(ReportFilters $filters): array
    {
        $lookup = fn (string $label, ?int $id, string $model, string $column) => $id !== null
            ? $label . ': ' . ($model::whereKey($id)->value($column) ?? '#' . $id)
            : null;

        $lines = [
            $lookup('Campus', $filters->campusId, Campus::class, 'campus_name'),
            $lookup('College', $filters->collegeId, College::class, 'college_name'),
            $lookup('Course', $filters->courseId, Course::class, 'course_name'),
            $lookup('Year level', $filters->yearLevelId, YearLevel::class, 'year_level_name'),
            $lookup('Department', $filters->departmentId, Department::class, 'department_name'),
            $lookup('Office', $filters->officeId, Office::class, 'office_name'),
            $lookup('Patient type', $filters->patientTypeId, PatientType::class, 'name'),
            $filters->gender ? 'Gender: ' . $filters->gender : null,
            $filters->consultMode !== 'all' ? 'Consult mode: ' . ($filters->consultMode === 'physical' ? 'Walk-in' : 'Virtual') : null,
            $filters->ageGroup ? 'Age: ' . ReportFilters::AGE_GROUPS[$filters->ageGroup][0] : null,
            $lookup('Body system', $filters->illnessSystemId, IllnessSystem::class, 'name'),
            $filters->unclassified ? 'Illness: not classified yet' : $lookup('Illness', $filters->illnessId, Illness::class, 'name'),
            $lookup('Service', $filters->serviceId, ServiceType::class, 'name'),
            $lookup('Medicine given', $filters->medicineId, Medicine::class, 'name'),
            $filters->staffId ? 'Nurse in charge / encoder: ' . (User::find($filters->staffId)?->full_name ?? '#' . $filters->staffId) : null,
            $filters->pregnancy !== 'all' ? 'Pregnancy: ' . ($filters->pregnancy === 'pregnant' ? 'pregnant' : 'not pregnant') : null,
            $filters->chronic !== 'all' ? 'Chronic condition: ' . ($filters->chronic === 'with' ? 'has one' : 'none') : null,
            $filters->chronicText ? 'Chronic condition contains: ' . $filters->chronicText : null,
        ];

        return array_values(array_filter($lines));
    }

    /** @return array{name: string, title: string} */
    private function preparedBy(?User $user): array
    {
        if ($user === null) {
            return ['name' => '', 'title' => ''];
        }

        $user->loadMissing('roles');
        $title = $user->roles->first()?->display_name ?? '';

        return ['name' => $user->full_name, 'title' => (string) $title];
    }
}
