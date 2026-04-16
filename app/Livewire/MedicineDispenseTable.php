<?php

namespace App\Livewire;

use App\Models\DispenseRecord;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Rappasoft\LaravelLivewireTables\Views\Column;
use Livewire\Attributes\Lazy;

/**
 * Renamed from MedicineBillTable → MedicineDispenseTable.
 * Displays the dispensing history (Tab 4 of MedicineScreen).
 */
#[Lazy]
class MedicineDispenseTable extends LivewireTableComponent
{
    public bool $showButtonOnHeader = true;

    public string $buttonComponent = 'medicine-history.add-button';

    protected $listeners = ['refresh' => '$refresh', 'changeFilter', 'resetPage'];

    protected $model = DispenseRecord::class;

    public function configure(): void
    {
        $this->setPrimaryKey('id')
            ->setDefaultSort('medicine_bills.created_at', 'desc');

        if (isRole('doctor')) {
            $this->showButtonOnHeader = false;
        }

        $this->setThAttributes(function (Column $column) {
            if ($column->isField('id')) {
                return ['class' => 'text-center ml-5'];
            }

            return [];
        });
    }

    public function placeholder()
    {
        return view('livewire.loading_skeleton');
    }

    public function columns(): array
    {
        $columns = [
            Column::make(__('messages.medicine_bills.history_number'), 'history_number')
                ->sortable()->searchable()
                ->view('medicine-history.columns.bill_id'),
            Column::make(__('messages.medicine_bills.bill_date'), 'created_at')
                ->sortable()->searchable()
                ->view('medicine-history.columns.bill_date'),
            Column::make(__('messages.prescription.patient'), 'patient_id')->hideIf(1),
            Column::make(__('messages.prescription.patient'), 'patient.patientUser.first_name')
                ->sortable(function (Builder $query, $direction) {
                    return $query->orderBy(
                        User::select('first_name')->whereColumn('id', 'patient.user_id'),
                        $direction
                    );
                })->searchable()->view('medicine-history.columns.patient'),
            Column::make(__('messages.doctor.doctor'), 'doctor_id')->hideIf(1),
            Column::make(__('messages.doctor.doctor'), 'doctor.doctorUser.first_name')
                ->sortable(function (Builder $query, $direction) {
                    return $query->orderBy(
                        User::select('first_name')->whereColumn('id', 'doctor.user_id'),
                        $direction
                    );
                })->searchable()->view('medicine-history.columns.doctor'),
        ];

        if (! isRole('doctor')) {
            $columns[] = Column::make(__('messages.common.action'), 'id')
                ->view('medicine-history.columns.action');
        }

        return $columns;
    }

    public function builder(): Builder
    {
        return DispenseRecord::with([
            'patient:id,user_id',
            'patient.patientUser:id,first_name,last_name',
            'doctor:id,user_id',
            'doctor.doctorUser:id,first_name,last_name',
        ]);
    }
}
