<?php

namespace App\Livewire;

use Carbon\Carbon;
use App\Models\Patient;
use Illuminate\Database\Eloquent\Builder;
use Rappasoft\LaravelLivewireTables\Views\Column;
use Livewire\Attributes\Lazy;

#[Lazy]
class PatientTable extends LivewireTableComponent
{
    protected $model = Patient::class;

    public bool $showButtonOnHeader = true;

    public ?string $module = null;

    protected string $tableName = 'patients';

    public string $buttonComponent = 'patients.components.add_button';

    public bool $showFilterOnHeader = true;

    public array $FilterComponent = ['patients.components.filter', Patient::PATIENT_FILTER];

    protected $listeners = ['refresh' => '$refresh', 'resetPage', 'changeDateFilter', 'changeStatusFilter'];

    public string $dateFilter = '';

    public string $statusFilter = 'active';

    public function mount(?string $module = null): void
    {
        $this->module = $module ?? request()->query('module');
        $this->showButtonOnHeader = ! $this->isPrescriptionModule();
        $this->initializeDefaultStatusFilter();
        $this->syncEmptyMessage();
    }

    /**
     * Configure the table settings.
     */
    public function configure(): void
    {
        $this->showButtonOnHeader = ! $this->isPrescriptionModule();

        $this->setPrimaryKey('id')
            ->setDefaultSort('created_at', 'desc')
            ->setQueryStringStatus(false);

        $this->syncEmptyMessage();

        $this->setThAttributes(function (Column $column) {
            if ($column->isField('id')) {
                return [
                    'class' => 'text-center',
                ];
            }

            return [];
        });
    }

    /**
     * Build the query for the table.
     */
    public function builder(): Builder
    {
        $query = Patient::with([
            'user' => function ($query) {
                $query->withTrashed()->select('id', 'first_name', 'last_name', 'email', 'email_verified_at', 'year_level_id', 'gender');
            },
            'patientType',
        ])
            ->withCount(['documentIssuances as consultation_form_count' => function ($subQuery) {
                $subQuery->where('document_type', 'consultation_form');
            }]);

        if ($this->statusFilter === 'archived') {
            $query->onlyTrashed();
        }

        if (!empty($this->dateFilter) && $this->dateFilter != getWeekDate()) {
            [$startDate, $endDate] = array_map(function ($date) {
                return Carbon::createFromFormat('d/m/Y', $date)->format('Y-m-d');
            }, explode(' - ', $this->dateFilter));

            $query->whereBetween('patients.created_at', [$startDate, $endDate]);
        }

        return $query;
    }

    /**
     * Define the placeholder view for the table.
     */
    public function placeholder(): string
    {
        return view('livewire.staff_skeleton')->render();
    }

    /**
     * Handle the date filter change.
     */
    public function changeDateFilter($date)
    {
        $this->dateFilter = $date;
        $this->setBuilder($this->builder());
        $this->resetPagination();
    }

    public function changeStatusFilter($payload = null): void
    {
        $value = $this->statusFilter ?: 'active';

        if (is_array($payload)) {
            $value = $payload['value'] ?? $payload['status'] ?? $value;
        } elseif (is_string($payload) && $payload !== '') {
            $value = $payload;
        } elseif ($payload !== null) {
            $value = (string) $payload;
        }

        $this->statusFilter = in_array($value, ['active', 'archived'], true) ? $value : 'active';
        $this->syncEmptyMessage();
        $this->setBuilder($this->builder());
        $this->resetPagination();
    }

    public function updatedStatusFilter($value): void
    {
        $this->changeStatusFilter($value);
    }

    /**
     * Define the columns for the table.
     */
    public function columns(): array
    {
        $actionView = $this->isPrescriptionModule()
            ? 'patients.components.action_prescription'
            : 'patients.components.action';

        return [
            Column::make(__('messages.patient.name'), 'user.first_name')
                ->view('patients.components.name')
                ->sortable()
                ->searchable(function (Builder $query, $direction) {
                    $query->whereHas('user', function (Builder $q) use ($direction) {
                        $q->withTrashed()
                            ->whereRaw("TRIM(CONCAT(first_name, ' ', last_name)) LIKE ?", ["%{$direction}%"]);
                    });
                }),
            Column::make(__('Patient Type'), 'patient_type_id')
                ->view('patients.components.patient_type')
                ->sortable(),
            Column::make(__('Total Consultations'), 'id')
                ->view('patients.components.consultation_form_count'),
            Column::make(__('messages.patient.registered_on'), 'created_at')
                ->sortable()
                ->view('patients.components.registered_on'),
            Column::make(__('messages.common.action'), 'user.id')
                ->view($actionView),
        ];
    }

    /**
     * Reset the pagination for the table.
     */
    public function resetPagination()
    {
        $this->resetPage('patientsPage');
    }

    private function initializeDefaultStatusFilter(): void
    {
        if ($this->statusFilter !== 'active') {
            return;
        }

        $activePatientsCount = Patient::query()->count();
        $archivedPatientsCount = Patient::onlyTrashed()->count();

        if ($activePatientsCount === 0 && $archivedPatientsCount > 0) {
            $this->statusFilter = 'archived';
        }
    }

    private function syncEmptyMessage(): void
    {
        if ($this->statusFilter === 'active') {
            $this->setEmptyMessage('No active patients found');

            return;
        }

        if ($this->statusFilter === 'archived') {
            $this->setEmptyMessage('No archived patients found');

            return;
        }

        $this->setEmptyMessage('No patients found');
    }

    private function isPrescriptionModule(): bool
    {
        return ($this->module ?? request()->query('module')) === 'prescription';
    }
}
