<?php

use App\Support\LegacyMysqlMigration;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * P2-M8 follow-up: MySQL used to run in non-strict mode, where an INSERT that omitted a
 * NOT NULL column without a default silently stored ''/0/'0000-00-00'. With strict mode on,
 * those inserts fail ("Field 'x' doesn't have a default value") and e.g. saving a prescription
 * or a medicine returned an error.
 *
 * Give each such legacy column the value MySQL used to substitute (or NULL where a zero date /
 * empty string was meaningless), so existing rows and existing code keep working unchanged.
 */
return new class extends Migration
{
    /** table => [column => full column definition] */
    private const COLUMNS = [
        'medicines' => [
            'salt_composition' => 'VARCHAR(191) NULL',
            'quantity' => 'INT NOT NULL DEFAULT 0',
            'available_quantity' => 'INT NOT NULL DEFAULT 0',
        ],
        'sale_medicines' => [
            'expiry_date' => 'DATETIME NULL',
        ],
        'purchased_medicines' => [
            'manufacturing_date' => 'VARCHAR(191) NULL',
        ],
        'prescriptions_medicines' => [
            'dose_interval' => 'INT NOT NULL DEFAULT 0',
        ],
        // A manual dispense record is created first and then points model_id at itself.
        'medicine_bills' => [
            'model_id' => 'VARCHAR(191) NULL',
        ],
    ];

    public function up(): void
    {
        $connection = DB::connection();
        if (! in_array($connection->getDriverName(), ['mysql', 'mariadb'], true)) {
            return;
        }

        $schema = $connection->getSchemaBuilder();

        // Rebuilding a table can revalidate zero dates in unrelated columns too.
        LegacyMysqlMigration::withoutZeroDateChecks(function () use ($connection, $schema) {
            foreach (self::COLUMNS as $table => $columns) {
                if (! $schema->hasTable($table)) {
                    continue;
                }

                foreach ($columns as $column => $definition) {
                    if (! $schema->hasColumn($table, $column)) {
                        continue;
                    }

                    $connection->statement(sprintf('ALTER TABLE `%s` MODIFY `%s` %s', $table, $column, $definition));
                }
            }
        });
    }

    public function down(): void
    {
        // Intentionally empty: restoring NOT NULL-without-default would re-break inserts.
    }
};
