<?php

namespace App\Livewire;

use App\Livewire\Tables\ResetsPageOnEvent;
use App\Livewire\Tables\TableComponent;
use App\Models\User;
use App\Support\SearchTerm;
use Illuminate\Database\Eloquent\Builder;
use App\Support\Tables\Column;
use Livewire\Attributes\Lazy;

#[Lazy]
class StaffTable extends TableComponent
{
    protected $model = User::class;

    public bool $showButtonOnHeader = true;

    public string $buttonComponent = 'staffs.components.add_button';

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

        $this->setTdAttributes(function (Column $column, $row, $columnIndex, $rowIndex) {
            if ($column->isField('id')) {
                return [
                    'class' => 'text-center',
                ];
            }

            return [];
        });
    }

    public function columns(): array
    {
        return [
            Column::make(__('messages.user.full_name'), 'first_name')->view('staffs.components.staff_name')
                ->sortable()->searchable(
                    function (Builder $query, $word) {
                        SearchTerm::wordInColumns($query, $word, SearchTerm::PERSON_COLUMNS);
                    }
                ),
            Column::make(__('messages.patient.name'), 'email')
                ->hideIf('email')
                ->searchable(),
            Column::make(__('messages.common.email'), 'email')->hideIf(1),
            Column::make(__('messages.staff.role'), 'email')->view('staffs.components.role'),
            // Column::make(
            //     __('messages.common.email_verified'),
            //     'email_verified_at'
            // )->view('staffs.components.email_verified')->sortable(),
            Column::make(__('messages.common.action'), 'id')->view('staffs.components.action'),
        ];
    }

    public function placeholder(): string
    {
        return view('livewire.staff_skeleton')->render();
    }

    public function builder(): Builder
    {
        // `media` and `gender` feed the profile picture of every row: loaded once for the page, not once per row.
        return User::with(['roles', 'media'])
            ->where('type', User::STAFF)
            ->where('id', '!=', getLogInUserId())
            ->select(['id', 'first_name', 'last_name', 'email', 'email_verified_at', 'type', 'gender']);
    }
}
