<?php

namespace App\Livewire;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Rappasoft\LaravelLivewireTables\Views\Column;
use Livewire\Attributes\Lazy;

#[Lazy]
class RoleTable extends LivewireTableComponent
{
    protected $model = Role::class;

    public bool $showButtonOnHeader = true;

    public string $buttonComponent = 'roles.components.add_button';

    protected $listeners = ['refresh' => '$refresh', 'resetPage'];

    public function configure(): void
    {
        $this->setPrimaryKey('id')
            ->setDefaultSort('created_at', 'desc')
            ->setQueryStringStatus(false)
            ->setTableAttributes([
                'class' => 'table table-striped table-row-bordered gy-5 gs-7 align-middle',
            ]);
    }

    public function builder(): Builder
    {
        return Role::with('permissions')
            ->whereNotNull('display_name')
            ->where('display_name', '!=', '')
            ->select('roles.*');
    }

    public function placeholder(): string
    {
         return view('livewire.staff_skeleton')->render();
    }

    public function columns(): array
    {
        return [
            Column::make(__('messages.common.name'), 'display_name')->view('roles.components.role')
                ->sortable()
                ->searchable(),
            Column::make(__('messages.role.permissions'), 'created_at')->view('roles.components.permission'),
            Column::make(__('messages.common.action'), 'id')->view('roles.components.action'),
        ];
    }
}
