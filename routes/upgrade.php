<?php

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Route;

Route::get('lang-js', function () {
    Artisan::call('lang:js');
})->middleware(['auth', 'checkUserStatus', 'role:clinic_admin']);
