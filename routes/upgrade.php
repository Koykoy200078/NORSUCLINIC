<?php

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Route;

Route::get('lang-js', function () {
    Artisan::call('lang:js');
});
