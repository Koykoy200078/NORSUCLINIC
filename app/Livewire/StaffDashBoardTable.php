<?php

namespace App\Livewire;

use App\Repositories\DashboardRepository;
use Livewire\Attributes\Lazy;
use Livewire\Component;

#[Lazy]
class StaffDashBoardTable extends Component
{
    /** @var array{patients: array<int, array<string, mixed>>, total: int} */
    public $data = ['patients' => [], 'total' => 0];

    public function mount(DashboardRepository $dashboard)
    {
        // Today's registrations, read fresh (a patient added a second ago is in the list). The Week and Month tabs fetch
        // theirs on click.
        $today = $dashboard->recentPatients('day', 'staff');

        $this->data = ['patients' => $today['data'], 'total' => $today['total']];
    }

    public function placeholder(): string
    {
        return view('livewire.dashboard_listing_table_skeleton')->render();
    }

    public function render()
    {
        return view('livewire.staff-dash-board-table');
    }
}
