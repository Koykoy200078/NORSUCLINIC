<?php

namespace App\Livewire;

use App\Models\PatientQueue;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Cache;
use Livewire\Component;

class Dashboard extends Component
{

    public $totalDoctorCount;
    public $totalPatientCount;
    public $totalRegisteredPatientCount;
    public $todayQueueCount;

    public function mount()
    {
        $todayDate = now()->format('Y-m-d');
        $stats = Cache::remember('livewire_admin_dashboard_' . $todayDate, 300, function () use ($todayDate) {
            return [
                'totalDoctorCount'           => User::toBase()->whereType(User::DOCTOR)->where('status', User::ACTIVE)->count(),
                'totalPatientCount'          => User::toBase()->whereType(User::PATIENT)->count(),
                'totalRegisteredPatientCount' => User::toBase()->whereType(User::PATIENT)->whereDate('created_at', $todayDate)->count(),
            ];
        });
        $this->totalDoctorCount            = $stats['totalDoctorCount'];
        $this->totalPatientCount           = $stats['totalPatientCount'];
        $this->totalRegisteredPatientCount = $stats['totalRegisteredPatientCount'];
        $this->todayQueueCount = Cache::remember('menu_badge_queue_count', 30, function () {
            return PatientQueue::whereIn('status', ['waiting', 'in_progress'])->count();
        });
    }

    public function render(): View
    {
        return view('livewire.dashboard');
    }
}
