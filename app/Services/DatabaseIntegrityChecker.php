<?php

namespace App\Services;

use App\Models\User;
use App\Support\ForeignKeyCatalog;
use App\Support\LegacySchemaUpgrade;
use App\Support\SchemaInspector;
use App\Support\SchemaParity;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;
use Symfony\Component\Console\Output\BufferedOutput;

/**
 * The checks behind `php artisan db:integrity`: is this database what a correct installation looks like, and is its data
 * consistent? Every check returns
 *
 *     ['key' => ..., 'title' => ..., 'severity' => 'ok'|'warning'|'error', 'errors' => [...], 'warnings' => [...]]
 *
 * An error is something that is wrong and must be fixed; a warning is worth a look but does not stop the clinic working.
 * The checks only read. The one exception is compareWithFreshInstall(), which builds a throwaway schema of its own (and
 * drops it again) and never touches the live one.
 */
final class DatabaseIntegrityChecker
{
    /** Business numbers and keys that must be unique: table => list of column sets. */
    private const UNIQUE_NUMBERS = [
        'lab_requests' => [['request_number']],
        'medicine_availabilities' => [['availability_no']],
        'medicine_bills' => [['history_number']],
        'medicines' => [['sku']],
        'patients' => [['patient_unique_id']],
        'settings' => [['key']],
        'users' => [['email'], ['university_id_number']],
        'doctors' => [['prc_license_number']],
        'medicine_batches' => [['medicine_id', 'batch_number']],
    ];

    /** Columns that may point at a record that is gone: the audit trail keeps the name of a deleted user. */
    private const MAY_DANGLE = ['activity_logs.user_id', 'sessions.user_id'];

    /** Columns whose parent table cannot be guessed from the name (user_id -> users). */
    private const PARENT_TABLES = [
        'added_by' => 'users', 'dispensed_by' => 'users', 'created_by' => 'users', 'updated_by' => 'users',
        'deleted_by' => 'users', 'document_creator_id' => 'users', 'nursing_incharged_id' => 'users',
        'patient_user_id' => 'users', 'medicine' => 'medicines',
    ];

    public function __construct(private readonly InventoryConsistency $inventory = new InventoryConsistency())
    {
    }

    /**
     * @return list<array{key: string, title: string, severity: string, errors: list<string>, warnings: list<string>}>
     */
    public function run(): array
    {
        return [
            $this->engines(),
            $this->foreignKeys(),
            $this->danglingReferences(),
            $this->zeroDates(),
            $this->duplicateKeys(),
            $this->medicineTotals(),
            $this->batchBalances(),
            $this->consultationMedicines(),
            $this->stockWithoutLedger(),
            $this->directPermissions(),
            $this->duplicateCandidates(),
            $this->pendingRepairs(),
        ];
    }

    /**
     * @return array{key: string, title: string, severity: string, errors: list<string>, warnings: list<string>}
     */
    public function engines(): array
    {
        $errors = [];
        foreach (SchemaInspector::tableEngines() as $table => $engine) {
            if ($engine !== 'InnoDB') {
                $errors[] = "table {$table} is {$engine}, not InnoDB";
            }
        }

        return $this->result('engines', 'Every table is InnoDB (foreign keys and transactions work)', $errors);
    }

    /**
     * @return array{key: string, title: string, severity: string, errors: list<string>, warnings: list<string>}
     */
    public function foreignKeys(): array
    {
        $engines = SchemaInspector::tableEngines();
        $existing = SchemaInspector::foreignKeys();
        $errors = [];

        foreach (ForeignKeyCatalog::all() as $key) {
            foreach ($existing as $other) {
                if ($other['table'] === $key['table'] && $other['columns'] === $key['columns']
                    && $other['refTable'] === $key['refTable'] && $other['refColumns'] === $key['refColumns']) {
                    continue 2;
                }
            }

            $reason = LegacySchemaUpgrade::whyKeyCannotBeAdded($key, $engines);
            $errors[] = $key['name'].': '.($reason ?? 'missing, nothing blocks it - run php artisan db:restore-foreign-keys');
        }

        return $this->result('foreign-keys', 'All '.count(ForeignKeyCatalog::all()).' foreign keys are in place', $errors);
    }

