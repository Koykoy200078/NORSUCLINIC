<?php

namespace App\Livewire;

use App\Models\Medicine;
use Illuminate\Database\Eloquent\Builder;
use Rappasoft\LaravelLivewireTables\Views\Column;

class MedicineTable extends LivewireTableComponent
{
    protected $model = Medicine::class;

    public bool $showButtonOnHeader = true;

    public string $buttonComponent = 'medicines.add-button';

    protected $listeners = ['refresh' => '$refresh', 'resetPage'];

    public function configure(): void
    {
        $this->setPrimaryKey('id')
            ->setDefaultSort('medicines.created_at', 'desc')
            ->setQueryStringStatus(false);

        // Hide add button for doctor users
        if (isRole('doctor')) {
            $this->showButtonOnHeader = false;
        }

        $this->setTdAttributes(function (Column $column, $row, $columnIndex, $rowIndex) {
            if ($column->isField('name')) {
                return [
                    'class' => 'pt-5',
                ];
            }

            return [];
        });
        $this->setThAttributes(function (Column $column) {
            return [];
        });
    }

    public function placeholder()
    {
        return view('livewire.staff_skeleton');
    }

    public function columns(): array
    {
        $columns = [
            Column::make(__('messages.medicine.medicine'), 'name')
                ->view('medicines.templates.columns.name')
                ->searchable()
                ->sortable(),
            Column::make('Generic Name', 'generic_name')
                ->searchable()
                ->sortable(),
            Column::make('Category', 'category_name')
                ->searchable()
                ->sortable(),
            Column::make(__('messages.medicine.available_quantity'), 'available_quantity')
                ->view('medicines.templates.columns.available_quantity')
                ->searchable()
                ->sortable(),
            Column::make('Expiration', 'id')
                ->view('medicines.templates.columns.expiration')
        ];

        // Only show Action column for non-doctor users
        if (!isRole('doctor')) {
            $columns[] = Column::make(__('messages.common.action'), 'id')->view('medicines.action');
        }

        return $columns;
    }

    public function builder(): Builder
    {
        return Medicine::query()
            ->select([
                'medicines.*'
            ]);
    }
}
