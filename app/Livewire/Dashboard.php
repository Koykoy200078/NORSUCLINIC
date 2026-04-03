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
        $stats = Cache::remember('livewire_admin_dashboard_' . $todayDate, 60, function () use ($todayDate) {
            return [
                'totalDoctorCount'  => User::toBase()->whereType(User::DOCTOR)->where('status', User::ACTIVE)->count(),
                'totalPatientCount' => User::toBase()->whereType(User::PATIENT)->count(),
            ];
        });
        $this->totalDoctorCount            = $stats['totalDoctorCount'];
        $this->totalPatientCount           = $stats['totalPatientCount'];
        // Always query fresh — never cache today's registered count so new patients reflect immediately
        $this->totalRegisteredPatientCount = User::toBase()->whereType(User::PATIENT)->whereDate('created_at', $todayDate)->count();
        $this->todayQueueCount = PatientQueue::whereIn('status', ['waiting', 'in_progress'])
            ->whereDate('created_at', $todayDate)
            ->count();
    }

    public function render(): View
    {
        return view('livewire.dashboard');
    }
}
