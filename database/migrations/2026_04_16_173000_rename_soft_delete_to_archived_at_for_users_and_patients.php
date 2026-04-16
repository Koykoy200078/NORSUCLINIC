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
        if (Schema::hasTable('users')) {
            if (! Schema::hasColumn('users', 'archived_at')) {
                Schema::table('users', function (Blueprint $table) {
                    $table->timestamp('archived_at')->nullable()->after('deleted_at');
                });
            }

            if (Schema::hasColumn('users', 'deleted_at')) {
                DB::statement('UPDATE users SET archived_at = deleted_at WHERE archived_at IS NULL AND deleted_at IS NOT NULL');
            }

            Schema::table('users', function (Blueprint $table) {
                if (! $this->indexExists('users', 'idx_users_archived_at')) {
                    $table->index('archived_at', 'idx_users_archived_at');
                }
            });

            if (Schema::hasColumn('users', 'deleted_at')) {
                Schema::table('users', function (Blueprint $table) {
                    $table->dropColumn('deleted_at');
                });
            }
        }

        if (Schema::hasTable('patients')) {
            if (! Schema::hasColumn('patients', 'archived_at')) {
                Schema::table('patients', function (Blueprint $table) {
                    $table->timestamp('archived_at')->nullable()->after('deleted_at');
                });
            }

            if (Schema::hasColumn('patients', 'deleted_at')) {
                DB::statement('UPDATE patients SET archived_at = deleted_at WHERE archived_at IS NULL AND deleted_at IS NOT NULL');
            }

            Schema::table('patients', function (Blueprint $table) {
                if (! $this->indexExists('patients', 'idx_patients_archived_at')) {
                    $table->index('archived_at', 'idx_patients_archived_at');
                }

                if (! $this->indexExists('patients', 'idx_patients_archived_created_at')) {
                    $table->index(['archived_at', 'created_at'], 'idx_patients_archived_created_at');
                }
            });

            if (Schema::hasColumn('patients', 'deleted_at')) {
                Schema::table('patients', function (Blueprint $table) {
                    if ($this->indexExists('patients', 'idx_patients_deleted_created_at')) {
                        $table->dropIndex('idx_patients_deleted_created_at');
                    }
                    if ($this->indexExists('patients', 'idx_patients_deleted_at')) {
                        $table->dropIndex('idx_patients_deleted_at');
                    }
                    $table->dropColumn('deleted_at');
                });
            }
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('users')) {
            if (! Schema::hasColumn('users', 'deleted_at')) {
                Schema::table('users', function (Blueprint $table) {
                    $table->timestamp('deleted_at')->nullable()->after('archived_at');
                });
            }

            if (Schema::hasColumn('users', 'archived_at')) {
                DB::statement('UPDATE users SET deleted_at = archived_at WHERE deleted_at IS NULL AND archived_at IS NOT NULL');
            }

            Schema::table('users', function (Blueprint $table) {
                if ($this->indexExists('users', 'idx_users_archived_at')) {
                    $table->dropIndex('idx_users_archived_at');
                }
                if (Schema::hasColumn('users', 'archived_at')) {
                    $table->dropColumn('archived_at');
                }
            });
        }

        if (Schema::hasTable('patients')) {
            if (! Schema::hasColumn('patients', 'deleted_at')) {
                Schema::table('patients', function (Blueprint $table) {
                    $table->timestamp('deleted_at')->nullable()->after('archived_at');
                });
            }

            if (Schema::hasColumn('patients', 'archived_at')) {
                DB::statement('UPDATE patients SET deleted_at = archived_at WHERE deleted_at IS NULL AND archived_at IS NOT NULL');
            }

            Schema::table('patients', function (Blueprint $table) {
                if (! $this->indexExists('patients', 'idx_patients_deleted_at')) {
                    $table->index('deleted_at', 'idx_patients_deleted_at');
                }
                if (! $this->indexExists('patients', 'idx_patients_deleted_created_at')) {
                    $table->index(['deleted_at', 'created_at'], 'idx_patients_deleted_created_at');
                }

                if ($this->indexExists('patients', 'idx_patients_archived_created_at')) {
                    $table->dropIndex('idx_patients_archived_created_at');
                }
                if ($this->indexExists('patients', 'idx_patients_archived_at')) {
                    $table->dropIndex('idx_patients_archived_at');
                }

                if (Schema::hasColumn('patients', 'archived_at')) {
                    $table->dropColumn('archived_at');
                }
            });
        }
    }
};
