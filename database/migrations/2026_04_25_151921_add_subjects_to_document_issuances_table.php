<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('document_issuances', function (Blueprint $table) {
            $table->json('subjects')->nullable()->after('medical_cert_remarks');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('document_issuances', function (Blueprint $table) {
            $table->dropColumn('subjects');
        });
    }
};
