<?php

namespace App\Livewire;

use App\Models\PatientQueue;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Rappasoft\LaravelLivewireTables\Views\Column;
use Livewire\Attributes\Lazy;

#[Lazy]
class DoctorPanelQueueTable extends LivewireTableComponent
{
    protected $model = PatientQueue::class;

    public bool $showFilterOnHeader = true;

    protected string $tableName = 'patient_queues';

    public bool $showButtonOnHeader = true;

    public array $FilterComponent = [
        'doctor_queue.doctor_panel.components.filter',
        PatientQueue::PAYMENT_TYPE_ALL,
        PatientQueue::STATUS,
    ];

    protected $listeners = [
        'refresh' => '$refresh',
        'changeStatusFilter',
        'changePaymentTypeFilter',
        'changeDateFilter',
        'resetPage',
    ];

    public string $buttonComponent = 'doctor_queue.doctor_panel.components.add_button';

    public string $paymentTypeFilter = '';

    public string $paymentStatusFilter = '';

    public string $dateFilter = '';

    public int $statusFilter = PatientQueue::WAITING;

    public function configure(): void
    {
        $this->setPrimaryKey('id')
            ->setDefaultSort('priority', 'desc')  // Priority patients first
            ->setSecondarySort('queue_number', 'asc')  // Then by queue number
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

    public function placeholder()
    {
        return view('livewire.patient_queue_skeleton');
    }

    public function builder(): Builder
    {
        $query = PatientQueue::with([
            'patient.user',
            'services',
            'transaction',
            'admittedBy:id,first_name,last_name'
        ])->where(
            'doctor_id',
            getLoginUser()->doctor->id
        )->select('patient_queues.*');

        $query->when(
            $this->statusFilter != '' && $this->statusFilter != PatientQueue::ALL_STATUS,
            function (Builder $q) {
                if ($this->statusFilter != PatientQueue::ALL) {
                    $q->where('patient_queues.status', '=', $this->statusFilter);
                }
            }
        );

        $query->when(
            $this->paymentTypeFilter != '' && $this->paymentTypeFilter != PatientQueue::ALL_PAYMENT,
            function (Builder $q) {
                $q->where('patient_queues.payment_type', '=', $this->paymentTypeFilter);
            }
        );

        if ($this->dateFilter != '' && $this->dateFilter != getWeekDate()) {
            $timeEntryDate = explode(' - ', $this->dateFilter);
            $startDate = Carbon::parse($timeEntryDate[0])->format('Y-m-d');
            $endDate = Carbon::parse($timeEntryDate[1])->format('Y-m-d');
            $query->whereBetween('patient_queues.date', [$startDate, $endDate]);
        } else {
            $timeEntryDate = explode(' - ', getWeekDate());
            $startDate = Carbon::parse($timeEntryDate[0])->format('Y-m-d');
            $endDate = Carbon::parse($timeEntryDate[1])->format('Y-m-d');
            $query->whereBetween('patient_queues.date', [$startDate, $endDate]);
        }

        return $query;
    }

    public function changeStatusFilter($status)
    {
        $this->statusFilter = $status;
        $this->setBuilder($this->builder());
        $this->resetPagination();
    }

    public function changePaymentTypeFilter($type)
    {
        $this->paymentTypeFilter = $type;
        $this->setBuilder($this->builder());
        $this->resetPagination();
    }

    public function changeDateFilter($date)
    {
        $this->dateFilter = $date;
        $this->setBuilder($this->builder());
        $this->resetPagination();
    }

    public function columns(): array
    {
        return [
            Column::make(__('Queue #'), 'queue_number')
                ->sortable()
                ->searchable(),

            Column::make(__('Priority'), 'priority')
                ->view('doctor_queue.doctor_panel.components.priority_badge')
                ->sortable(),

            Column::make(__('Room'), 'room_number')
                ->sortable()
                ->searchable(),

            Column::make(
                __('messages.appointment.patient'),
                'patient.user.first_name'
            )->view('doctor_queue.doctor_panel.components.patient')
                ->sortable()
                ->searchable(
                    function (Builder $query, $direction) {
                        return $query->whereHas('patient.user', function (Builder $q) use ($direction) {
                            $q->whereRaw("TRIM(CONCAT(first_name, ?, last_name, ?)) LIKE ?", [' ', ' ', "%{$direction}%"]);
                        });
                    }
                ),

            Column::make(__('messages.patient.name'), 'patient.user.email')
                ->hideIf('patient.user.email')
                ->searchable(),

            Column::make(
                __('Date'),
                'date'
            )->view('doctor_queue.doctor_panel.components.queue_date')
                ->sortable()->searchable(),

            Column::make(__('messages.appointment.status'), 'id')
                ->format(function ($value, $row) {
                    return view('doctor_queue.doctor_panel.components.status')
                        ->with([
                            'row' => $row,
                            'waiting' => PatientQueue::WAITING,
                            'inProgress' => PatientQueue::IN_PROGRESS,
                            'completed' => PatientQueue::COMPLETED,
                            'cancelled' => PatientQueue::CANCELLED,
                        ]);
                }),

            Column::make(__('messages.common.action'), 'id')
                ->view('doctor_queue.doctor_panel.components.action'),
        ];
    }

    public function resetPagination()
    {
        $this->resetPage('patientQueuesPage');
    }
}
