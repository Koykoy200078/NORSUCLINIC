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
        // Add indexes for User table to optimize role/type queries
        Schema::table('users', function (Blueprint $table) {
            $table->index(['type', 'status'], 'idx_users_type_status');
            $table->index(['type', 'created_at'], 'idx_users_type_created');
            $table->index('email_verified_at', 'idx_users_email_verified');
        });

        // Add indexes for Patient table
        Schema::table('patients', function (Blueprint $table) {
            $table->index('created_at', 'idx_patients_created_at');
            $table->index('user_id', 'idx_patients_user_id');
        });

        // Add indexes for Settings table
        Schema::table('settings', function (Blueprint $table) {
            $table->index('key', 'idx_settings_key');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex('idx_users_type_status');
            $table->dropIndex('idx_users_type_created');
            $table->dropIndex('idx_users_email_verified');
        });

        Schema::table('patients', function (Blueprint $table) {
            $table->dropIndex('idx_patients_created_at');
            $table->dropIndex('idx_patients_user_id');
        });

        Schema::table('settings', function (Blueprint $table) {
            $table->dropIndex('idx_settings_key');
        });
    }
};
