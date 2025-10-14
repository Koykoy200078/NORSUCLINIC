<?php

namespace App\Livewire;

use App\Models\PatientQueue;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Rappasoft\LaravelLivewireTables\Views\Column;
use Livewire\Attributes\Lazy;

#[Lazy]
class DoctorPanelAppointmentTable extends LivewireTableComponent
{
    protected $model = PatientQueue::class;

    public bool $showFilterOnHeader = true;

    protected string $tableName = 'appointments';

    public bool $showButtonOnHeader = true;

    public array $FilterComponent = [
        'doctor_appointment.doctor_panel.components.filter',
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

    public string $buttonComponent = 'doctor_appointment.doctor_panel.components.add_button';

    public string $paymentTypeFilter = '';

    public string $paymentStatusFilter = '';

    public string $dateFilter = '';

    public int $statusFilter = PatientQueue::BOOKED;

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

    public function placeholder()
    {
        return view('livewire.appointment_skeleton');
    }

    public function builder(): Builder
    {
        $query = PatientQueue::with(['patient.user', 'services', 'transaction'])->where(
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
            Column::make(
                __('messages.appointment.patient'),
                'patient.user.first_name'
            )->view('doctor_appointment.doctor_panel.components.patient')
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
                __('messages.appointment.appointment_at'),
                'date'
            )->view('doctor_appointment.doctor_panel.components.appointment_at')
                ->sortable()->searchable(),
            Column::make(__('messages.appointment.status'), 'id')
                ->format(function ($value, $row) {
                    return view('doctor_appointment.doctor_panel.components.status')
                        ->with([
                            'row' => $row,
                            'book' => PatientQueue::BOOKED,
                            'accepted' => PatientQueue::ACCEPTED,
                            'finished' => PatientQueue::FINISHED,
                            'cancel' => PatientQueue::CANCELLED,
                        ]);
                }),
            Column::make(__('messages.common.action'), 'id')->view('doctor_appointment.doctor_panel.components.action'),
        ];
    }

    public function resetPagination()
    {
        $this->resetPage('appointmentsPage');
    }
}
