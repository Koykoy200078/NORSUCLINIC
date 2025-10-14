<?php

namespace App\Livewire;

use App\Models\PatientQueue;
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
      $todayDate = Carbon::now()->format('Y-m-d');
      $patientId = getLogInUser()->patient->id;
      $todayCompleted = PatientQueue::wherePatientId($patientId)->where(
         'date',
         '=',
         $todayDate
      )->whereStatus(PatientQueue::FINISHED)->count();
      $this->todayAppointmentCount = PatientQueue::wherePatientId($patientId)->where(
         'date',
         '=',
         $todayDate
      )->count();
      $this->upcomingAppointmentCount = PatientQueue::wherePatientId($patientId)->where(
         'date',
         '>',
         $todayDate
      )->whereNotIn('status', [PatientQueue::CANCELLED])->count();
      $this->pastCompletedAppointmentCount = PatientQueue::wherePatientId($patientId)->where(
         'date',
         '<',
         $todayDate
      )->count();
      $this->completedAppointmentCount = $this->pastCompletedAppointmentCount + $todayCompleted;
      $this->todayAppointment = PatientQueue::with(['patient.user', 'doctor.user', 'services'])
         ->wherePatientId($patientId)
         ->whereStatus(PatientQueue::BOOKED)
         ->where('date', '=', $todayDate)
         ->orderBy('created_at', 'DESC')
         ->get();

      $this->upcomingAppointment = PatientQueue::with(['patient.user', 'doctor.user', 'services'])
         ->wherePatientId($patientId)
         ->whereStatus(PatientQueue::BOOKED)
         ->where('date', '>', $todayDate)
         ->get();
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

