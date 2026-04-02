<?php

namespace App\Livewire;

use Carbon\Carbon;
use Livewire\Component;
use Livewire\Attributes\Lazy;

#[Lazy]
class PatientDashboardSidebarTable extends Component
{

   public $todayAppointmentCount;
   public $upcomingAppointmentCount;
   public $pastCompletedAppointmentCount;
   public $completedAppointmentCount;
   public $todayAppointment;
   public $upcomingAppointment;

   public function mount()
   {
      $this->todayAppointmentCount = 0;
      $this->upcomingAppointmentCount = 0;
      $this->pastCompletedAppointmentCount = 0;
      $this->completedAppointmentCount = 0;
      $this->todayAppointment = collect();
      $this->upcomingAppointment = collect();
   }
   public function placeholder()
   {
      return view('livewire.patient_dashboard_sidebar_skeleton');
   }
   public function render()
   {
      return view('livewire.patient-dashboard-sidebar-table');
   }
}
