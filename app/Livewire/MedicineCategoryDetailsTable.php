<?php

namespace App\Livewire;

use App\Livewire\Tables\TableComponent;
use App\Models\Medicine;
use Illuminate\Database\Eloquent\Builder;
use App\Support\Tables\Column;

class MedicineCategoryDetailsTable extends TableComponent
{
    protected $model = Medicine::class;

    protected $listeners = ['refresh' => '$refresh', 'changeFilter', 'resetPage'];

    public $categoryDetails;

    public function mount(string $categoryDetails): void
    {
        $this->categoryDetails = $categoryDetails;
    }

    public function configure(): void
    {
        $this->setPrimaryKey('id');
        $this->setThAttributes(function (Column $column) {
            return [];
        });
    }

    public function columns(): array
    {
        return [
            Column::make(__('messages.medicine.medicine'), 'name')
                ->sortable()
                ->searchable(),
            Column::make(__('messages.medicine.generic'), 'generic.name')
                ->view('categories.templates.columnsDetails.brand')
                ->searchable()
                ->sortable(),

            Column::make(__('messages.medicine.description'), 'description')
                ->searchable()
                ->sortable()
                ->view('categories.templates.columnsDetails.description'),
        ];
    }

    public function builder(): Builder
    {
        return Medicine::query()
            ->with('medicineCategory', 'generic')
            ->where('category_id', $this->categoryDetails);
    }
}
