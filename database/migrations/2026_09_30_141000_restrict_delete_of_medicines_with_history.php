<?php

use App\Support\LegacyMysqlMigration;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * C-04: deleting a medicine used to cascade through the database and erase its stock batches, the
 * whole stock ledger (medicine_transactions), prescription lines and consultation medicine rows.
 * RESTRICT makes the database refuse instead; the application also refuses up front and explains.
 * The ledger's own link to a batch is protected the same way.
 */
return new class extends Migration
{
    /** table.column => referenced table */
    private const FOREIGN_KEYS = [
        ['table' => 'medicine_batches', 'column' => 'medicine_id', 'ref_table' => 'medicines'],
        ['table' => 'consultation_medicines', 'column' => 'medicine_id', 'ref_table' => 'medicines'],
        ['table' => 'prescriptions_medicines', 'column' => 'medicine', 'ref_table' => 'medicines'],
        ['table' => 'used_medicines', 'column' => 'medicine_id', 'ref_table' => 'medicines'],
        ['table' => 'medicine_transactions', 'column' => 'batch_id', 'ref_table' => 'medicine_batches'],
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
            $existing = DB::selectOne(
                'SELECT k.CONSTRAINT_NAME AS name, r.UPDATE_RULE AS update_rule
                 FROM information_schema.KEY_COLUMN_USAGE k
                 JOIN information_schema.REFERENTIAL_CONSTRAINTS r
                   ON r.CONSTRAINT_SCHEMA = k.CONSTRAINT_SCHEMA AND r.CONSTRAINT_NAME = k.CONSTRAINT_NAME
                 WHERE k.TABLE_SCHEMA = DATABASE() AND k.TABLE_NAME = ? AND k.COLUMN_NAME = ?
                   AND k.REFERENCED_TABLE_NAME = ?',
                [$fk['table'], $fk['column'], $fk['ref_table']],
                false
            );

            if (! $existing) {
                continue;
            }

            // Two statements: MariaDB rejects dropping and re-adding the same constraint name in
            // one ALTER TABLE.
            LegacyMysqlMigration::withoutZeroDateChecks(function () use ($fk, $existing, $deleteRule) {
                DB::statement(sprintf('ALTER TABLE `%s` DROP FOREIGN KEY `%s`', $fk['table'], $existing->name));
                DB::statement(sprintf(
                    'ALTER TABLE `%s` ADD CONSTRAINT `%s` FOREIGN KEY (`%s`) REFERENCES `%s` (`id`) ON DELETE %s ON UPDATE %s',
                    $fk['table'],
                    $existing->name,
                    $fk['column'],
                    $fk['ref_table'],
                    $deleteRule,
                    $existing->update_rule ?: 'RESTRICT'
                ));
            });
        }
    }
};
