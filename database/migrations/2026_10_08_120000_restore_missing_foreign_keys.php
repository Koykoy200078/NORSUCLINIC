<?php

use App\Support\LegacySchemaUpgrade;
use Illuminate\Database\Migrations\Migration;

/**
 * CLINIC-DATA UPGRADE, step 4 (audit plan 1.4). A fresh install has 47 foreign keys; the clinic's MyISAM database has
 * none, because MyISAM ignores them (the earlier migrations that restrict or add keys found nothing to change). Once the
 * tables are InnoDB (2026_09_30_110000) this adds every missing key of App\Support\ForeignKeyCatalog.
 *
 * It never changes data. A key is added only when both tables are InnoDB, the two columns have the same type and no
 * row points at a parent that does not exist; otherwise the key is skipped and the reason is printed, so the orphan rows
 * can be looked at and fixed by hand. `php artisan db:integrity` lists what is still missing.
 */
return new class extends Migration
{
    public function up(): void
    {
        $report = LegacySchemaUpgrade::restoreForeignKeys();

        if ($report['added'] !== []) {
            LegacySchemaUpgrade::announce('Restored '.count($report['added']).' foreign key(s).');
        }

        foreach ($report['skipped'] as $name => $reason) {
            LegacySchemaUpgrade::announce("Foreign key {$name} NOT added: {$reason}.", true);
        }
    }

    public function down(): void
    {
        // Intentionally empty: the keys belong to every correct installation.
    }
};
