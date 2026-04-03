<?php

namespace App\Livewire;

use Rappasoft\LaravelLivewireTables\DataTableComponent;
use Rappasoft\LaravelLivewireTables\Exceptions\DataTableConfigurationException;
use Livewire\Attributes\Lazy;

#[Lazy]
/**
 * Class LivewireTableComponent
 */
class LivewireTableComponent extends DataTableComponent
{
    protected bool $columnSelectStatus = false;

    public bool $showFilterOnHeader = false;

    public bool $paginationIsEnabled = false;

    public bool $paginationStatus = true;

    public bool $sortingPillsStatus = false;

    public bool $reordering = false;

    protected $listeners = ['refresh' => '$refresh'];

    public string $emptyMessage = 'No data available in table';

    // for table header button
    public bool $showButtonOnHeader = false;

    public string $buttonComponent = '';

    public function configure(): void
    {
        // TODO: Implement configure() method.
    }

    public function columns(): array
    {
        // TODO: Implement columns() method.
    }

    public function refreshDataTable()
    {
        $this->dispatch('refresh');
    }

    public function placeholder()
    {
        return view('livewire.loading_skeleton');
    }

    public function updatedPerPage($value): void
    {
        if (! in_array($value, $this->getPerPageAccepted(), false)) {
            $value = $this->setPerPage($this->getPerPageAccepted()[0] ?? 10);
        }

        $this->resetComputedPage();
    }
}
