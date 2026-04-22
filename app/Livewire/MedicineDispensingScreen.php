<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\Attributes\Url;

/**
 * Tabbed screen for medicine dispensing workflows.
 */
class MedicineDispensingScreen extends Component
{
    #[Url(as: 'tab', history: true)]
    public string $tab = 'verify-prescriptions';

    private const VALID_TABS = [
        'verify-prescriptions',
        'dispense-history',
        'stock-out',
    ];

    public function mount(): void
    {
        if (! in_array($this->tab, self::VALID_TABS, true)) {
            $this->tab = 'verify-prescriptions';
        }
    }

    public function setTab(string $tab): void
    {
        if (! in_array($tab, self::VALID_TABS, true)) {
            return;
        }

        $this->tab = $tab;
    }

    public function render()
    {
        return view('livewire.medicine-dispensing-screen');
    }
}
