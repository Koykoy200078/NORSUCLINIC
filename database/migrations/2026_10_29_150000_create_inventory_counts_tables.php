<?php

use App\Support\SchemaInspector;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Quarterly count sheets (Phase 11 D, owner request 2026-10-29): the clinic types the final remaining count of each medicine
 * for a quarter (or for everything on the day it counted), with a reason and a note, and the reports show it next to what the
 * system says. Two new tables, always InnoDB, made only when missing (a second run changes nothing); no existing table changes.
 *
 * - `inventory_counts`: one sheet (year, quarter 1-4 or null for "everything counted that day", draft or finalized).
 * - `inventory_count_lines`: one medicine on a sheet: the system amount when the sheet was made or last refreshed, what was
 *   counted, the difference, a reason and a note, and when the shelf stock was corrected from it. The medicine's name, strength
 *   and unit are copied so a later rename or merge does not change what was counted.
 *
 * Rolling back refuses while any sheet exists: a count is a record, never thrown away silently.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('inventory_counts')) {
            Schema::create('inventory_counts', function (Blueprint $table): void {
                $table->engine = 'InnoDB';
                $table->id();
                $table->unsignedSmallInteger('year');
                $table->unsignedTinyInteger('quarter')->nullable();
                $table->string('scope', 10)->default('all');
                $table->date('counted_on');
                $table->string('status', 12)->default('draft');
                $table->text('notes')->nullable();
                $this->idColumn($table, 'created_by', 'users')->nullable();
                $this->idColumn($table, 'finalized_by', 'users')->nullable();
                $table->dateTime('finalized_at')->nullable();
                $table->timestamps();
                $table->index(['year', 'quarter', 'status'], 'idx_inventory_counts_period');
                $table->foreign('created_by')->references('id')->on('users')->nullOnDelete();
                $table->foreign('finalized_by')->references('id')->on('users')->nullOnDelete();
            });
        }

        if (! Schema::hasTable('inventory_count_lines')) {
            Schema::create('inventory_count_lines', function (Blueprint $table): void {
                $table->engine = 'InnoDB';
                $table->id();
                $table->unsignedBigInteger('inventory_count_id');
                $this->idColumn($table, 'medicine_id', 'medicines');
                $table->string('medicine_name', 191);
                $table->string('strength', 100)->nullable();
                $table->string('unit', 50)->nullable();
                $table->integer('system_quantity');
                $table->integer('counted_quantity')->nullable();
                $table->integer('difference')->nullable();
                $table->string('reason', 30)->nullable();
                $table->string('note', 500)->nullable();
                $table->dateTime('adjusted_at')->nullable();
                $this->idColumn($table, 'adjusted_by', 'users')->nullable();
                // The audit row of the stock correction (the audit trail is append-only, so no key is needed).
                $table->unsignedBigInteger('adjusted_audit_id')->nullable();
                $table->timestamps();
                $table->unique(['inventory_count_id', 'medicine_id'], 'uq_inventory_count_lines');
                $table->foreign('inventory_count_id')->references('id')->on('inventory_counts')->cascadeOnDelete();
                $table->foreign('medicine_id')->references('id')->on('medicines')->restrictOnDelete();
                $table->foreign('adjusted_by')->references('id')->on('users')->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('inventory_counts') && DB::table('inventory_counts')->exists()) {
            throw new RuntimeException('Count sheets exist; export or keep them: this rollback would delete every count sheet.');
        }

        Schema::dropIfExists('inventory_count_lines');
        Schema::dropIfExists('inventory_counts');
    }

    /** A column of the same type as the id it points at (the clinic's older tables may use 32-bit ids). */
    private function idColumn(Blueprint $table, string $name, string $referenced): \Illuminate\Database\Schema\ColumnDefinition
    {
        $type = (string) SchemaInspector::columnType($referenced, 'id');

        return str_contains($type, 'bigint') ? $table->unsignedBigInteger($name) : $table->unsignedInteger($name);
    }
};
