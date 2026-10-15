<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Plan Phase 5 (Pass A, validation vs columns): the dispense record form accepts a note of up to 2,000 characters, but the
 * column held 191, so a longer note made the whole save fail in strict mode. The column becomes TEXT (same character set
 * and collation, still optional). No row changes.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('medicine_bills') && Schema::hasColumn('medicine_bills', 'note')) {
            DB::statement('ALTER TABLE `medicine_bills` MODIFY `note` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL');
        }
    }

    public function down(): void
    {
        // Narrowing would be refused in strict mode (or cut the text) for a note longer than 191 characters: only narrow when none is.
        if (Schema::hasTable('medicine_bills') && Schema::hasColumn('medicine_bills', 'note')
            && ! DB::table('medicine_bills')->whereRaw('CHAR_LENGTH(note) > 191')->exists()) {
            DB::statement('ALTER TABLE `medicine_bills` MODIFY `note` VARCHAR(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL');
        }
    }
};
