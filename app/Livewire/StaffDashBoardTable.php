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
        $this->data['patients'] = Patient::with(['user:id,first_name,last_name,email,created_at,university_id_number', 'media'])
            ->whereDate('created_at', today())
            ->orderBy('created_at', 'DESC')
            ->get()
            ->map(fn($p) => [
                'id'                 => $p->id,
                'patient_identifier' => $p->user->university_id_number ?: $p->patient_unique_id,
                'profile'            => $p->profile,
                'user'               => [
                    'id'         => $p->user->id,
                    'full_name'  => $p->user->full_name,
                    'email'      => $p->user->email,
                    'created_at' => $p->user->created_at,
                ],
            ])
            ->toArray();
    }

    public function placeholder(): string
    {
        return view('livewire.dashboard_listing_table_skeleton')->render();
    }

    public function render()
    {
        return view('livewire.staff-dash-board-table');
    }
}
