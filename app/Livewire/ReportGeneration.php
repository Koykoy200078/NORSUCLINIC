<?php

namespace App\Livewire;

use App\Models\ActivityLog;
use App\Models\Campus;
use App\Models\College;
use App\Models\Course;
use App\Models\Department;
use App\Models\DocumentIssuance;
use App\Models\IllnessSystem;
use App\Models\Medicine;
use App\Models\Office;
use App\Models\Patient;
use App\Models\PatientType;
use App\Models\Prescription;
use App\Models\ServiceType;
use App\Models\User;
use App\Models\YearLevel;
use App\Services\Reports\AccomplishmentReportBuilder;
use App\Services\Reports\ReportFilters;
use App\Services\Reports\ReportQueries;
use App\Support\SearchTerm;
use Carbon\Carbon;
use Livewire\Component;
use Livewire\WithPagination;

class ReportGeneration extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    /** The tabs that describe the patient and the visit (they share the second group of filters). */
    private const CONSULTATION_TABS = ['visits', 'accomplishment'];

    public $tab = 'logs';
    public $search = '';
    public $user_type = 'all';
    public $action = 'all';
    public $status = 'all';
    public $date_from = '';
    public $date_to = '';

    // Patient / visit filters (Patient Visits and Accomplishment Report). '' means "any".
    public $month = '';
    public $campus_id = '';
    public $college_id = '';
    public $course_id = '';
    public $year_level_id = '';
    public $department_id = '';
    public $office_id = '';
    public $patient_type_id = '';
    public $gender = '';
    public $consult_mode = 'all';
    public $illness_system_id = '';
    public $illness_id = '';
    public $service_id = '';
    public $medicine_id = '';
    public $age_group = '';
    public $staff_id = '';
    public $pregnancy = 'all';
    public $chronic = 'all';
    public $chronic_text = '';

    /** True while "Walk-in" is only the Accomplishment Report's own default (not something the user picked). */
    public bool $consult_mode_auto = false;

    protected $queryString = [
        'tab' => ['except' => 'logs'],
        'search' => ['except' => ''],
        'user_type' => ['except' => 'all'],
        'action' => ['except' => 'all'],
        'status' => ['except' => 'all'],
        'date_from' => ['except' => ''],
        'date_to' => ['except' => ''],
        'campus_id' => ['except' => ''],
        'college_id' => ['except' => ''],
        'course_id' => ['except' => ''],
        'year_level_id' => ['except' => ''],
        'department_id' => ['except' => ''],
        'office_id' => ['except' => ''],
        'patient_type_id' => ['except' => ''],
        'gender' => ['except' => ''],
        'consult_mode' => ['except' => 'all'],
        'illness_system_id' => ['except' => ''],
        'illness_id' => ['except' => ''],
        'service_id' => ['except' => ''],
        'medicine_id' => ['except' => ''],
        'age_group' => ['except' => ''],
        'staff_id' => ['except' => ''],
        'pregnancy' => ['except' => 'all'],
        'chronic' => ['except' => 'all'],
        'chronic_text' => ['except' => ''],
    ];

    /** The property names of the patient / visit filters. */
    private const CONSULTATION_FILTERS = [
        'campus_id', 'college_id', 'course_id', 'year_level_id', 'department_id', 'office_id', 'patient_type_id', 'gender',
        'consult_mode', 'illness_system_id', 'illness_id', 'service_id', 'medicine_id', 'age_group', 'staff_id',
        'pregnancy', 'chronic', 'chronic_text',
    ];

    public function mount()
    {
        // Use request values if present
        $this->tab = $this->permittedTab(request('tab', 'logs'));
        $this->search = request('search', '');
        $this->user_type = request('user_type', 'all');
        $this->action = request('action', 'all');
        $this->status = request('status', 'all');
        $this->date_from = request('date_from', '');
        $this->date_to = request('date_to', '');

        foreach (self::CONSULTATION_FILTERS as $property) {
            $this->{$property} = request($property, $this->{$property});
        }

        // The report counts walk-in consultations unless the filter is changed.
        if ($this->tab === 'accomplishment' && ! request()->has('consult_mode')) {
            $this->consult_mode = 'physical';
            $this->consult_mode_auto = true;
        }
    }

    public function setTab($tab)
    {
        $this->tab = $this->permittedTab((string) $tab);

        if ($this->tab === 'accomplishment' && $this->consult_mode === 'all') {
            $this->consult_mode = 'physical';
            $this->consult_mode_auto = true;
        } elseif ($this->tab !== 'accomplishment' && $this->consult_mode_auto) {
            // Leaving the report: the other tabs show every consultation again unless Walk-in was picked on purpose.
            $this->consult_mode = 'all';
            $this->consult_mode_auto = false;
        }

        $this->resetPage();
    }

    /**
     * Report and notification tabs are available to every signed-in clinic role.
     * Check component access as well as the page route on Livewire updates.
     */
    private function permittedTab(?string $tab): string
    {
        $tab = $tab ?: 'logs';

        if (canViewActivityLogTab($tab)) {
            return $tab;
        }

        return canViewActivityLogTab('visits') ? 'visits' : 'global_search';
    }

    public function resetFilters()
    {
        $this->reset(['search', 'user_type', 'action', 'status', 'date_from', 'date_to', 'month', ...self::CONSULTATION_FILTERS]);

        $this->consult_mode_auto = false;
        if ($this->tab === 'accomplishment') {
            $this->consult_mode = 'physical';
            $this->consult_mode_auto = true;
        }

        $this->resetPage();
    }

    /** A hand-picked consult mode is the user's own choice and stays when the tab changes. */
    public function updatedConsultMode()
    {
        $this->consult_mode_auto = false;
    }

    public function updatedSearch()
    {
        $this->resetPage();
    }

    /** Any filter change goes back to the first page of the list. */
    public function updated($property)
    {
        if ($property !== 'search') {
            $this->resetPage();
        }
    }

    /** Quick date ranges: this / last month, this / last year. */
    public function setPeriod(string $period)
    {
        $now = Carbon::now();

        [$from, $to] = match ($period) {
            'this_month' => [$now->copy()->startOfMonth(), $now->copy()->endOfMonth()],
            'last_month' => [$now->copy()->subMonthNoOverflow()->startOfMonth(), $now->copy()->subMonthNoOverflow()->endOfMonth()],
            'this_year' => [$now->copy()->startOfYear(), $now->copy()->endOfYear()],
            'last_year' => [$now->copy()->subYear()->startOfYear(), $now->copy()->subYear()->endOfYear()],
            default => [null, null],
        };

        $this->month = '';
        $this->date_from = $from?->toDateString() ?? '';
        $this->date_to = $to?->toDateString() ?? '';
        $this->resetPage();
    }

    /** The month picker ("2026-03") fills the date range with that whole month. */
    public function updatedMonth($value)
    {
        if (is_string($value) && preg_match('/^(\d{4})-(\d{2})$/', $value, $match) && checkdate((int) $match[2], 1, (int) $match[1])) {
            $start = Carbon::create((int) $match[1], (int) $match[2], 1);
            $this->date_from = $start->toDateString();
            $this->date_to = $start->copy()->endOfMonth()->toDateString();
        }
    }

    /**
     * The filters as one object, shared with the CSV / PDF / Excel exports so a file contains what the screen shows.
     */
    public function reportFilters(): ReportFilters
    {
        return ReportFilters::fromArray([
            'search' => $this->search,
            'date_from' => $this->date_from,
            'date_to' => $this->date_to,
            'user_type' => $this->user_type,
            'action' => $this->action,
            'status' => $this->status,
        ] + collect(self::CONSULTATION_FILTERS)->mapWithKeys(fn ($property) => [$property => $this->{$property}])->all());
    }

    /** The lists behind the filter boxes of the Patient Visits / Accomplishment Report tabs. */
    private function filterChoices(): array
    {
        $staffIds = fn (string $column) => DocumentIssuance::query()
            ->where('document_type', 'consultation_form')->whereNotNull($column)->select($column);

        return [
            'campuses' => Campus::orderBy('campus_name')->pluck('campus_name', 'id'),
            'colleges' => College::orderBy('college_name')->pluck('college_name', 'id'),
            'courses' => Course::orderBy('course_name')->pluck('course_name', 'id'),
            'yearLevels' => YearLevel::orderBy('id')->pluck('year_level_name', 'id'),
            'departments' => Department::orderBy('department_name')->pluck('department_name', 'id'),
            'offices' => Office::orderBy('office_name')->pluck('office_name', 'id'),
            'patientTypes' => PatientType::orderBy('id')->pluck('name', 'id'),
            'illnessSystems' => IllnessSystem::ordered()->with('illnesses')->get(),
            'serviceGroups' => ServiceType::ordered()->get()->groupBy('category')
                ->mapWithKeys(fn ($services, $category) => [ServiceType::CATEGORY_LABELS[$category] ?? $category => $services]),
            'medicines' => Medicine::orderBy('name')->pluck('name', 'id'),
            'staffMembers' => User::query()
                ->whereIn('id', $staffIds('document_creator_id'))
                ->orWhereIn('id', $staffIds('nursing_incharged_id'))
                ->orderBy('first_name')->orderBy('last_name')
                ->get(['id', 'first_name', 'middle_name', 'last_name'])
                ->mapWithKeys(fn (User $user) => [$user->id => $user->full_name]),
            'ageGroups' => ReportFilters::AGE_GROUPS,
        ];
    }

    public function render()
    {
        $data = [];
        $actions = ActivityLog::select('action')->distinct()->whereNotIn('action', ['created_patient'])->pluck('action')->toArray();
        $filters = $this->reportFilters();

        switch ($this->tab) {
            case 'logs':
                $data['activityLogs'] = ReportQueries::logs($filters)->paginate(20);
                break;

            case 'visits':
            case 'inventory':
            case 'dispensing':
            case 'appointments':
                $data['reports'] = ReportQueries::forTab($this->tab, $filters)->paginate(20);
                break;

            case 'accomplishment':
                $data['accomplishment'] = app(AccomplishmentReportBuilder::class)->build($filters, auth()->user());
                break;

            case 'global_search':
                // Reports are open to every staff account and doctor, but this tab finds records of other modules: it
                // only searches (and links to) the modules the signed-in role may use.
                $data['searchModules'] = [
                    'patients' => canUseModule('patients'),
                    'prescriptions' => canUseModule('prescriptions'),
                    'inventory' => canUseModule('inventory'),
                ];

                if ($this->search) {
                    $person = fn ($inner, $word) => $inner->where(fn ($p) => SearchTerm::wordInColumns($p, $word, SearchTerm::PERSON_COLUMNS));

                    if ($data['searchModules']['patients']) {
                        $data['patients'] = SearchTerm::whereAllWords(Patient::with('user'), $this->search, [
                            fn ($inner, $word) => $inner->whereHas('user', fn ($u) => $person($u, $word)),
                            'patient_unique_id',
                        ])->limit(10)->get();
                    }
                    if ($data['searchModules']['prescriptions']) {
                        $data['prescriptions'] = SearchTerm::whereAllWords(Prescription::with('patient.user'), $this->search, [
                            fn ($inner, $word) => $inner->whereHas('patient.user', fn ($u) => $person($u, $word)),
                        ])->limit(10)->get();
                    }
                    if ($data['searchModules']['inventory']) {
                        $data['inventory'] = SearchTerm::whereAllWords(Medicine::query(), $this->search, ['name'])->limit(10)->get();
                    }
                    $data['global_reports'] = canViewActivityLogTab('logs')
                        ? SearchTerm::whereAllWords(ActivityLog::query(), $this->search, ['patient_name', 'description'])->limit(10)->get()
                        : collect();
                }
                break;
        }

        return view('livewire.report-generation', array_merge($data, [
            'actions' => $actions,
            'choices' => in_array($this->tab, self::CONSULTATION_TABS, true) ? $this->filterChoices() : [],
            'activeFilters' => $filters->activeConsultationFilters(),
            // Query-string values of the Export CSV link: the same filters the table above is using.
            'exportQuery' => $filters->toQuery() + ['tab' => $this->tab],
        ]));
    }
}
