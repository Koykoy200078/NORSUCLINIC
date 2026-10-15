<?php

namespace App\Livewire;

use App\Models\PatientQueue;
use App\Repositories\DashboardRepository;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class Dashboard extends Component
{
    public $totalDoctorCount;

    public $totalPatientCount;

    public $totalRegisteredPatientCount;

    public $todayQueueCount;

    public function mount(DashboardRepository $dashboard)
    {
        // Read fresh every time: a patient or doctor added a second ago is counted, and the figures are the ones of the
        // Patients and Doctors lists (see DashboardRepository).
        foreach ($dashboard->figures() as $figure => $value) {
            $this->{$figure} = $value;
        }

        $this->todayQueueCount = PatientQueue::whereIn('status', ['waiting', 'in_progress'])
            ->whereDate('created_at', today())
            ->count();
    }

    public function render(): View
    {
        return view('livewire.dashboard');
    }
}
