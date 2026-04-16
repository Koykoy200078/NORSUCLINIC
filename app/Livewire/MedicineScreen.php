<?php

namespace App\Livewire;

use Livewire\Component;

/**
 * Single-screen tabbed interface for all medicine management.
 *
 * Tab switching is handled 100% by Alpine.js (x-show + history.replaceState).
 * This component has NO Livewire properties — it just mounts the child
 * components once and never re-renders, which avoids destroying lazy
 * child components on every tab click.
 */
class MedicineScreen extends Component
{
    public function render()
    {
        return view('livewire.medicine-screen');
    }
}
