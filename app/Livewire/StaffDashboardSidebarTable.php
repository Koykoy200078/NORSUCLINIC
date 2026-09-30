<?php

namespace App\Livewire;

use Carbon\Carbon;
use Livewire\Component;
use Livewire\Attributes\Lazy;

#[Lazy]
class StaffDashboardSidebarTable extends Component
{

    public function placeholder(): string
    {
        return view('livewire.dashboard_sidebar_skeleton')->render();
    }

    public function render()
    {
        return view('livewire.staff-dashboard-sidebar-table');
    }
}