    /**
     * Rows whose `xxx_id` points at a record that does not exist, for columns that have no foreign key. The parent
     * table is guessed from the column name (campus_id -> campuses).
     *
     * @return array{key: string, title: string, severity: string, errors: list<string>, warnings: list<string>}
     */
    public function danglingReferences(): array
    {
        $tables = SchemaInspector::tableEngines();
        $covered = [];
        foreach ([...ForeignKeyCatalog::all(), ...SchemaInspector::foreignKeys()] as $key) {
            $covered[$key['table'].'.'.$key['columns'][0]] = true;
        }

        $names = implode(',', array_map(fn (string $name): string => DB::getPdo()->quote($name), array_keys(self::PARENT_TABLES)));
        $columns = DB::select(
            "SELECT c.TABLE_NAME AS tbl, c.COLUMN_NAME AS col
             FROM information_schema.COLUMNS c
             JOIN information_schema.TABLES t ON t.TABLE_SCHEMA = c.TABLE_SCHEMA AND t.TABLE_NAME = c.TABLE_NAME AND t.TABLE_TYPE = 'BASE TABLE'
             WHERE c.TABLE_SCHEMA = DATABASE() AND (c.COLUMN_NAME LIKE '%\\_id' OR c.COLUMN_NAME IN ({$names}))
             ORDER BY c.TABLE_NAME, c.ORDINAL_POSITION",
            [],
            false
        );

        $errors = [];
        foreach ($columns as $column) {
            $where = $column->tbl.'.'.$column->col;
            $parent = self::PARENT_TABLES[$column->col] ?? Str::plural(Str::beforeLast($column->col, '_id'));
            if (isset($covered[$where]) || in_array($where, self::MAY_DANGLE, true)
                || ! isset($tables[$parent]) || $parent === $column->tbl || SchemaInspector::columnType($parent, 'id') === null) {
                continue;
            }

            $from = 'FROM '.SchemaInspector::quote($column->tbl).' c LEFT JOIN '.SchemaInspector::quote($parent).' p ON p.id = c.'
                .SchemaInspector::quote($column->col).' WHERE c.'.SchemaInspector::quote($column->col).' IS NOT NULL AND p.id IS NULL';
            $rows = (int) (DB::selectOne("SELECT COUNT(*) AS n {$from}", [], false)->n ?? 0);
            if ($rows === 0) {
                continue;
            }

            $values = array_column(DB::select(
                'SELECT DISTINCT c.'.SchemaInspector::quote($column->col)." AS value {$from} ORDER BY 1 LIMIT 10",
                [],
                false
            ), 'value');
            $errors[] = "{$where}: {$rows} row(s) point at {$parent} that do not exist (".implode(', ', $values).($rows > count($values) ? ', ...' : '').')';
        }

        return $this->result('dangling-references', 'No row points at a record that does not exist', $errors);
    }

    /**
     * @return array{key: string, title: string, severity: string, errors: list<string>, warnings: list<string>}
     */
    public function zeroDates(): array
    {
        $errors = [];
        $warnings = [];

        foreach (SchemaInspector::dateColumns() as $column) {
            $name = SchemaInspector::quote($column['column']);
            $rows = (int) (DB::selectOne(
                'SELECT COUNT(*) AS n FROM '.SchemaInspector::quote($column['table'])." WHERE YEAR({$name}) = 0 OR MONTH({$name}) = 0 OR DAY({$name}) = 0",
                [],
                false
            )->n ?? 0);
            if ($rows === 0) {
                continue;
            }

            $where = $column['table'].'.'.$column['column'];
            if ($column['nullable']) {
                $errors[] = "{$where}: {$rows} zero date(s) - run php artisan migrate";
            } else {
                $warnings[] = "{$where}: {$rows} zero date(s) in a NOT NULL column - fix by hand";
            }
        }

        return $this->result('zero-dates', 'No zero dates (0000-00-00)', $errors, $warnings);
    }

