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

        return !empty($result);
    }

    /**
     * Improve archived/active patient filtering and document issuance counts.
     */
    public function up(): void
    {
        if (Schema::hasTable('patients')) {
            Schema::table('patients', function (Blueprint $table) {
                if (! $this->indexExists('patients', 'idx_patients_deleted_at')) {
                    $table->index('deleted_at', 'idx_patients_deleted_at');
                }

                if (! $this->indexExists('patients', 'idx_patients_deleted_created_at')) {
                    $table->index(['deleted_at', 'created_at'], 'idx_patients_deleted_created_at');
                }
            });
        }

        if (Schema::hasTable('document_issuances')) {
            Schema::table('document_issuances', function (Blueprint $table) {
                if (! $this->indexExists('document_issuances', 'idx_document_issuances_user_id')) {
                    $table->index('user_id', 'idx_document_issuances_user_id');
                }

                if (! $this->indexExists('document_issuances', 'idx_document_issuances_document_type')) {
                    $table->index('document_type', 'idx_document_issuances_document_type');
                }

                if (! $this->indexExists('document_issuances', 'idx_document_issuances_user_type')) {
                    $table->index(['user_id', 'document_type'], 'idx_document_issuances_user_type');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('patients')) {
            Schema::table('patients', function (Blueprint $table) {
                if ($this->indexExists('patients', 'idx_patients_deleted_created_at')) {
                    $table->dropIndex('idx_patients_deleted_created_at');
                }

                if ($this->indexExists('patients', 'idx_patients_deleted_at')) {
                    $table->dropIndex('idx_patients_deleted_at');
                }
            });
        }

        if (Schema::hasTable('document_issuances')) {
            Schema::table('document_issuances', function (Blueprint $table) {
                if ($this->indexExists('document_issuances', 'idx_document_issuances_user_type')) {
                    $table->dropIndex('idx_document_issuances_user_type');
                }

                if ($this->indexExists('document_issuances', 'idx_document_issuances_document_type')) {
                    $table->dropIndex('idx_document_issuances_document_type');
                }

                if ($this->indexExists('document_issuances', 'idx_document_issuances_user_id')) {
                    $table->dropIndex('idx_document_issuances_user_id');
                }
            });
        }
    }
};
