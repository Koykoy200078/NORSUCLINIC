<?php

namespace App\Livewire;

use App\Models\Medicine;
use App\Models\Patient;
use App\Models\PatientQueue;
use Carbon\Carbon;
use Livewire\Component;
use Livewire\Attributes\Lazy;

#[Lazy]
class DoctorDashboardTable extends Component
{
   public $medicinesCount = 0;
   public $patientsCount = 0;
   public $patientQueuesCount = 0;
   public $totalAppointmentCount = 0;
   public $todayAppointmentCount = 0;
   public $upcomingAppointmentCount = 0;

   public function mount()
   {
      $this->loadStatistics();
   }

   public function loadStatistics()
   {
      $this->medicinesCount = Medicine::count();
      $this->patientsCount = Patient::count();
      $this->patientQueuesCount = PatientQueue::whereIn('status', ['waiting', 'in_progress'])->count();
      $this->totalAppointmentCount = 0;
      $this->todayAppointmentCount = 0;
      $this->upcomingAppointmentCount = 0;
   }

   public function placeholder()
   {
      return view('livewire.doctor_dashboard_skeleton');
   }

   public function render()
   {
      return view('livewire.doctor-dashboard-table');
   }
}
