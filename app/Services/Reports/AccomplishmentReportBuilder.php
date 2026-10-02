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
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

/**
 * Builds the ACCOMPLISHMENT REPORT: how many consultations there were for each illness (by body system) and for
 * each service, per college column. The consultations come from ConsultationFilter, the same filters the Patient
 * Visits tab uses, so the matrix always agrees with the list.
 *
 * Column of a visit: a student is counted under the college they were in on the day; faculty and staff under
 * F&S; a guest under Guests/Others; a student with no college under Unspecified.
 * One count per consultation and picked illness (a visit with two illnesses adds to both rows); a service counts
 * a consultation once, whether it was ticked by hand, recognised by its rule, or both (ServiceRules).
 *
 * The result is plain arrays so the screen, the PDF, the Excel file and the CSV all render the same data.
 */
final class AccomplishmentReportBuilder
{
    public const FACULTY_STAFF = 'fs';

    public const GUEST = 'guest';

    public const UNSPECIFIED = 'unspecified';

    /**
     * @return array<string, mixed>
     */
    public function build(ReportFilters $filters, ?User $preparedBy = null): array
    {
        $columns = $this->collegeColumns();
        $known = array_flip(array_column($columns, 'key'));
        $known[self::FACULTY_STAFF] = true;
        $known[self::GUEST] = true;

        $illnessCounts = $this->illnessCounts($filters, $known);
        $serviceCounts = $this->serviceCounts($filters, $known);

        $illnessSections = $this->illnessSections($filters, $illnessCounts);
        $serviceSections = $this->serviceSections($filters, $serviceCounts);

        $illnessTotals = $this->sumRows(collect($illnessSections)->pluck('counts'));
        $serviceTotals = $this->sumRows(collect($serviceSections)->pluck('counts'));

        $columns[] = ['key' => self::FACULTY_STAFF, 'label' => 'F&S', 'name' => 'Faculty and staff'];

        // Guests and unclassified affiliations only get a column when the report has someone in it.
        foreach ([self::GUEST => ['Guests/Others', 'Guests and others'], self::UNSPECIFIED => ['Unspecified', 'College not recorded']] as $key => [$label, $name]) {
            $used = ($illnessTotals[$key] ?? 0) + ($serviceTotals[$key] ?? 0) > 0;
            if ($used) {
                $columns[] = ['key' => $key, 'label' => $label, 'name' => $name];
            }
        }

        $consultations = $this->base($filters)->count();

        return [
            'columns' => $columns,
            'illness_sections' => $illnessSections,
            'illness_total' => $this->total($illnessTotals),
            'service_sections' => $serviceSections,
            'service_total' => $this->total($serviceTotals),
            'consultations' => $consultations,
            'unclassified' => $this->base($filters)->whereRaw('NOT ' . ServiceRules::hasIllness())->count(),
            'period' => $this->periodLabel($filters),
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

    /** The column a consultation is counted in, as SQL (needs the patient_types join named pt_col). */
    private function bucketSql(): string
    {
        return "CASE WHEN pt_col.code IN ('faculty', 'staff') THEN '" . self::FACULTY_STAFF . "'"
            . " WHEN pt_col.code = 'guest' THEN '" . self::GUEST . "'"
            . " WHEN document_issuances.college_id IS NOT NULL THEN CONCAT('c', document_issuances.college_id)"
            . " ELSE '" . self::UNSPECIFIED . "' END";
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
    private function illnessCounts(ReportFilters $filters, array $known): array
    {
        $query = $this->base($filters)
            ->leftJoin('patient_types as pt_col', 'pt_col.id', '=', 'document_issuances.patient_type_id')
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
            ->selectRaw('ci.illness_id as item_id, ' . $this->bucketSql() . ' as bucket, COUNT(*) as total')
            ->groupBy('ci.illness_id', 'bucket')
            ->get();

        return $this->countsByItem($rows, $known);
    }

    /**
     * @param  array<string, true>  $known
     * @return array<int, array<string, int>>  service id => column key => count
     */
    private function serviceCounts(ReportFilters $filters, array $known): array
    {
        $counts = [];

        $services = ServiceType::query()->ordered();
        if ($filters->serviceId !== null) {
            $services->whereKey($filters->serviceId);
        }

        foreach ($services->get() as $service) {
            $query = $this->base($filters)
                ->leftJoin('patient_types as pt_col', 'pt_col.id', '=', 'document_issuances.patient_type_id');

            // The service filter itself already narrowed the visits to this service; the other lines still count.
            ServiceRules::matching($query, $service);

            $rows = $query->toBase()
                ->selectRaw($service->id . ' as item_id, ' . $this->bucketSql() . ' as bucket, COUNT(*) as total')
                ->groupBy('bucket')
                ->get();

            $counts += $this->countsByItem($rows, $known);
        }

        return $counts;
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

    /** @return array<int, array<string, mixed>> */
    private function illnessSections(ReportFilters $filters, array $counts): array
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

                $groups[$illness->group_label ?? ''][] = [
                    'name' => $illness->name,
                    'is_other' => (bool) $illness->is_other,
                    'counts' => $rowCounts,
                    'total' => $total,
                ];
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

    /** @return array<int, array<string, mixed>> */
    private function serviceSections(ReportFilters $filters, array $counts): array
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

                $rows[] = ['name' => $service->name, 'is_other' => (bool) $service->is_other, 'auto' => $service->auto_rule !== null, 'counts' => $rowCounts, 'total' => $total];
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

        $user->loadMissing('staffProfile.roleDesignation');

        $title = $user->staffProfile?->roleDesignation?->name
            ?? match ($user->roles->first()?->name) {
                'clinic_admin' => 'Clinic Administrator',
                'doctor' => 'Doctor',
                'nurse' => 'Nurse',
                'staff' => 'Clinic Staff',
                default => '',
            };

        return ['name' => $user->full_name, 'title' => (string) $title];
    }
}
