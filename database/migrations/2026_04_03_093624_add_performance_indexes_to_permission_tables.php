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
        // model_has_roles: Spatie queries by (model_id, model_type) but only role_id is indexed — full table scan per user
        Schema::table('model_has_roles', function (Blueprint $table) {
            if (!$this->indexExists('model_has_roles', 'model_has_roles_model_id_type_index')) {
                $table->index(['model_id', 'model_type'], 'model_has_roles_model_id_type_index');
            }
        });

        // model_has_permissions: same issue — queries by model_id+model_type, only permission_id is indexed
        Schema::table('model_has_permissions', function (Blueprint $table) {
            // already has model_has_permissions_model_id_model_type_index, skip
        });

        // doctors: two duplicate indexes on user_id — remove the redundant one
        Schema::table('doctors', function (Blueprint $table) {
            if ($this->indexExists('doctors', 'idx_doctors_user')) {
                $table->dropIndex('idx_doctors_user');
            }
        });
    }

    public function down(): void
    {
        Schema::table('model_has_roles', function (Blueprint $table) {
            if ($this->indexExists('model_has_roles', 'model_has_roles_model_id_type_index')) {
                $table->dropIndex('model_has_roles_model_id_type_index');
            }
        });

        Schema::table('doctors', function (Blueprint $table) {
            $table->index('user_id', 'idx_doctors_user');
        });
    }

    private function indexExists(string $table, string $index): bool
    {
        return collect(\Illuminate\Support\Facades\DB::select("SHOW INDEX FROM `{$table}`"))
            ->pluck('Key_name')
            ->contains($index);
    }
};
