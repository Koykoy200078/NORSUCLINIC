<?php

namespace App\Livewire;

use App\Models\PatientQueue;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Livewire\Component;
use Livewire\Attributes\Lazy;

#[Lazy]
class StaffDashboard extends Component
{
    public $totalDoctorCount;
    public $totalPatientCount;
    public $todayAppointmentCount;
    public $totalRegisteredPatientCount;

    public function mount()
    {
        // Staff can see the same metrics as admin but with appropriate access control
        $this->totalDoctorCount = User::toBase()->whereType(User::DOCTOR)->where('status', User::ACTIVE)->count();
        $this->totalPatientCount = User::toBase()->whereType(User::PATIENT)->count();
        $this->todayAppointmentCount = PatientQueue::toBase()->where('date', Carbon::now()->format('Y-m-d'))->whereStatus(PatientQueue::BOOKED)->count();
        $this->totalRegisteredPatientCount = User::toBase()->whereType(User::PATIENT)->whereRaw('Date(created_at) = CURDATE()')->count();
    }

    public function placeholder()
    {
        return view('livewire.dashboard_skeleton');
    }

    public function render(): View
    {
        return view('livewire.staff-dashboard');
    }
}

