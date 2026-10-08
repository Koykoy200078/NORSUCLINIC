<?php

use App\Support\LegacyMysqlMigration;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * H-08: prescriptions.doctor_id / patient_id were ON DELETE CASCADE, so hard-deleting a doctor
 * (or a patient) silently erased every prescription (and, through prescriptions_medicines, every
 * prescribed line) attached to them. RESTRICT makes the database refuse instead; the application
 * additionally refuses to delete a doctor who has prescriptions and asks for deactivation.
 */
return new class extends Migration
{
    private const FOREIGN_KEYS = [
        ['table' => 'prescriptions', 'name' => 'prescriptions_doctor_id_foreign', 'column' => 'doctor_id', 'ref_table' => 'doctors'],
        ['table' => 'prescriptions', 'name' => 'prescriptions_patient_id_foreign', 'column' => 'patient_id', 'ref_table' => 'patients'],
    ];

    public function up(): void
    {
        $this->recreate('RESTRICT');
    }

    public function down(): void
    {
        $this->recreate('CASCADE');
    }

    private function recreate(string $deleteRule): void
    {
        if (! in_array(DB::getDriverName(), ['mysql', 'mariadb'], true)) {
            return;
        }

        foreach (self::FOREIGN_KEYS as $fk) {
            $exists = DB::selectOne(
                'SELECT COUNT(*) AS c FROM information_schema.TABLE_CONSTRAINTS
                 WHERE CONSTRAINT_SCHEMA = DATABASE() AND TABLE_NAME = ? AND CONSTRAINT_NAME = ? AND CONSTRAINT_TYPE = \'FOREIGN KEY\'',
                [$fk['table'], $fk['name']],
                false
            )?->c ?? 0;

            if (! $exists) {
                continue;
            }

            LegacyMysqlMigration::withoutZeroDateChecks(function () use ($fk, $deleteRule) {
                // Two statements: MariaDB rejects dropping and re-adding a constraint of the same name
                // inside one ALTER TABLE ("Duplicate key on write or update").
                DB::statement(sprintf('ALTER TABLE `%s` DROP FOREIGN KEY `%s`', $fk['table'], $fk['name']));
                DB::statement(sprintf(
                    'ALTER TABLE `%s` ADD CONSTRAINT `%s` FOREIGN KEY (`%s`) REFERENCES `%s` (`id`) ON DELETE %s ON UPDATE CASCADE',
                    $fk['table'],
                    $fk['name'],
                    $fk['column'],
                    $fk['ref_table'],
                    $deleteRule
                ));
            });
        }
    }
};
