<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $this->refactorPrescriptions();
        $this->refactorPrescriptionMedicineLines();
        $this->expandMedicinesMaster();
        $this->ensureInventoryTables();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $this->dropInventoryTables();
        $this->rollbackMedicinesMaster();
        $this->rollbackPrescriptionRefactor();
        $this->rollbackPrescriptionMedicineLineRefactor();
    }

    private function refactorPrescriptions(): void
    {
        if (!Schema::hasTable('prescriptions')) {
            return;
        }

        Schema::table('prescriptions', function (Blueprint $table) {
            if (!Schema::hasColumn('prescriptions', 'doctor_license_s2_number')) {
                $table->string('doctor_license_s2_number', 100)->nullable()->after('doctor_id');
            }

            if (!Schema::hasColumn('prescriptions', 'consultation_date')) {
                $table->date('consultation_date')->nullable()->after('doctor_license_s2_number');
            }

            if (!Schema::hasColumn('prescriptions', 'icd10_diagnosis_id')) {
                $table->unsignedBigInteger('icd10_diagnosis_id')->nullable()->after('consultation_date');
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

            if (!Schema::hasColumn('prescriptions', 'is_active')) {
                $table->boolean('is_active')->default(true)->after('reference');
            }

            if (!Schema::hasColumn('prescriptions', 'dispensed_at')) {
                $table->timestamp('dispensed_at')->nullable()->after('status');
            }

            if (!Schema::hasColumn('prescriptions', 'dispensed_by')) {
                $table->unsignedBigInteger('dispensed_by')->nullable()->after('dispensed_at');
            }

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

        if (Schema::hasColumn('prescriptions', 'icd10_diagnosis_id')) {
            try {
                Schema::table('prescriptions', function (Blueprint $table) {
                    $table->index('icd10_diagnosis_id', 'idx_prescriptions_icd10_diagnosis_id');
                });
            } catch (\Throwable $e) {
                // Index may already exist.
            }
        }

        if (Schema::hasColumn('prescriptions', 'status')) {
            $statusType = $this->safeColumnType('prescriptions', 'status');
            if (in_array($statusType, ['bool', 'boolean', 'tinyint', 'tinyinteger', 'integer', 'int', 'bigint', 'smallint'], true)) {
                DB::table('prescriptions')->update([
                    'is_active' => DB::raw('COALESCE(status, 1)'),
                ]);

                Schema::table('prescriptions', function (Blueprint $table) {
                    $table->dropColumn('status');
                });
            }
        }

        if (!Schema::hasColumn('prescriptions', 'status')) {
            Schema::table('prescriptions', function (Blueprint $table) {
                $table->enum('status', ['pending', 'dispensed', 'cancelled'])->default('pending')->after('is_active');
            });
        }

        if (Schema::hasColumn('prescriptions', 'status')) {
            try {
                Schema::table('prescriptions', function (Blueprint $table) {
                    $table->index('status', 'idx_prescriptions_dispense_status');
                });
            } catch (\Throwable $e) {
                // Index may already exist.
            }

            DB::table('prescriptions')->whereNull('status')->update(['status' => 'pending']);
        }

        if (Schema::hasColumn('prescriptions', 'dispensed_by')) {
            try {
                Schema::table('prescriptions', function (Blueprint $table) {
                    $table->index('dispensed_by', 'idx_prescriptions_dispensed_by');
                });
            } catch (\Throwable $e) {
                // Index may already exist.
            }

            try {
                Schema::table('prescriptions', function (Blueprint $table) {
                    $table->foreign('dispensed_by', 'fk_prescriptions_dispensed_by')
                        ->references('id')
                        ->on('users')
                        ->nullOnDelete();
                });
            } catch (\Throwable $e) {
                // Foreign key may already exist.
            }
        }
    }

    private function refactorPrescriptionMedicineLines(): void
    {
        if (!Schema::hasTable('prescriptions_medicines')) {
            return;
        }

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

    private function expandMedicinesMaster(): void
    {
        if (!Schema::hasTable('medicines')) {
            return;
        }

        Schema::table('medicines', function (Blueprint $table) {
            if (!Schema::hasColumn('medicines', 'generic_name')) {
                $table->string('generic_name')->nullable()->after('generic_id');
            }

            if (!Schema::hasColumn('medicines', 'brand_name')) {
                $table->string('brand_name')->nullable()->after('generic_name');
            }

            if (!Schema::hasColumn('medicines', 'category')) {
                $table->string('category')->default('Uncategorized')->after('brand_name');
            }

            if (!Schema::hasColumn('medicines', 'category_name')) {
                $table->string('category_name')->nullable()->after('category');
            }

            if (!Schema::hasColumn('medicines', 'dosage')) {
                $table->string('dosage')->nullable()->after('category_name');
            }

            if (!Schema::hasColumn('medicines', 'uom')) {
                $table->string('uom', 50)->nullable()->after('dosage');
            }

            if (!Schema::hasColumn('medicines', 'sku')) {
                $table->string('sku', 100)->nullable()->after('uom');
            }

            if (!Schema::hasColumn('medicines', 'reorder_level')) {
                $table->unsignedInteger('reorder_level')->default(0)->after('sku');
            }

            if (!Schema::hasColumn('medicines', 'baseline_quantity')) {
                $table->unsignedInteger('baseline_quantity')->default(0)->after('reorder_level');
            }
        });

        if (Schema::hasColumn('medicines', 'sku')) {
            try {
                Schema::table('medicines', function (Blueprint $table) {
                    $table->unique('sku', 'uq_medicines_sku');
                });
            } catch (\Throwable $e) {
                // Unique key may already exist.
            }
        }

        if (Schema::hasTable('generics') && Schema::hasTable('categories')) {
            DB::statement(
                "UPDATE medicines m
                 LEFT JOIN generics g ON g.id = m.generic_id
                 LEFT JOIN categories c ON c.id = m.category_id
                 SET
                    m.generic_name = COALESCE(NULLIF(m.generic_name, ''), g.name, m.name),
                    m.brand_name = COALESCE(NULLIF(m.brand_name, ''), m.name),
                    m.category = COALESCE(NULLIF(m.category, ''), NULLIF(m.category_name, ''), c.name, 'Uncategorized'),
                    m.category_name = COALESCE(NULLIF(m.category_name, ''), c.name, 'Uncategorized')"
            );
        }

        if (Schema::hasColumn('medicines', 'category') && Schema::hasColumn('medicines', 'category_name')) {
            DB::statement(
                "UPDATE medicines
                 SET
                    category = COALESCE(NULLIF(category, ''), NULLIF(category_name, ''), 'Uncategorized'),
                    category_name = COALESCE(NULLIF(category_name, ''), NULLIF(category, ''), 'Uncategorized')"
            );
        }

        if (Schema::hasColumn('medicines', 'dosage') && Schema::hasColumn('medicines', 'uom')) {
            DB::statement(
                "UPDATE medicines
                 SET
                    dosage = COALESCE(NULLIF(dosage, ''), 'Standard dose'),
                    uom = COALESCE(NULLIF(uom, ''), 'unit')"
            );
        }

        if (Schema::hasColumn('medicines', 'minimum_stock_alert') && Schema::hasColumn('medicines', 'reorder_level')) {
            DB::statement('UPDATE medicines SET reorder_level = COALESCE(reorder_level, minimum_stock_alert, 0)');
        }

        if (Schema::hasColumn('medicines', 'baseline_quantity')) {
            DB::statement(
                'UPDATE medicines
                 SET baseline_quantity = COALESCE(NULLIF(baseline_quantity, 0), NULLIF(quantity, 0), NULLIF(available_quantity, 0), 0)'
            );
        }
    }

    private function ensureInventoryTables(): void
    {
        if (!Schema::hasTable('medicine_batches')) {
            Schema::create('medicine_batches', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('medicine_id');
                $table->string('batch_number', 100);
                $table->string('dosage', 100)->nullable();
                $table->unsignedInteger('quantity')->default(0);
                $table->date('manufacturing_date')->nullable();
                $table->date('expiration_date');
                $table->string('supplier_name')->nullable();
                $table->decimal('unit_cost', 8, 2)->nullable();
                $table->date('date_received');
                $table->timestamps();

                $table->foreign('medicine_id')->references('id')->on('medicines')->onDelete('cascade');
                $table->unique(['medicine_id', 'batch_number'], 'uq_medicine_batches_medicine_batch');
                $table->index(['medicine_id', 'expiration_date'], 'idx_medicine_batches_fefo');
                $table->index(['medicine_id', 'quantity'], 'idx_medicine_batches_quantity');
            });
        } elseif (!Schema::hasColumn('medicine_batches', 'dosage')) {
            Schema::table('medicine_batches', function (Blueprint $table) {
                $table->string('dosage', 100)->nullable()->after('batch_number');
            });
        }

        if (
            Schema::hasTable('medicine_batches') &&
            Schema::hasColumn('medicine_batches', 'dosage') &&
            Schema::hasTable('medicines') &&
            Schema::hasColumn('medicines', 'dosage')
        ) {
            DB::statement(
                "UPDATE medicine_batches mb
                 INNER JOIN medicines m ON m.id = mb.medicine_id
                 SET mb.dosage = COALESCE(NULLIF(mb.dosage, ''), NULLIF(m.dosage, ''), 'N/A')"
            );
        }

        if (Schema::hasTable('medicine_batches')) {
            DB::statement('UPDATE medicine_batches SET date_received = COALESCE(date_received, CURDATE())');
            DB::statement('UPDATE medicine_batches SET expiration_date = COALESCE(expiration_date, date_received, CURDATE())');
            DB::statement('UPDATE medicine_batches SET unit_cost = NULL WHERE unit_cost IS NOT NULL AND unit_cost > 999999.99');

            try {
                DB::statement('ALTER TABLE medicine_batches MODIFY COLUMN expiration_date DATE NOT NULL');
                DB::statement('ALTER TABLE medicine_batches MODIFY COLUMN date_received DATE NOT NULL');
                DB::statement('ALTER TABLE medicine_batches MODIFY COLUMN unit_cost DECIMAL(8,2) NULL');
            } catch (\Throwable $e) {
                // Table engine/version differences may affect MODIFY support.
            }
        }

        if (!Schema::hasTable('medicine_transactions')) {
            Schema::create('medicine_transactions', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('batch_id');
                $table->unsignedBigInteger('user_id')->nullable();
                $table->enum('transaction_type', ['stock_in', 'dispense', 'adjustment', 'disposal']);
                $table->integer('quantity');
                $table->integer('balance_after');
                $table->string('reference_type')->nullable();
                $table->unsignedBigInteger('reference_id')->nullable();
                $table->text('remarks')->nullable();
                $table->timestamps();

                $table->foreign('batch_id')->references('id')->on('medicine_batches')->onDelete('cascade');
                $table->foreign('user_id')->references('id')->on('users')->nullOnDelete();
                $table->index(['batch_id', 'created_at'], 'idx_medicine_transactions_batch_date');
                $table->index(['transaction_type', 'created_at'], 'idx_medicine_transactions_type_date');
                $table->index(['reference_type', 'reference_id'], 'idx_medicine_transactions_reference');
            });
        }

        if (Schema::hasTable('medicine_transactions')) {
            DB::statement(
                "UPDATE medicine_transactions
                 SET transaction_type = CASE
                     WHEN transaction_type = 'stock_out' THEN 'dispense'
                     WHEN transaction_type = 'return' THEN 'disposal'
                     ELSE transaction_type
                 END"
            );
            DB::statement('UPDATE medicine_transactions SET quantity = ABS(quantity)');

            try {
                DB::statement("ALTER TABLE medicine_transactions MODIFY COLUMN transaction_type ENUM('stock_in','dispense','adjustment','disposal') NOT NULL");
            } catch (\Throwable $e) {
                // Table engine/version differences may affect MODIFY support.
            }
        }
    }

    private function dropInventoryTables(): void
    {
        Schema::dropIfExists('medicine_transactions');
        Schema::dropIfExists('medicine_batches');
    }

    private function rollbackMedicinesMaster(): void
    {
        if (!Schema::hasTable('medicines')) {
            return;
        }

        try {
            Schema::table('medicines', function (Blueprint $table) {
                $table->dropUnique('uq_medicines_sku');
            });
        } catch (\Throwable $e) {
            // Unique key may not exist.
        }

        Schema::table('medicines', function (Blueprint $table) {
            $dropColumns = [];
            foreach (['generic_name', 'brand_name', 'category', 'category_name', 'dosage', 'uom', 'sku', 'reorder_level', 'baseline_quantity'] as $column) {
                if (Schema::hasColumn('medicines', $column)) {
                    $dropColumns[] = $column;
                }
            }

            if (!empty($dropColumns)) {
                $table->dropColumn($dropColumns);
            }
        });
    }

    private function rollbackPrescriptionRefactor(): void
    {
        if (!Schema::hasTable('prescriptions')) {
            return;
        }

        try {
            Schema::table('prescriptions', function (Blueprint $table) {
                $table->dropForeign('fk_prescriptions_dispensed_by');
            });
        } catch (\Throwable $e) {
            // Foreign key may not exist.
        }

        try {
            Schema::table('prescriptions', function (Blueprint $table) {
                $table->dropIndex('idx_prescriptions_dispensed_by');
            });
        } catch (\Throwable $e) {
            // Index may not exist.
        }

        if (Schema::hasColumn('prescriptions', 'status')) {
            $statusType = $this->safeColumnType('prescriptions', 'status');

            if (!in_array($statusType, ['bool', 'boolean', 'tinyint', 'tinyinteger', 'integer', 'int'], true)) {
                try {
                    Schema::table('prescriptions', function (Blueprint $table) {
                        $table->dropIndex('idx_prescriptions_dispense_status');
                    });
                } catch (\Throwable $e) {
                    // Index may not exist.
                }

                Schema::table('prescriptions', function (Blueprint $table) {
                    if (!Schema::hasColumn('prescriptions', 'legacy_status')) {
                        $table->boolean('legacy_status')->nullable();
                    }
                });

                DB::table('prescriptions')->update([
                    'legacy_status' => DB::raw("CASE WHEN status = 'cancelled' THEN 0 ELSE 1 END"),
                ]);

                Schema::table('prescriptions', function (Blueprint $table) {
                    $table->dropColumn('status');
                });

                Schema::table('prescriptions', function (Blueprint $table) {
                    $table->renameColumn('legacy_status', 'status');
                });
            }
        }

        if (Schema::hasColumn('prescriptions', 'is_active') && Schema::hasColumn('prescriptions', 'status')) {
            DB::table('prescriptions')->update([
                'status' => DB::raw('COALESCE(is_active, 1)'),
            ]);
        }

        if (Schema::hasColumn('prescriptions', 'icd10_diagnosis_id')) {
            try {
                Schema::table('prescriptions', function (Blueprint $table) {
                    $table->dropIndex('idx_prescriptions_icd10_diagnosis_id');
                });
            } catch (\Throwable $e) {
                // Index may not exist.
            }
        }

        Schema::table('prescriptions', function (Blueprint $table) {
            $dropColumns = [];
            foreach (
                [
                    'doctor_license_s2_number',
                    'consultation_date',
                    'icd10_diagnosis_id',
                    'next_visit_days',
                    'weight_kg',
                    'pulse_rate',
                    'body_temperature',
                    'blood_pressure',
                    'height_cm',
                    'dispensed_by',
                    'dispensed_at',
                    'is_active',
                ] as $column
            ) {
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

    private function rollbackPrescriptionMedicineLineRefactor(): void
    {
        if (!Schema::hasTable('prescriptions_medicines')) {
            return;
        }

        Schema::table('prescriptions_medicines', function (Blueprint $table) {
            $dropColumns = [];
            foreach (
                [
                    'route_of_administration',
                    'frequency',
                    'duration_value',
                    'duration_unit',
                    'total_quantity',
                    'instructions',
                ] as $column
            ) {
                if (Schema::hasColumn('prescriptions_medicines', $column)) {
                    $dropColumns[] = $column;
                }
            }

            if (!empty($dropColumns)) {
                $table->dropColumn($dropColumns);
            }
        });
    }

    private function safeColumnType(string $table, string $column): ?string
    {
        if (!Schema::hasColumn($table, $column)) {
            return null;
        }

        try {
            return Schema::getColumnType($table, $column);
        } catch (\Throwable $e) {
            return null;
        }
    }
};
