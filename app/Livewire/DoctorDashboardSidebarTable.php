<?php

namespace App\Livewire;

use Carbon\Carbon;
use Livewire\Component;
use Livewire\Attributes\Lazy;

#[Lazy]
class DoctorDashboardSidebarTable extends Component
{

   public function placeholder()
   {
      return view('livewire.doctor_dashboard_sidebar_skeleton');
   }
   public function render()
   {
      return view('livewire.doctor-dashboard-sidebar-table');
   }
}
