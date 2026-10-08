<?php

use App\Support\LegacyMysqlMigration;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Consultations, medical certificates and excuse slips are clinical records. Deleting one from a screen used to remove
 * the row for good; it now sets `deleted_at` instead - the record disappears from every list, report and search but
 * can still be recovered, and its photos stay with it. (R3-M6; the clinic's records have to be kept.)
 *
 * Rolling this back would make every deleted record visible again, so down() keeps the rows hidden by refusing to run
 * while any deleted record exists.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('document_issuances', 'deleted_at')) {
            return;
        }

        LegacyMysqlMigration::withoutZeroDateChecks(function () {
            Schema::table('document_issuances', function (Blueprint $table) {
                $table->softDeletes();
                $table->index('deleted_at', 'document_issuances_deleted_at_index');
            });
        });
    }

    public function down(): void
    {
        if (! Schema::hasColumn('document_issuances', 'deleted_at')) {
            return;
        }

        $deleted = \Illuminate\Support\Facades\DB::table('document_issuances')->useWritePdo()->whereNotNull('deleted_at')->count();
        if ($deleted > 0) {
            throw new RuntimeException("{$deleted} deleted clinical record(s) would become visible again. Recover or purge them first.");
        }

        LegacyMysqlMigration::withoutZeroDateChecks(function () {
            Schema::table('document_issuances', function (Blueprint $table) {
                $table->dropIndex('document_issuances_deleted_at_index');
                $table->dropSoftDeletes();
            });
        });
    }
};
