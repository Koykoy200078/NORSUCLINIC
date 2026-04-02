<?php

namespace App\Livewire;

use Carbon\Carbon;
use Livewire\Component;
use Livewire\Attributes\Lazy;

#[Lazy]
class PatientDashboardSidebarTable extends Component
{
   public function placeholder()
   {
      return view('livewire.patient_dashboard_sidebar_skeleton');
   }
   public function render()
   {
      return view('livewire.patient-dashboard-sidebar-table');
   }
}
