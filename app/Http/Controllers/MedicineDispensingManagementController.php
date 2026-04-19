<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

class MedicineDispensingManagementController extends AppBaseController
{
    public function index(): View
    {
        return view('medicine-dispensing.index');
    }
}
