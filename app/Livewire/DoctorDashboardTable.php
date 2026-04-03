<?php

namespace App\Livewire;

use App\Models\Medicine;
use App\Models\Patient;
use App\Models\PatientQueue;
use Carbon\Carbon;
use Livewire\Component;

class DoctorDashboardTable extends Component
{
   public $medicinesCount = 0;
   public $patientsCount = 0;
   public $patientQueuesCount = 0;

   public function mount()
   {
      $this->loadStatistics();
   }

   public function loadStatistics()
   {
      $this->medicinesCount     = Medicine::count();
      $this->patientsCount      = Patient::count();
      // Filter by today so the label "Patient Queues Today" is accurate
      $this->patientQueuesCount = PatientQueue::whereIn('status', ['waiting', 'in_progress'])
         ->whereDate('created_at', today())
         ->count();
   }

   public function render()
   {
      return view('livewire.doctor-dashboard-table');
   }
}
