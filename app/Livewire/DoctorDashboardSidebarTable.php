<?php

namespace App\Livewire;

use App\Models\PatientQueue;
use Carbon\Carbon;
use Livewire\Component;
use Livewire\Attributes\Lazy;

#[Lazy]
class DoctorDashboardSidebarTable extends Component
{
   public $totalAppointmentCount;
   public $todayAppointmentCount;
   public $upcomingAppointmentCount;

   public function mount()
   {
      $doctorId = getLogInUser()->doctor->id;
      $todayDate = Carbon::now()->format('Y-m-d');
      $this->totalAppointmentCount = PatientQueue::whereDoctorId($doctorId)->whereNotIn(
         'status',
         [PatientQueue::CANCELLED]
      )->count();
      $this->todayAppointmentCount = PatientQueue::whereDoctorId($doctorId)->where(
         'date',
         '=',
         $todayDate
      )->whereNotIn('status', [PatientQueue::CANCELLED])->count();
      $this->upcomingAppointmentCount = PatientQueue::whereDoctorId($doctorId)->where(
         'date',
         '>',
         $todayDate
      )->whereStatus(PatientQueue::BOOKED)->count();
   }

   public function placeholder()
   {
      return view('livewire.doctor_dashboard_sidebar_skeleton');
   }
   public function render()
   {
      return view('livewire.doctor-dashboard-sidebar-table');
   }
}

