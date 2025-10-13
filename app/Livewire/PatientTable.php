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

    protected string $tableName = 'patients';

    public string $buttonComponent = 'patients.components.add_button';

    public bool $showFilterOnHeader = true;

    public array $FilterComponent = ['patients.components.filter', Patient::PATIENT_FILTER];

    protected $listeners = ['refresh' => '$refresh', 'resetPage', 'changeDateFilter', 'patientChangeDateFilter'];

    public string $dateFilter = '';

    /**
     * Configure the table settings.
     */
    public function configure(): void
    {
        $this->setPrimaryKey('id')
            ->setDefaultSort('created_at', 'desc')
            ->setQueryStringStatus(false);

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
        $query = Patient::with(['user:id,first_name,last_name,email,email_verified_at,year_level_id', 'appointments:id,patient_id'])
            ->withCount('appointments')
            ->withCount(['requestDocuments as request_documents_count' => function ($subQuery) {
                $subQuery->selectRaw('COUNT(*)')
                    ->whereColumn('request_documents.user_id', 'patients.user_id');
            }])
            ->withCount(['requestDocuments as consultation_form_count' => function ($subQuery) {
                $subQuery->selectRaw('COUNT(*)')
                    ->whereColumn('request_documents.user_id', 'patients.user_id')
                    ->where('request_documents.document_type', 'consultation_form');
            }]);

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
    public function placeholder()
    {
        return view('livewire.doctor_holiday_skeleton');
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

    /**
     * Define the columns for the table.
     */
    public function columns(): array
    {
        return [
            Column::make(__('messages.patient.name'), 'user.first_name')
                ->view('patients.components.name')
                ->sortable()
                ->searchable(function (Builder $query, $direction) {
                    $query->whereHas('user', function (Builder $q) use ($direction) {
                        $q->whereRaw("TRIM(CONCAT(first_name, ' ', last_name)) LIKE ?", ["%{$direction}%"]);
                    });
                }),
            Column::make(__('messages.patient.email'), 'user.email')
                ->searchable(),
            Column::make(__('messages.doctor_dashboard.total_appointments'), 'id')
                ->sortable()
                ->view('patients.components.total_appointments'),
            Column::make(__('Total Consultations'), 'id')
                ->view('patients.components.consultation_form_count'),
            Column::make(__('messages.patient.registered_on'), 'created_at')
                ->sortable()
                ->view('patients.components.registered_on'),
            Column::make(__('messages.common.action'), 'user.id')
                ->view('patients.components.action'),
        ];
    }

    /**
     * Reset the pagination for the table.
     */
    public function resetPagination()
    {
        $this->resetPage('patientsPage');
    }
}
