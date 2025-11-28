<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('patient_queues', function (Blueprint $table) {
            $table->unsignedBigInteger('latest_consultation_id')->nullable()->after('notes');
            $table->boolean('has_consultation_attachment')->default(false)->after('latest_consultation_id');

            // Add foreign key constraint
            $table->foreign('latest_consultation_id')
                ->references('id')
                ->on('request_documents')
                ->onDelete('set null');

            $table->index(['patient_id', 'latest_consultation_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('patient_queues', function (Blueprint $table) {
            $table->dropForeign(['latest_consultation_id']);
            $table->dropIndex(['patient_id', 'latest_consultation_id']);
            $table->dropColumn(['latest_consultation_id', 'has_consultation_attachment']);
        });
    }
};
