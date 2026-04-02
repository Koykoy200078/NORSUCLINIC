<?php

namespace App\Livewire;

use Carbon\Carbon;
use Livewire\Component;
use Livewire\Attributes\Lazy;

#[Lazy]
class AdminDashboardSidebarTable extends Component
{
   public $upcomingAppointmentCount;
   public $totalAppointmentCount;

   public function mount()
   {
      $this->upcomingAppointmentCount = 0;
      $this->totalAppointmentCount = 0;
   }

   public function placeholder()
   {
      return view('livewire.dashboard_sidebar_skeleton');
   }
   public function render()
   {
      return view('livewire.admin-dashboard-sidebar-table');
   }
}
