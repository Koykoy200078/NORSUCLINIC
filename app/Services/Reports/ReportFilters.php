<?php

namespace App\Services\Reports;

use Illuminate\Http\Request;

/**
 * The filters of the Report Generation screen, in one place. The on-screen tables and the CSV / PDF / Excel
 * exports build their queries from the same object, so a file always contains what the screen shows.
 *
 * The first group applies to every tab. The second group describes the patient and the visit and applies to the
 * Patient Visits tab and to the ACCOMPLISHMENT REPORT (see ConsultationFilter).
 */
final class ReportFilters
{
    public const CONSULT_MODES = ['all', 'physical', 'virtual'];

    public const PREGNANCY = ['all', 'pregnant', 'not_pregnant'];

    public const CHRONIC = ['all', 'with', 'none'];

    public const GENDERS = ['Male', 'Female'];

    /** key => [label, youngest, oldest] (an age is whole years on the day of the visit) */
    public const AGE_GROUPS = [
        'child' => ['Child (0-12)', 0, 12],
        'teen' => ['Teen (13-17)', 13, 17],
        'young_adult' => ['18-24', 18, 24],
        'adult' => ['25-34', 25, 34],
        'middle_age' => ['35-49', 35, 49],
        'older_adult' => ['50-59', 50, 59],
        'senior' => ['60 and above', 60, 150],
    ];

    /** Query-string name => property, for the id style filters. */
    private const ID_FILTERS = [
        'campus_id' => 'campusId',
        'college_id' => 'collegeId',
        'course_id' => 'courseId',
        'year_level_id' => 'yearLevelId',
        'department_id' => 'departmentId',
        'office_id' => 'officeId',
        'patient_type_id' => 'patientTypeId',
        'illness_system_id' => 'illnessSystemId',
        'illness_id' => 'illnessId',
        'service_id' => 'serviceId',
        'medicine_id' => 'medicineId',
        'staff_id' => 'staffId',
    ];

    public function __construct(
        public ?string $search = null,
        public ?string $dateFrom = null,
        public ?string $dateTo = null,
        public string $userType = 'all',
        public string $action = 'all',
        public string $status = 'all',
        public ?int $campusId = null,
        public ?int $collegeId = null,
        public ?int $courseId = null,
        public ?int $yearLevelId = null,
        public ?int $departmentId = null,
        public ?int $officeId = null,
        public ?int $patientTypeId = null,
        public ?string $gender = null,
        public string $consultMode = 'all',
        public ?int $illnessSystemId = null,
        public ?int $illnessId = null,
        public bool $unclassified = false,
        public ?int $serviceId = null,
        public ?int $medicineId = null,
        public ?string $ageGroup = null,
        public ?int $staffId = null,
        public string $pregnancy = 'all',
        public string $chronic = 'all',
        public ?string $chronicText = null,
    ) {
    }

    /**
     * @param  array<string, mixed>  $input  keys as used by the screen and the export links
     */
    public static function fromArray(array $input): self
    {
        $ids = [];
        foreach (self::ID_FILTERS as $key => $property) {
            $ids[$property] = self::id($input[$key] ?? null);
        }

        // "none" in the illness box means "visits that have no illness picked yet".
        $unclassified = self::text($input['illness_id'] ?? null) === 'none';

        $gender = self::text($input['gender'] ?? null);
        $ageGroup = self::text($input['age_group'] ?? null);

        return new self(
            search: self::text($input['search'] ?? null),
            dateFrom: self::date($input['date_from'] ?? null),
            dateTo: self::date($input['date_to'] ?? null),
            userType: self::text($input['user_type'] ?? null) ?? 'all',
            action: self::text($input['action'] ?? null) ?? 'all',
            status: self::text($input['status'] ?? null) ?? 'all',
            campusId: $ids['campusId'],
            collegeId: $ids['collegeId'],
            courseId: $ids['courseId'],
            yearLevelId: $ids['yearLevelId'],
            departmentId: $ids['departmentId'],
            officeId: $ids['officeId'],
            patientTypeId: $ids['patientTypeId'],
            gender: in_array($gender, self::GENDERS, true) ? $gender : null,
            consultMode: self::choice($input['consult_mode'] ?? null, self::CONSULT_MODES),
            illnessSystemId: $ids['illnessSystemId'],
            illnessId: $unclassified ? null : $ids['illnessId'],
            unclassified: $unclassified,
            serviceId: $ids['serviceId'],
            medicineId: $ids['medicineId'],
            ageGroup: isset(self::AGE_GROUPS[$ageGroup]) ? $ageGroup : null,
            staffId: $ids['staffId'],
            pregnancy: self::choice($input['pregnancy'] ?? null, self::PREGNANCY),
            chronic: self::choice($input['chronic'] ?? null, self::CHRONIC),
            chronicText: self::text($input['chronic_text'] ?? null),
        );
    }

    public static function fromRequest(Request $request): self
    {
        return self::fromArray($request->all());
    }

    /**
     * The non-default values, as query-string parameters for an export link.
     *
     * @return array<string, string|int>
     */
    public function toQuery(): array
    {
        $query = [
            'search' => $this->search,
            'date_from' => $this->dateFrom,
            'date_to' => $this->dateTo,
            'user_type' => $this->userType !== 'all' ? $this->userType : null,
            'action' => $this->action !== 'all' ? $this->action : null,
            'status' => $this->status !== 'all' ? $this->status : null,
        ];

        foreach (self::ID_FILTERS as $key => $property) {
            $query[$key] = $this->{$property};
        }

        if ($this->unclassified) {
            $query['illness_id'] = 'none';
        }

        $query += [
            'gender' => $this->gender,
            'consult_mode' => $this->consultMode !== 'all' ? $this->consultMode : null,
            'age_group' => $this->ageGroup,
            'pregnancy' => $this->pregnancy !== 'all' ? $this->pregnancy : null,
            'chronic' => $this->chronic !== 'all' ? $this->chronic : null,
            'chronic_text' => $this->chronicText,
        ];

        return array_filter($query, fn ($value) => $value !== null && $value !== '');
    }

    /** How many of the patient / visit filters are switched on (the Filters button shows this number). */
    public function activeConsultationFilters(): int
    {
        $query = $this->toQuery();

        return count(array_intersect_key($query, array_flip([
            ...array_keys(self::ID_FILTERS), 'gender', 'consult_mode', 'age_group', 'pregnancy', 'chronic', 'chronic_text',
        ])));
    }

    private static function text(mixed $value): ?string
    {
        if (! is_string($value) && ! is_numeric($value)) {
            return null;
        }

        $value = trim((string) $value);

        return $value === '' ? null : $value;
    }

    /** A record id: only a plain positive whole number is accepted. */
    private static function id(mixed $value): ?int
    {
        $value = self::text($value);

        return $value !== null && ctype_digit($value) && (int) $value > 0 ? (int) $value : null;
    }

    /** @param  array<int, string>  $allowed  the first one is the default */
    private static function choice(mixed $value, array $allowed): string
    {
        $value = self::text($value);

        return in_array($value, $allowed, true) ? $value : $allowed[0];
    }

    /** Only a real Y-m-d date is accepted; anything else is ignored instead of reaching the query. */
    private static function date(mixed $value): ?string
    {
        $value = self::text($value);

        if ($value === null || ! preg_match('/^\d{4}-\d{2}-\d{2}$/', $value)) {
            return null;
        }

        [$year, $month, $day] = array_map('intval', explode('-', $value));

        return checkdate($month, $day, $year) ? $value : null;
    }
}
