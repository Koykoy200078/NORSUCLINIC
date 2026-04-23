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
        Schema::create('lab_requests', function (Blueprint $table) {
            $table->id();

            // Auto-generated unique request number (6-digit)
            $table->string('request_number')->unique();

            // Users
            $table->unsignedBigInteger('document_creator_id'); // who created the request (staff/doctor/admin/patient)
            $table->unsignedBigInteger('patient_user_id');     // FK to users.id

            // Patient demographics (snapshot — same pattern as document_issuances)
            $table->string('patient_name');
            $table->integer('patient_age')->nullable();
            $table->string('patient_gender')->nullable();
            $table->date('patient_dob')->nullable();
            $table->string('patient_contact')->nullable();
            $table->string('address')->nullable();
            $table->string('campus')->nullable();
            $table->string('college')->nullable();
            $table->string('course')->nullable();
            $table->string('year_level')->nullable();
            $table->string('status_affiliation')->nullable(); // student/faculty/employee/guest

            // Request meta
            $table->date('requested_at');
            $table->string('clinical_indication')->nullable(); // reason/indication
            $table->text('remarks')->nullable();

            // Status tracking
            // pending → collected → processing → completed | cancelled | referred | rejected
            $table->enum('status', [
                'pending',
                'collected',
                'processing',
                'completed',
                'cancelled',
                'referred',
                'rejected',
            ])->default('pending');

            // Status timestamps
            $table->timestamp('collected_at')->nullable();
            $table->timestamp('processed_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->timestamp('referred_at')->nullable();
            $table->timestamp('rejected_at')->nullable();

            // Additional notes per status change
            $table->text('status_note')->nullable();

            // Requesting physician info (may differ from creator)
            $table->string('requesting_physician')->nullable();
            $table->string('physician_license_no')->nullable();

            $table->timestamps();

            // Indexes for performance
            $table->index('patient_user_id');
            $table->index('document_creator_id');
            $table->index('status');
            $table->index('requested_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lab_requests');
    }
};
