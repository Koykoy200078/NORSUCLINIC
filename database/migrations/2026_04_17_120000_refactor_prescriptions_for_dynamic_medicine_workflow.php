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
        if (Schema::hasTable('prescriptions')) {
            Schema::table('prescriptions', function (Blueprint $table) {
                if (!Schema::hasColumn('prescriptions', 'doctor_license_s2_number')) {
                    $table->string('doctor_license_s2_number', 100)->nullable()->after('doctor_id');
                }

                if (!Schema::hasColumn('prescriptions', 'consultation_date')) {
                    $table->date('consultation_date')->nullable()->after('doctor_license_s2_number');
                }

                if (!Schema::hasColumn('prescriptions', 'icd10_diagnosis_id')) {
                    $table->unsignedBigInteger('icd10_diagnosis_id')->nullable()->after('consultation_date');
                    $table->index('icd10_diagnosis_id', 'idx_prescriptions_icd10_diagnosis_id');
                }

                if (!Schema::hasColumn('prescriptions', 'next_visit_days')) {
                    $table->unsignedInteger('next_visit_days')->nullable()->after('advice');
                }

                if (!Schema::hasColumn('prescriptions', 'weight_kg')) {
                    $table->decimal('weight_kg', 8, 2)->nullable()->after('next_visit_days');
                }

                if (!Schema::hasColumn('prescriptions', 'pulse_rate')) {
                    $table->string('pulse_rate', 50)->nullable()->after('weight_kg');
                }

                if (!Schema::hasColumn('prescriptions', 'body_temperature')) {
                    $table->decimal('body_temperature', 4, 1)->nullable()->after('pulse_rate');
                }

                if (!Schema::hasColumn('prescriptions', 'blood_pressure')) {
                    $table->string('blood_pressure', 50)->nullable()->after('body_temperature');
                }

                if (!Schema::hasColumn('prescriptions', 'height_cm')) {
                    $table->decimal('height_cm', 8, 2)->nullable()->after('blood_pressure');
                }

                // Remove redundant editable fields from prescription records.
                $dropColumns = [];
                foreach (['accident', 'surgery', 'medical_history', 'others'] as $column) {
                    if (Schema::hasColumn('prescriptions', $column)) {
                        $dropColumns[] = $column;
                    }
                }

                if (!empty($dropColumns)) {
                    $table->dropColumn($dropColumns);
                }
            });
        }

        if (Schema::hasTable('prescriptions_medicines')) {
            Schema::table('prescriptions_medicines', function (Blueprint $table) {
                if (!Schema::hasColumn('prescriptions_medicines', 'route_of_administration')) {
                    $table->string('route_of_administration', 50)->nullable()->after('dosage');
                }

                if (!Schema::hasColumn('prescriptions_medicines', 'frequency')) {
                    $table->unsignedInteger('frequency')->nullable()->after('route_of_administration');
                }

                if (!Schema::hasColumn('prescriptions_medicines', 'duration_value')) {
                    $table->unsignedInteger('duration_value')->nullable()->after('frequency');
                }

                if (!Schema::hasColumn('prescriptions_medicines', 'duration_unit')) {
                    $table->string('duration_unit', 20)->nullable()->after('duration_value');
                }

                if (!Schema::hasColumn('prescriptions_medicines', 'total_quantity')) {
                    $table->unsignedInteger('total_quantity')->nullable()->after('duration_unit');
                }

                if (!Schema::hasColumn('prescriptions_medicines', 'instructions')) {
                    $table->text('instructions')->nullable()->after('total_quantity');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('prescriptions')) {
            Schema::table('prescriptions', function (Blueprint $table) {
                if (Schema::hasColumn('prescriptions', 'icd10_diagnosis_id')) {
                    $table->dropIndex('idx_prescriptions_icd10_diagnosis_id');
                }

                $dropColumns = [];
                foreach ([
                    'doctor_license_s2_number',
                    'consultation_date',
                    'icd10_diagnosis_id',
                    'next_visit_days',
                    'weight_kg',
                    'pulse_rate',
                    'body_temperature',
                    'blood_pressure',
                    'height_cm',
                ] as $column) {
                    if (Schema::hasColumn('prescriptions', $column)) {
                        $dropColumns[] = $column;
                    }
                }

                if (!empty($dropColumns)) {
                    $table->dropColumn($dropColumns);
                }

                if (!Schema::hasColumn('prescriptions', 'accident')) {
                    $table->string('accident')->nullable();
                }

                if (!Schema::hasColumn('prescriptions', 'surgery')) {
                    $table->string('surgery')->nullable();
                }

                if (!Schema::hasColumn('prescriptions', 'medical_history')) {
                    $table->string('medical_history')->nullable();
                }

                if (!Schema::hasColumn('prescriptions', 'others')) {
                    $table->string('others')->nullable();
                }
            });
        }

        if (Schema::hasTable('prescriptions_medicines')) {
            Schema::table('prescriptions_medicines', function (Blueprint $table) {
                $dropColumns = [];
                foreach ([
                    'route_of_administration',
                    'frequency',
                    'duration_value',
                    'duration_unit',
                    'total_quantity',
                    'instructions',
                ] as $column) {
                    if (Schema::hasColumn('prescriptions_medicines', $column)) {
                        $dropColumns[] = $column;
                    }
                }

                if (!empty($dropColumns)) {
                    $table->dropColumn($dropColumns);
                }
            });
        }
    }
};
