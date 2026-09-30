<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * roles.display_name / permissions.display_name are NOT NULL with no default. Anything that creates a
 * role or permission without a label (Spatie's own helpers, a migration, tinker) fails in strict SQL
 * mode with "Field 'display_name' doesn't have a default value". Give them an empty-string default.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! in_array(DB::getDriverName(), ['mysql', 'mariadb'], true)) {
            return;
        }

        foreach (['roles', 'permissions'] as $table) {
            if (Schema::hasTable($table) && Schema::hasColumn($table, 'display_name')) {
                DB::statement("ALTER TABLE `{$table}` MODIFY `display_name` VARCHAR(191) NOT NULL DEFAULT ''");
            }
        }
    }

    public function down(): void
    {
        // Intentionally empty: removing the default would re-break label-less inserts.
    }
};
