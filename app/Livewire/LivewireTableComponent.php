<?php

namespace App\Livewire;

use App\Livewire\Concerns\SearchesByWords;
use Rappasoft\LaravelLivewireTables\DataTableComponent;
use Rappasoft\LaravelLivewireTables\Exceptions\DataTableConfigurationException;
use Livewire\Attributes\Lazy;

#[Lazy]
/**
 * Class LivewireTableComponent
 */
class LivewireTableComponent extends DataTableComponent
{
    use SearchesByWords;

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

    /**
     * Runs before configure() on every request — sets performance defaults
     * that all child tables inherit without needing parent::configure() calls.
     */
    public function configuring(): void
    {
        $this->setSearchDebounce(500)
            ->setColumnSelectStatus(false)
            ->setPerPageAccepted([10, 25, 50])
            ->setPerPage(10);
    }

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

    public function placeholder(): string
    {
        return view('livewire.loading_skeleton')->render();
    }

    public function updatedPerPage($value): void
    {
        if (! in_array($value, $this->getPerPageAccepted(), false)) {
            $value = $this->setPerPage($this->getPerPageAccepted()[0] ?? 10);
        }

        $this->resetComputedPage();
    }
}
