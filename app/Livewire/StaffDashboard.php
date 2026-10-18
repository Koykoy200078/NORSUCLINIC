<?php

namespace App\Livewire;

use App\Repositories\DashboardRepository;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class StaffDashboard extends Component
{
    /** Redraws on the real-time notice of a module this page shows (resources/assets/js/realtime), and every minute while it is down. */
    protected $listeners = ['refresh' => '$refresh'];

    public $totalDoctorCount;

    public $totalPatientCount;

    public $totalRegisteredPatientCount;

    public function mount(DashboardRepository $dashboard)
    {
        // Read fresh every time: a patient or doctor added a second ago is counted, and the figures are the ones of the
        // Patients and Doctors lists (see DashboardRepository).
        foreach ($dashboard->figures() as $figure => $value) {
            $this->{$figure} = $value;
        }
    }

    public function render(): View
    {
        return view('livewire.staff-dashboard');
    }
}
