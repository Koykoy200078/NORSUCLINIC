<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('users')) {
            return;
        }

        if (Schema::hasColumn('users', 'pager_extension')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('pager_extension');
            });
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('users')) {
            return;
        }

        if (! Schema::hasColumn('users', 'pager_extension')) {
            Schema::table('users', function (Blueprint $table) {
                $table->string('pager_extension', 60)->nullable()->after('contact');
            });
        }
    }
};
