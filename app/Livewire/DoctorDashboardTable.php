<?php

namespace App\Livewire;

use App\Models\Medicine;
use App\Models\Patient;
use App\Models\Appointment;
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
      // Count total medicines
      $this->medicinesCount = Medicine::count();

      // Count total patients
      $this->patientsCount = Patient::count();

      // Count patient queues (today's pending/confirmed appointments)
      $this->patientQueuesCount = Appointment::whereDate('date', Carbon::today())
         ->whereIn('status', [0, 1]) // 0 = pending, 1 = confirmed
         ->count();

      // Get doctor's appointments counts
      $doctorId = getLogInUser()->doctor->id;
      $todayDate = Carbon::now()->format('Y-m-d');

      $this->totalAppointmentCount = Appointment::whereDoctorId($doctorId)
         ->whereNotIn('status', [Appointment::CANCELLED])
         ->count();

      $this->todayAppointmentCount = Appointment::whereDoctorId($doctorId)
         ->where('date', '=', $todayDate)
         ->whereNotIn('status', [Appointment::CANCELLED])
         ->count();

      $this->upcomingAppointmentCount = Appointment::whereDoctorId($doctorId)
         ->where('date', '>', $todayDate)
         ->whereStatus(Appointment::BOOKED)
         ->count();
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
