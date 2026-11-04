<?php

namespace App\Livewire\Tables;

use Livewire\Attributes\On;

/**
 * A list that goes back to its first page when the page's scripts dispatch "resetPage" (after a search box or a filter
 * changed, or a row was added). The event carries no page name, so it resets the default paginator, as it always did.
 *
 * Not every list listens: the consultation and lab-request lists keep the page the person is on.
 */
trait ResetsPageOnEvent
{
    #[On('resetPage')]
    public function resetPageOnEvent(): void
    {
        $this->resetPage();
    }
}
