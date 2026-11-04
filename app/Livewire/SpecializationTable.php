<?php

namespace App\Livewire;

use App\Livewire\Tables\ResetsPageOnEvent;
use App\Livewire\Tables\TableComponent;
use App\Models\Specialization;
use App\Support\Tables\Column;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\Lazy;

#[Lazy]
class SpecializationTable extends TableComponent
{
    protected $model = Specialization::class;

    public bool $showButtonOnHeader = true;

    public string $buttonComponent = 'specializations.components.add_button';

    use ResetsPageOnEvent;

    public function configure(): void
    {
        $this->setPrimaryKey('id')
            ->setDefaultSort('created_at', 'desc');

        $this->setThAttributes(function (Column $column) {
            if ($column->isField('id')) {
                return [
                    'class' => 'text-center',
                ];
            }

            return [];
        });
    }

    public function placeholder(): string
    {
        return view('livewire.loading_skeleton')->render();
    }

    public function columns(): array
    {
        return [
            Column::make(__('messages.common.name'), 'name')->view('specializations.components.name')
                ->sortable()
                ->searchable(),
            Column::make(__('messages.common.action'), 'id')->view('specializations.components.action'),
        ];
    }

    public function builder(): Builder
    {
        return Specialization::query();
    }
}
