<?php

use App\Services\Reports\ConsultationSnapshotBackfill;
use App\Support\LegacyMysqlMigration;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * A consultation used to keep only the NAMES of the patient's campus / college / course / year level, so the
 * report could not group or filter them reliably. The ids are now saved too (as they were on the day of the
 * visit), together with the patient type and the faculty department / staff office. Existing consultations are
 * filled in from their saved names and, failing that, from the patient's record.
 */
return new class extends Migration
{
    public function up(): void
    {
        LegacyMysqlMigration::withoutZeroDateChecks(function () {
            Schema::table('document_issuances', function (Blueprint $table) {
                foreach (['campus_id', 'college_id', 'course_id', 'year_level_id', 'department_id', 'office_id', 'patient_type_id'] as $column) {
                    $table->unsignedBigInteger($column)->nullable()->after('year_level');
                }

                $table->index('requested_at', 'document_issuances_requested_at_index');
                $table->index('college_id', 'document_issuances_college_id_index');
                $table->index('patient_type_id', 'document_issuances_patient_type_id_index');
            });
        });

        app(ConsultationSnapshotBackfill::class)->run();
    }

    public function down(): void
    {
        LegacyMysqlMigration::withoutZeroDateChecks(function () {
            Schema::table('document_issuances', function (Blueprint $table) {
                $table->dropIndex('document_issuances_requested_at_index');
                $table->dropIndex('document_issuances_college_id_index');
                $table->dropIndex('document_issuances_patient_type_id_index');
                $table->dropColumn(['campus_id', 'college_id', 'course_id', 'year_level_id', 'department_id', 'office_id', 'patient_type_id']);
            });
        });
    }
};
