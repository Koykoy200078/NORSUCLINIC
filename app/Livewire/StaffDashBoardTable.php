<?php

namespace App\Livewire;

use Livewire\Attributes\Lazy;

/** The staff dashboard's recent registrations (RecentPatientsTable). */
#[Lazy]
class StaffDashBoardTable extends RecentPatientsTable
{
    protected function panel(): string
    {
        return 'staff';
    }
}
