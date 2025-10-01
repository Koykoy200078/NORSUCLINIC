<?php

namespace App\Livewire;

use App\Models\User;
use App\Models\MedicineBill;
use Illuminate\Database\Eloquent\Builder;
use Rappasoft\LaravelLivewireTables\Views\Column;
use Livewire\Attributes\Lazy;

#[Lazy]
class MedicineBillTable extends LivewireTableComponent
{
    public bool $showButtonOnHeader = true;

    public string $buttonComponent = 'medicine-history.add-button';

    protected $listeners = ['refresh' => '$refresh', 'changeFilter', 'resetPage'];

    protected $model = MedicineBill::class;

    public function configure(): void
    {
        $this->setPrimaryKey('id')
            ->setDefaultSort('medicine_bills.created_at', 'desc');

        // Hide add button for doctor users
        if (isRole('doctor')) {
            $this->showButtonOnHeader = false;
        }

        $this->setThAttributes(function (Column $column) {
            if ($column->isField('id')) {
                return [
                    'class' => 'text-center ml-5',
                ];
            }
            if ($column->isField('net_amount')) {
                return [
                    'class' => 'text-end',
                ];
            }
            if ($column->isField('payment_status')) {
                return [
                    'class' => 'text-center',
                ];
            }

            return [];
        });
    }

    public function placeholder()
    {
        return view('livewire.smart_patient_cards_skeleton');
    }

    public function columns(): array
    {
        $columns = [
            Column::make(__('messages.medicine_bills.history_number'), 'history_number')
                ->sortable()
                ->searchable()
                ->view('medicine-history.columns.bill_id'),
            Column::make(__('messages.appointment.date'), 'created_at')
                ->sortable()
                ->searchable()
                ->view('medicine-history.columns.bill_date'),
            Column::make(__('messages.visit.patient'), 'patient_id')->hideIf(1),
            Column::make(__('messages.visit.patient'), 'patient.patientUser.first_name')
                ->sortable(function (Builder $query, $direction) {
                    return $query->orderBy(User::select('first_name')->whereColumn('id', 'patient.user_id'), $direction);
                })->searchable()->view('medicine-history.columns.patient'),
            Column::make(__('messages.doctor.doctor'), 'doctor_id')->hideIf(1),
            Column::make(__('messages.doctor.doctor'), 'doctor.doctorUser.first_name')
                ->sortable(function (Builder $query, $direction) {
                    return $query->orderBy(User::select('first_name')->whereColumn('id', 'doctor.user_id'), $direction);
                })->searchable()->view('medicine-history.columns.doctor'),
            // Column::make(__('messages.purchase_medicine.discount'), 'discount')
            //     ->sortable()
            //     ->searchable()
            //     ->view('medicine-history.columns.discount'),
            // Column::make(__('messages.purchase_medicine.net_amount'), 'net_amount')
            //     ->sortable()
            //     ->searchable()
            //     ->view('medicine-history.columns.amount'),
            // Column::make(__('messages.medicine_bills.payment_status'), 'payment_status')
            //     ->sortable()->view('medicine-history.columns.payment_status'),
        ];

        // Only show Action column for non-doctor users
        if (!isRole('doctor')) {
            $columns[] = Column::make(__('messages.common.action'), 'id')
                ->view('medicine-history.columns.action');
        }

        return $columns;
    }

    function builder(): Builder
    {
        /** @var MedicineBill $query */
        return MedicineBill::with(['patient', 'doctor.user', 'doctor.doctorUser', 'patient.user', 'patient.patientUser']);
    }
}
