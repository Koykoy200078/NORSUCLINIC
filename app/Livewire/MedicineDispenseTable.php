<?php

namespace App\Livewire;

use App\Models\DispenseRecord;
use App\Models\DispenseRecordItem;
use App\Models\Prescription;
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
            ->setDefaultSort('medicine_bills.bill_date', 'desc');

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
            Column::make('Dispense ID', 'history_number')
                ->sortable()->searchable()
                ->view('medicine-history.columns.bill_id'),
            Column::make('Dispensed At', 'bill_date')
                ->sortable()->searchable()
                ->view('medicine-history.columns.bill_date'),
            Column::make(__('messages.prescription.patient'), 'patient_id')->hideIf(1),
            Column::make(__('messages.prescription.patient'), 'patient.user.first_name')
                ->sortable(function (Builder $query, $direction) {
                    return $query->orderBy(
                        User::select('first_name')->whereColumn('id', 'patient.user_id'),
                        $direction
                    );
                })->searchable()->view('medicine-history.columns.patient'),
            Column::make(__('messages.doctor.doctor'), 'doctor_id')->hideIf(1),
            Column::make(__('messages.doctor.doctor'), 'doctor.user.first_name')
                ->sortable(function (Builder $query, $direction) {
                    return $query->orderBy(
                        User::select('first_name')->whereColumn('id', 'doctor.user_id'),
                        $direction
                    );
                })->searchable()->view('medicine-history.columns.doctor'),
            Column::make('Quantity Dispensed', 'id')
                ->sortable(function (Builder $query, $direction) {
                    return $query->orderBy('dispensed_quantity', $direction);
                })
                ->view('medicine-history.columns.dispensed_quantity'),
            Column::make(__('messages.common.action'), 'id')
                ->view('medicine-history.columns.action'),
        ];

        return $columns;
    }

    public function builder(): Builder
    {
        return DispenseRecord::query()
            ->select('medicine_bills.*')
            ->selectSub(
                DispenseRecordItem::query()
                    ->selectRaw('COALESCE(SUM(sale_quantity), 0)')
                    ->whereColumn('sale_medicines.medicine_bill_id', 'medicine_bills.id'),
                'dispensed_quantity'
            )
            ->with([
                'patient:id,user_id',
                'patient.user:id,first_name,last_name,email,gender',
                'doctor:id,user_id',
                'doctor.user:id,first_name,last_name,email,gender',
            ])
            ->where(function (Builder $query) {
                $query->whereIn('medicine_bills.model_type', [
                    DispenseRecord::class,
                    'App\Models\MedicineBill',
                ])->orWhere(function (Builder $prescriptionQuery) {
                    $prescriptionQuery->where('medicine_bills.model_type', Prescription::class)
                        ->whereExists(function ($exists) {
                            $exists->selectRaw('1')
                                ->from('prescriptions')
                                ->whereColumn('prescriptions.id', 'medicine_bills.model_id')
                                ->where('prescriptions.status', Prescription::DISPENSE_STATUS_DISPENSED);
                        });
                });
            });
    }
}
