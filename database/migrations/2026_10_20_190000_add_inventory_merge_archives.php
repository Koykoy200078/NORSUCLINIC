<?php

use App\Models\Category;
use App\Models\Generic;
use App\Models\Medicine;
use App\Support\SchemaInspector;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        foreach (['medicines', 'categories', 'generics'] as $name) {
            if (! Schema::hasColumn($name, 'merged_into_id')) {
                $big = str_contains((string) SchemaInspector::columnType($name, 'id'), 'bigint');
                Schema::table($name, function (Blueprint $table) use ($big): void {
                    if ($big) {
                        $table->unsignedBigInteger('merged_into_id')->nullable();
                    } else {
                        $table->unsignedInteger('merged_into_id')->nullable();
                    }
                });
            }
            if (! Schema::hasColumn($name, 'merged_at')) {
                Schema::table($name, function (Blueprint $table): void {
                    $table->dateTime('merged_at')->nullable();
                });
            }
            if (! Schema::hasColumn($name, 'merged_by')) {
                Schema::table($name, function (Blueprint $table): void {
                    $table->unsignedBigInteger('merged_by')->nullable();
                });
            }
            $hasForeignKey = collect(SchemaInspector::foreignKeys())->contains(fn (array $key) => $key['name'] === $name.'_merged_into_id_foreign');
            if (! $hasForeignKey) {
                Schema::table($name, function (Blueprint $table) use ($name): void {
                    $table->foreign('merged_into_id')->references('id')->on($name)->onDelete('restrict');
                });
            }
        }
        if (! Schema::hasColumn('medicine_batches', 'original_batch_number')) {
            Schema::table('medicine_batches', function (Blueprint $table): void {
                $table->string('original_batch_number', 100)->nullable();
            });
        }
        $this->resetModels();
    }

    public function down(): void
    {
        foreach (['medicines', 'categories', 'generics'] as $name) {
            if (DB::table($name)->whereNotNull('merged_into_id')->exists()) {
                throw new RuntimeException('Merge archives exist; restore a pre-merge backup before rolling back this migration.');
            }
        }
        foreach (['medicines', 'categories', 'generics'] as $name) {
            Schema::table($name, function (Blueprint $table): void {
                $table->dropForeign(['merged_into_id']);
                $table->dropColumn(['merged_into_id', 'merged_at', 'merged_by']);
            });
        }
        Schema::table('medicine_batches', function (Blueprint $table): void {
            $table->dropColumn('original_batch_number');
        });
        $this->resetModels();
    }

    private function resetModels(): void
    {
        foreach ([Medicine::class, Category::class, Generic::class] as $model) {
            $model::forgetMergeSchemaCache();
        }
    }
};
