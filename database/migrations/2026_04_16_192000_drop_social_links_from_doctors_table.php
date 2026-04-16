<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('doctors')) {
            return;
        }

        Schema::table('doctors', function (Blueprint $table) {
            $dropColumns = [];

            foreach (['twitter_url', 'linkedin_url', 'instagram_url'] as $column) {
                if (Schema::hasColumn('doctors', $column)) {
                    $dropColumns[] = $column;
                }
            }

            if (! empty($dropColumns)) {
                $table->dropColumn($dropColumns);
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('doctors')) {
            return;
        }

        Schema::table('doctors', function (Blueprint $table) {
            if (! Schema::hasColumn('doctors', 'twitter_url')) {
                $table->string('twitter_url')->nullable()->after('consultation_hours');
            }

            if (! Schema::hasColumn('doctors', 'linkedin_url')) {
                $table->string('linkedin_url')->nullable()->after('twitter_url');
            }

            if (! Schema::hasColumn('doctors', 'instagram_url')) {
                $table->string('instagram_url')->nullable()->after('linkedin_url');
            }
        });
    }
};
