<?php

namespace App\Livewire;

use Livewire\Attributes\Lazy;

/** The admin dashboard's recent registrations (RecentPatientsTable). */
#[Lazy]
class AdminDashBoardTable extends RecentPatientsTable
{
    protected function panel(): string
    {
        return 'admin';
    }
}
