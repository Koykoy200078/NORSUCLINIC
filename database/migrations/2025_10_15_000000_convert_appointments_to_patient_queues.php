<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Convert appointments table to patient_queues system
     */
    public function up(): void
    {
        // Rename the table
        Schema::rename('appointments', 'patient_queues');

        // Add new columns for queue system
        Schema::table('patient_queues', function (Blueprint $table) {
            // Queue-specific fields
            $table->string('room_number')->nullable()->after('description');
            $table->boolean('priority')->default(false)->after('room_number');
            $table->integer('queue_number')->nullable()->after('priority');

            // Tracking fields
            $table->unsignedBigInteger('admitted_by')->nullable()->after('queue_number');
            $table->timestamp('admitted_at')->nullable()->after('admitted_by');
            $table->timestamp('started_at')->nullable()->after('admitted_at');
            $table->timestamp('completed_at')->nullable()->after('started_at');

            // Foreign key for admitted_by (user who admitted the patient)
            $table->foreign('admitted_by')
                ->references('id')
                ->on('users')
                ->onDelete('set null')
                ->onUpdate('cascade');

            // Add indexes for queue system
            $table->index(['queue_number', 'date'], 'idx_queue_number_date');
            $table->index(['priority', 'status'], 'idx_priority_status');
            $table->index(['room_number'], 'idx_room_number');
            $table->index(['admitted_by'], 'idx_admitted_by');
        });

        // Update status values to match queue system
        // BOOKED (1) → WAITING (1) - no change needed
        // ACCEPTED (2) → IN_PROGRESS (2) - no change needed
        // FINISHED (3) → COMPLETED (3) - no change needed
        // CANCELLED (4) → CANCELLED (4) - no change needed

        // Set admitted_at to created_at for existing records
        DB::statement('UPDATE patient_queues SET admitted_at = created_at WHERE admitted_at IS NULL');

        // Set queue_number for existing records (ordered by created_at per day)
        DB::statement('
            UPDATE patient_queues pq
            JOIN (
                SELECT id, 
                       ROW_NUMBER() OVER (PARTITION BY date ORDER BY created_at) as new_queue_number
                FROM patient_queues
            ) as numbered
            ON pq.id = numbered.id
            SET pq.queue_number = numbered.new_queue_number
            WHERE pq.queue_number IS NULL
        ');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove new columns
        Schema::table('patient_queues', function (Blueprint $table) {
            // Drop foreign key first
            $table->dropForeign(['admitted_by']);

            // Drop indexes
            $table->dropIndex('idx_queue_number_date');
            $table->dropIndex('idx_priority_status');
            $table->dropIndex('idx_room_number');
            $table->dropIndex('idx_admitted_by');

            // Drop columns
            $table->dropColumn([
                'room_number',
                'priority',
                'queue_number',
                'admitted_by',
                'admitted_at',
                'started_at',
                'completed_at'
            ]);
        });

        // Rename back to appointments
        Schema::rename('patient_queues', 'appointments');
    }
};
