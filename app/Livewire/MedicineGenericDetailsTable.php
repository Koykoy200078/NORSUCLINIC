<?php

namespace App\Livewire;

use App\Livewire\Tables\ResetsPageOnEvent;
use App\Livewire\Tables\TableComponent;
use App\Models\Medicine;
use Illuminate\Database\Eloquent\Builder;
use App\Support\Tables\Column;
use Livewire\Attributes\Lazy;

#[Lazy]
class MedicineGenericDetailsTable extends TableComponent
{
    protected $model = Medicine::class;

    public $genericDetails;

    use ResetsPageOnEvent;

    public function mount(string $genericDetails): void
    {
        $this->genericDetails = $genericDetails;
    }

    public function configure(): void
    {
        $this->setPrimaryKey('id');
        $this->setThAttributes(function (Column $column) {
            return [];
        });
    }

    public function placeholder(): string
    {
        return view('livewire.staff_skeleton')->render();
    }

    public function columns(): array
    {
        return [
            Column::make(__('messages.medicine.category'), 'medicineCategory.name')
                ->view('generics.templates.columnsDetails.category')
                ->searchable()
                ->sortable(),
            Column::make(__('messages.medicine.medicine'), 'name')
                ->searchable()
                ->sortable(),
            Column::make(__('messages.medicine.generic'), 'category_id')
                ->hideIf('category_id')
        ];
    }

    public function builder(): Builder
    {
        /** @var Medicine $query */
        $query = Medicine::with('medicineCategory', 'generic')->where('generic_id', $this->genericDetails);

        return $query;
    }
}