    /**
     * @return array{key: string, title: string, severity: string, errors: list<string>, warnings: list<string>}
     */
    public function duplicateKeys(): array
    {
        $errors = [];

        foreach (self::UNIQUE_NUMBERS as $table => $sets) {
            foreach ($sets as $columns) {
                foreach ($columns as $column) {
                    if (SchemaInspector::columnType($table, $column) === null) {
                        continue 2;
                    }
                }

                $list = implode(', ', array_map([SchemaInspector::class, 'quote'], $columns));
                $present = implode(' AND ', array_map(fn (string $c): string => SchemaInspector::quote($c).' IS NOT NULL', $columns));
                $rows = DB::select(
                    "SELECT {$list}, COUNT(*) AS n FROM ".SchemaInspector::quote($table)." WHERE {$present} GROUP BY {$list} HAVING COUNT(*) > 1 ORDER BY n DESC LIMIT 10",
                    [],
                    false
                );
                foreach ($rows as $row) {
                    $value = implode(', ', array_map(fn (string $c) => $row->{$c}, $columns));
                    $where = count($columns) === 1 ? "{$table}.{$columns[0]}" : "{$table}(".implode(', ', $columns).')';
                    $errors[] = "{$where}: \"{$value}\" appears {$row->n} times";
                }
            }
        }

        return $this->result('duplicate-keys', 'No duplicate numbers or keys (history, request, SKU, e-mail, ...)', $errors);
    }

    /**
     * @return array{key: string, title: string, severity: string, errors: list<string>, warnings: list<string>}
     */
    public function medicineTotals(): array
    {
        $errors = array_map(
            fn (array $row): string => "{$row['name']} (#{$row['id']}): recorded {$row['recorded']}, batches add up to {$row['batches']}",
            $this->inventory->totalMismatches()
        );

        return $this->result('medicine-totals', 'Medicine totals match their batches', $errors);
    }

    /**
     * @return array{key: string, title: string, severity: string, errors: list<string>, warnings: list<string>}
     */
    public function batchBalances(): array
    {
        $errors = array_map(
            fn (array $row): string => "batch #{$row['id']} ({$row['medicine']}, {$row['batch_number']}): quantity {$row['quantity']}, the ledger says {$row['ledger']}",
            $this->inventory->ledgerMismatches()
        );

        return $this->result('batch-balances', 'Batch balances match the stock ledger', $errors);
    }

    /**
     * @return array{key: string, title: string, severity: string, errors: list<string>, warnings: list<string>}
     */
    public function consultationMedicines(): array
    {
        $errors = array_map(
            fn (array $row): string => "consultation #{$row['consultation']}, {$row['medicine']} (#{$row['medicine_id']}): the lines say {$row['lines']}, the ledger deducted {$row['ledger']}",
            $this->inventory->consultationMismatches()
        );

        return $this->result('consultation-medicines', 'Consultation medicines match the stock ledger', $errors);
    }

    /**
     * @return array{key: string, title: string, severity: string, errors: list<string>, warnings: list<string>}
     */
    public function stockWithoutLedger(): array
    {
        $warnings = array_map(
            fn (array $row): string => "{$row['name']} (#{$row['id']}): {$row['units']} unit(s) in batches that have no ledger row",
            $this->inventory->stockWithoutLedger()
        );

        return $this->result('stock-without-ledger', 'No stock without ledger rows (the yearly report needs them)', [], $warnings);
    }

    /**
     * Permissions come from the user's role; a permission given straight to a non-admin user bypasses the Roles screen.
     *
     * @return array{key: string, title: string, severity: string, errors: list<string>, warnings: list<string>}
     */
    public function directPermissions(): array
    {
        $rows = DB::select(
            "SELECT u.id, GROUP_CONCAT(DISTINCT r.name ORDER BY r.name) AS roles, COUNT(DISTINCT mp.permission_id) AS direct
             FROM model_has_permissions mp
             JOIN users u ON u.id = mp.model_id AND mp.model_type = ?
             LEFT JOIN model_has_roles mr ON mr.model_id = u.id AND mr.model_type = ?
             LEFT JOIN roles r ON r.id = mr.role_id
             GROUP BY u.id
             HAVING COALESCE(SUM(r.name = 'clinic_admin'), 0) = 0
             ORDER BY u.id",
            [User::class, User::class],
            false
        );

        $warnings = array_map(
            fn ($row): string => "user #{$row->id} (".($row->roles ?: 'no role')."): {$row->direct} permission(s) given directly instead of by the role",
            $rows
        );

        return $this->result('direct-permissions', 'No direct permissions on non-admin users (the role is the only source)', [], $warnings);
    }

