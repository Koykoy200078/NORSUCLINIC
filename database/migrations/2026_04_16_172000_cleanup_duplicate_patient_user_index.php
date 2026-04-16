<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private function indexExists(string $table, string $indexName): bool
    {
        $result = DB::select(
            'SELECT 1 FROM information_schema.statistics WHERE table_schema = DATABASE() AND table_name = ? AND index_name = ? LIMIT 1',
            [$table, $indexName]
        );

        return ! empty($result);
    }

    public function up(): void
    {
        if (! Schema::hasTable('patients')) {
            return;
        }

        Schema::table('patients', function (Blueprint $table) {
            // Keep idx_patients_user_id and remove legacy duplicate idx_patients_user.
            if ($this->indexExists('patients', 'idx_patients_user') && $this->indexExists('patients', 'idx_patients_user_id')) {
                $table->dropIndex('idx_patients_user');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('patients')) {
            return;
        }

        Schema::table('patients', function (Blueprint $table) {
            if (! $this->indexExists('patients', 'idx_patients_user')) {
                $table->index('user_id', 'idx_patients_user');
            }
        });
    }
};
