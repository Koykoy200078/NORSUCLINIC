<?php

namespace App\Livewire;

use App\Repositories\DashboardRepository;
use Livewire\Component;

/**
 * The dashboard's "Recent patients registration" list. The admin and staff dashboards show the same list, each with its own
 * panel's links (re-audit 2026-11-13, DRY: the component and its view used to be two copies).
 */
abstract class RecentPatientsTable extends Component
{
    /** @var array{patients: array<int, array<string, mixed>>, total: int} */
    public $data = ['patients' => [], 'total' => 0];

    /** The panel whose links the rows use: "admin" or "staff". */
    abstract protected function panel(): string;

    public function mount(DashboardRepository $dashboard)
    {
        // Today's registrations, read fresh (a patient added a second ago is in the list). The Week and Month tabs fetch
        // theirs on click.
        $today = $dashboard->recentPatients('day', $this->panel());

        $this->data = ['patients' => $today['data'], 'total' => $today['total']];
    }

    public function placeholder(): string
    {
        return view('livewire.dashboard_listing_table_skeleton')->render();
    }

    public function render()
    {
        return view('livewire.recent-patients-table');
    }
}
