<?php

namespace App\Livewire;

use App\Models\PatientQueue;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Rappasoft\LaravelLivewireTables\Views\Column;
use Livewire\Attributes\Lazy;

#[Lazy]
class DoctorQueueTable extends LivewireTableComponent
{
    public $doctorId;

    protected $model = PatientQueue::class;

    public bool $showFilterOnHeader = true;

    public array $FilterComponent = ['doctor_queue.components.filter', PatientQueue::STATUS];

    protected $listeners = ['refresh' => '$refresh', 'resetPage', 'changeDoctorStatusFilter', 'changeDateFilter'];

    public int $statusFilter = PatientQueue::WAITING;

    public string $dateFilter = '';

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
            'patient:id,user_id',
            'patient.user:id,first_name,last_name',
            'admittedBy:id,first_name,last_name'
        ])->where(
            'doctor_id',
            '=',
            $this->doctorId
        )->select('patient_queues.*');

        $query->when(
            $this->statusFilter != '' && $this->statusFilter != PatientQueue::ALL_STATUS,
            function (Builder $q) {
                if ($this->statusFilter != PatientQueue::ALL) {
                    $q->where('patient_queues.status', '=', $this->statusFilter);
                }
            }
        );

        if ($this->dateFilter != '' && $this->dateFilter != getWeekDate()) {
            $timeEntryDate = explode(' - ', $this->dateFilter);
            $startDate = Carbon::createFromFormat('d/m/Y', $timeEntryDate[0])->format('Y-m-d');
            $endDate = Carbon::createFromFormat('d/m/Y', $timeEntryDate[1])->format('Y-m-d');
            $query->whereBetween('patient_queues.date', [$startDate, $endDate]);
        } else {
            $timeEntryDate = explode(' - ', getWeekDate());
            $startDate = Carbon::parse($timeEntryDate[0])->format('Y-m-d');
            $endDate = Carbon::parse($timeEntryDate[1])->format('Y-m-d');
            $query->whereBetween('patient_queues.date', [$startDate, $endDate]);
        }

        return $query;
    }

    public function changeDoctorStatusFilter($status)
    {
        if ($status == null) {
            $status = 1;
        }
        $this->statusFilter = $status;
        $this->setBuilder($this->builder());
    }

    public function changeDateFilter($date)
    {
        $this->dateFilter = $date;
        $this->setBuilder($this->builder());
    }

    public function columns(): array
    {
        return [
            Column::make(__('Queue #'), 'queue_number')
                ->sortable()
                ->searchable(),

            Column::make(__('Priority'), 'priority')
                ->view('doctor_queue.components.priority_badge')
                ->sortable(),

            Column::make(__('Room'), 'room_number')
                ->sortable()
                ->searchable(),

            Column::make(
                __('messages.appointment.patient'),
                'patient.user.first_name'
            )->view('doctor_queue.components.patient')
                ->sortable()
                ->searchable(
                    function (Builder $query, $direction) {
                        return $query->whereHas('patient.user', function (Builder $q) use ($direction) {
                            $q->whereRaw("TRIM(CONCAT(first_name, ?, last_name, ?)) LIKE ?", [' ', ' ', "%{$direction}%"]);
                        });
                    }
                ),

            Column::make(
                __('Date'),
                'date'
            )->view('doctor_queue.components.queue_date')
                ->sortable()->searchable(),

            Column::make(__('Status'), 'status')
                ->view('doctor_queue.components.status_badge'),

            Column::make(__('messages.common.action'), 'id')
                ->view('doctor_queue.components.action'),
        ];
    }
}
