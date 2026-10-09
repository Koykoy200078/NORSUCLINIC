<?php

use App\Support\LegacySchemaUpgrade;
use Illuminate\Database\Migrations\Migration;

/**
 * CLINIC-DATA UPGRADE, step 1 (audit plan 1.2). The clinic's database was created on a server whose default engine is
 * MyISAM, so all of its 59 tables are MyISAM: no foreign keys (the earlier migrations that add or restrict them found
 * nothing to change), no transactions and no row locks, which the stock deduction, the queue and every
 * all-or-nothing save rely on. A server whose default is InnoDB cannot even build the newer illness tables on top of
 * them (error 1824: a foreign key cannot point at a MyISAM table).
 *
 * This migration sorts before the other September migrations, so every later step already sees InnoDB. It is a
 * no-op on a database that is InnoDB already.
 */
return new class extends Migration
{
    public function up(): void
    {
        $converted = LegacySchemaUpgrade::convertToInnoDb();

        if ($converted !== []) {
            LegacySchemaUpgrade::announce('Converted '.count($converted).' MyISAM table(s) to InnoDB.');
        }
    }

    public function down(): void
    {
        // Intentionally empty: going back to MyISAM would remove foreign keys and transactions again.
    }
};