    /**
     * Medicines with the same name and strength, patients with the same name and birth date: probably the same thing
     * entered twice. Only ids are listed.
     *
     * @return array{key: string, title: string, severity: string, errors: list<string>, warnings: list<string>}
     */
    public function duplicateCandidates(): array
    {
        $warnings = [];

        foreach (DB::select(
            "SELECT GROUP_CONCAT(id ORDER BY id) AS ids FROM medicines
             GROUP BY LOWER(TRIM(name)), LOWER(TRIM(COALESCE(dosage, ''))), COALESCE(generic_id, 0)
             HAVING COUNT(*) > 1 ORDER BY MIN(id)",
            [],
            false
        ) as $row) {
            $warnings[] = 'medicines '.$this->hashList($row->ids).': same name and strength';
        }

        foreach (DB::select(
            'SELECT GROUP_CONCAT(p.id ORDER BY p.id) AS ids FROM patients p JOIN users u ON u.id = p.user_id
             WHERE u.archived_at IS NULL
             GROUP BY LOWER(TRIM(u.first_name)), LOWER(TRIM(u.last_name)), u.dob
             HAVING COUNT(*) > 1 ORDER BY MIN(p.id)',
            [],
            false
        ) as $row) {
            $warnings[] = 'patients '.$this->hashList($row->ids).': same name and birth date';
        }

        return $this->result('duplicate-candidates', 'No duplicate candidates (medicines, patients)', [], $warnings);
    }

    /**
     * What the two repair commands would still change (their dry runs).
     *
     * @return array{key: string, title: string, severity: string, errors: list<string>, warnings: list<string>}
     */
    public function pendingRepairs(): array
    {
        $warnings = [];

        $text = new BufferedOutput();
        Artisan::call('text:repair-entities', [], $text);
        if (preg_match('/Would decode (\d+) value\(s\)/', $text->fetch(), $match) === 1) {
            $warnings[] = "text:repair-entities would decode {$match[1]} value(s) - run it with --apply after a backup";
        }

        $phone = new BufferedOutput();
        Artisan::call('phone:normalize', [], $phone);
        if (preg_match('/Dry run: (\d+) record\(s\) would be updated/', $phone->fetch(), $match) === 1 && (int) $match[1] > 0) {
            $warnings[] = "phone:normalize would update {$match[1]} record(s) - run it with --apply after a backup";
        }

        return $this->result('pending-repairs', 'No pending data repairs (encoded text, phone numbers)', [], $warnings);
    }

    /**
     * Build a fresh install in a throwaway schema (all migrations), compare its structure with this database, and drop
     * the throwaway schema again. The live database is only read.
     *
     * @return list<string> one line per structural difference (empty when the schemas are the same)
     */
    public function compareWithFreshInstall(): array
    {
        $live = DB::connection();
        $scratch = substr($live->getDatabaseName(), 0, 48).'_fresh_compare';
        $settings = array_merge($live->getConfig(), ['url' => null, 'prefix' => '']);
        config([
            'database.connections.integrity_server' => array_merge($settings, ['name' => 'integrity_server', 'database' => null]),
            'database.connections.integrity_fresh' => array_merge($settings, ['name' => 'integrity_fresh', 'database' => $scratch]),
        ]);

        $server = DB::connection('integrity_server');
        $dropScratch = fn () => $server->statement('DROP DATABASE IF EXISTS '.SchemaInspector::quote($scratch));

        try {
            $dropScratch();
            $server->getSchemaBuilder()->createDatabase($scratch);

            $output = new BufferedOutput();
            if (Artisan::call('migrate', ['--database' => 'integrity_fresh', '--force' => true], $output) !== 0) {
                throw new RuntimeException('The fresh install could not be built: '.mb_substr($output->fetch(), -600));
            }

            return SchemaParity::differences(SchemaInspector::describe($scratch), SchemaInspector::describe());
        } finally {
            DB::purge('integrity_fresh');
            $dropScratch();
            DB::purge('integrity_server');
        }
    }

    /**
     * @param  list<string>  $errors
     * @param  list<string>  $warnings
     * @return array{key: string, title: string, severity: string, errors: list<string>, warnings: list<string>}
     */
    private function result(string $key, string $title, array $errors = [], array $warnings = []): array
    {
        return [
            'key' => $key,
            'title' => $title,
            'severity' => $errors !== [] ? 'error' : ($warnings !== [] ? 'warning' : 'ok'),
            'errors' => $errors,
            'warnings' => $warnings,
        ];
    }

    /**
     * "41,42" => "#41, #42"
     */
    private function hashList(string $ids): string
    {
        return '#'.str_replace(',', ', #', $ids);
    }
}
