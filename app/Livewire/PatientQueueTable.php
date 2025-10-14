<?php

namespace App\Livewire;

use Carbon\Carbon;
use App\Models\User;
use App\Models\Patient;
use App\Models\PatientQueue;
use Illuminate\Database\Eloquent\Builder;
use Rappasoft\LaravelLivewireTables\Views\Column;
use Livewire\Attributes\Lazy;

#[Lazy]
class PatientQueueTable extends LivewireTableComponent
{
    protected $model = PatientQueue::class;

    public bool $showButtonOnHeader = true;

    protected string $tableName = 'patient_queues';

    public string $buttonComponent = 'patient_queues.components.add_button';

    public bool $showFilterOnHeader = true;

    public array $FilterComponent = ['patient_queues.components.filter', PatientQueue::PAYMENT_TYPE_ALL, PatientQueue::STATUS];

    protected $listeners = [
        'refresh' => '$refresh',
        'resetPage',
        'changeStatusFilter',
        'changePaymentTypeFilter',
        'changeDateFilter',
        'changePaymentStatusFilter',
    ];

    public string $paymentTypeFilter = '';

    public string $paymentStatusFilter = '';

    public string $dateFilter = '';

    public $statusFilter = PatientQueue::WAITING;

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
        // Optimized eager loading with selective columns
        $query = PatientQueue::with([
            'doctor.user:id,first_name,last_name,email,status',
            'patient.user:id,first_name,last_name,email',
            'services:id,name,charges',
            'transaction:id,appointment_unique_id,amount,status',
            'doctor.reviews:id,doctor_id,rating',
            'doctor.user.media',
            'admittedBy:id,first_name,last_name'
        ]);

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
                $q->where('payment_type', '=', $this->paymentTypeFilter);
            }
        );

        $query->when(
            $this->paymentStatusFilter != '',
            function (Builder $q) {
                if ($this->paymentStatusFilter != PatientQueue::ALL_PAYMENT) {
                    if ($this->paymentStatusFilter == PatientQueue::PENDING) {
                        $q->has('transaction', '=', null);
                    } elseif ($this->paymentStatusFilter == PatientQueue::PAID) {
                        $q->has('transaction', '!=', null);
                    }
                }
            }
        );

        // Only filter by date when a date range/value is explicitly provided.
        if (!empty($this->dateFilter)) {
            try {
                if (str_contains($this->dateFilter, ' - ')) {
                    $parts = explode(' - ', $this->dateFilter);
                    $startDate = Carbon::createFromFormat('d/m/Y', trim($parts[0]))->format('Y-m-d');
                    $endDate = Carbon::createFromFormat('d/m/Y', trim($parts[1]))->format('Y-m-d');
                    $query->whereBetween('date', [$startDate, $endDate]);
                } else {
                    $singleDate = Carbon::createFromFormat('d/m/Y', trim($this->dateFilter))->format('Y-m-d');
                    $query->whereDate('date', $singleDate);
                }
            } catch (\Exception $e) {
                // Invalid format — skip date filtering
            }
        }

        if (getLoginUser()->hasRole('patient')) {
            $query->where('patient_id', getLoginUser()->patient->id);
        }

        return $query->select('patient_queues.*');
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

    public function changePaymentStatusFilter($type)
    {
        $this->paymentStatusFilter = $type;
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
                ->view('patient_queues.components.priority_badge')
                ->sortable(),

            Column::make(__('Room'), 'room_number')
                ->sortable()
                ->searchable(),

            Column::make(__('messages.visit.doctor'), 'doctor.doctorUser.first_name')
                ->view('patient_queues.components.doctor_name')
                ->sortable()
                ->searchable(
                    function (Builder $query, $direction) {
                        return $query->whereHas('doctor.doctorUser', function (Builder $q) use ($direction) {
                            $q->whereRaw("TRIM(CONCAT(first_name, ?, last_name, ?)) LIKE ?", [' ', ' ', "%{$direction}%"]);
                        });
                    }
                ),

            Column::make(__('messages.appointment.patient'), 'patient.patientUser.first_name')
                ->view('patient_queues.components.patient_name')
                ->sortable(function (Builder $query, $direction) {
                    return $query->orderBy(User::select('first_name')->whereColumn('id', 'patient.user_id'), $direction);
                })
                ->searchable(),

            Column::make(__('messages.appointment.patient'), 'patient.patientUser.last_name')
                ->hideIf('patient.patientUser.last_name')
                ->searchable(),

            Column::make(__('messages.appointment.doctor'), 'doctor.doctorUser.email')
                ->hideIf('doctor.doctorUser.email')
                ->searchable(),

            Column::make(__('messages.appointment.patient'), 'patient.patientUser.email')
                ->hideIf('patient.patientUser.email')
                ->searchable(),

            Column::make(
                __('Date'),
                'date'
            )->view('patient_queues.components.queue_date')
                ->sortable()->searchable(),

            Column::make(__('Admitted By'), 'admittedBy.first_name')
                ->view('patient_queues.components.admitted_by'),

            Column::make(__('messages.common.action'), 'id')
                ->view('patient_queues.components.action'),
        ];
    }

    public function resetPagination()
    {
        $this->resetPage('patientQueuesPage');
    }
}
