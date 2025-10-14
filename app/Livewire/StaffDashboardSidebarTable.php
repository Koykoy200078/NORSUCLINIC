<?php

namespace App\Livewire;

use App\Models\PatientQueue;
use Carbon\Carbon;
use Livewire\Component;
use Livewire\Attributes\Lazy;

#[Lazy]
class StaffDashboardSidebarTable extends Component
{
    public $upcomingAppointmentCount;
    public $totalAppointmentCount;

    public function mount()
    {
        $todayDate = Carbon::now()->format('Y-m-d');
        $this->upcomingAppointmentCount = PatientQueue::where('date', '>', $todayDate)->count();
        $this->totalAppointmentCount = PatientQueue::count();
    }

    public function placeholder()
    {
        return view('livewire.dashboard_sidebar_skeleton');
    }

    public function render()
    {
        return view('livewire.staff-dashboard-sidebar-table');
    }
}

