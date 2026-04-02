<?php

namespace App\Livewire;

use App\Models\Generic;
use Rappasoft\LaravelLivewireTables\Views\Column;
use Livewire\Attributes\Lazy;

#[Lazy]
class MedicineGenericTable extends LivewireTableComponent
{
    protected $model = Generic::class;

    public bool $showButtonOnHeader = true;

    public string $buttonComponent = 'generics.add-button';

    protected $listeners = ['refresh' => '$refresh', 'changeFilter', 'resetPage'];

    public function configure(): void
    {
        $this->setPrimaryKey('id')
            ->setDefaultSort('generics.created_at', 'desc')
            ->setQueryStringStatus(false);

        // Hide add button for doctor users
        if (isRole('doctor')) {
            $this->showButtonOnHeader = false;
        }

        $this->setThAttributes(function (Column $column) {
            // For doctor users: Generic column takes 100% width
            if (isRole('doctor')) {
                if ($column->isField('name')) {
                    return [
                        'class' => 'w-100',
                        'style' => 'width: 100% !important',
                    ];
                }
            } else {
                // For non-doctor users: Generic 95%, Action 5%
                if ($column->isField('name')) {
                    return [
                        'class' => 'w-95',
                        'style' => 'width: 95% !important',
                    ];
                }

                if ($column->isField('id')) {
                    return [
                        'class' => 'text-center w-5',
                        'style' => 'width: 5% !important',
                    ];
                }
            }

            return [];
        });

        $this->setTdAttributes(function (Column $column, $row, $columnIndex, $rowIndex) {
            // Center align the action column cells
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
        return view('livewire.loading_skeleton');
    }

    public function columns(): array
    {
        $columns = [
            Column::make(__('messages.medicine.generic'), 'name')
                ->view('generics.templates.columns.name')
                ->searchable()
                ->sortable(),
        ];

        // Only show Action column for non-doctor users
        if (!isRole('doctor')) {
            $columns[] = Column::make(__('messages.common.action'), 'id')->view('generics.action');
        }

        return $columns;
    }
}
