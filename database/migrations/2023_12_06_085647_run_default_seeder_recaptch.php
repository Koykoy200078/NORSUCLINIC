<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // AddCaptchaFieldSettingTableSeeder removed - reCAPTCHA feature has been removed
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
