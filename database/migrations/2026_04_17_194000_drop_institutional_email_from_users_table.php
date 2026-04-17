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
        if (! Schema::hasTable('users')) {
            return;
        }

        Schema::table('users', function (Blueprint $table) {
            if ($this->indexExists('users', 'idx_users_institutional_email')) {
                $table->dropIndex('idx_users_institutional_email');
            }

            if (Schema::hasColumn('users', 'institutional_email')) {
                $table->dropColumn('institutional_email');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('users')) {
            return;
        }

        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'institutional_email')) {
                $table->string('institutional_email', 191)->nullable()->after('email');
            }

            if (! $this->indexExists('users', 'idx_users_institutional_email')) {
                $table->unique('institutional_email', 'idx_users_institutional_email');
            }
        });
    }
};
