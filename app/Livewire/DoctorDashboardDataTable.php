<?php

namespace App\Livewire;

use App\Models\PatientQueue;
use App\Models\Patient;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Livewire\Component;
use Livewire\Attributes\Lazy;

#[Lazy]
class DoctorDashboardDataTable extends Component
{
    public $appointments;
    public function mount(Request $request)
    {
        $doctorId = getLogInUser()->doctor->id;
        $this->appointments['records'] = PatientQueue::with(['patient.user'])
            ->where('doctor_id', $doctorId)
            ->whereStatus(PatientQueue::BOOKED)
            ->whereDate('date', Carbon::today())
            ->orderBy('date', 'ASC')
            ->get()->toArray();
    }

    public function placeholder()
    {
        return view('livewire.dashboard_listing_table_skeleton');
    }
    public function render()
    {
        return view('livewire.doctor-dashboard-data-table');
    }
}

