<?php

namespace App\Livewire;

use App\Models\Patient;
use Livewire\Component;
use Livewire\Attributes\Lazy;

#[Lazy]
class StaffDashBoardTable extends Component
{
    public $data;

    public function mount()
    {
        $this->data = []; // Initialize array
        $this->data['patients'] = Patient::with(['user'])
            ->whereDate('created_at', today())
            ->orderBy('created_at', 'DESC')
            ->get()->toArray();
    }

    public function placeholder()
    {
        return view('livewire.dashboard_listing_table_skeleton');
    }

    public function render()
    {
        return view('livewire.staff-dash-board-table');
    }
}
