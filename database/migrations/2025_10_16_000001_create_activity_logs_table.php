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
        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable(); // clinic_admin, staff, or doctor
            $table->string('user_type')->nullable(); // admin, staff, doctor
            $table->string('user_name')->nullable(); // for display purposes
            $table->string('action'); // e.g., 'created', 'updated', 'deleted', 'used_medicine', etc.
            $table->string('subject_type')->nullable(); // e.g., 'Patient', 'RequestDocuments', 'Medicine'
            $table->unsignedBigInteger('subject_id')->nullable(); // ID of the affected record
            $table->text('description'); // Human-readable description

            // Patient/Document specific fields from request_documents
            $table->date('date')->nullable(); // Date of activity
            $table->string('patient_name')->nullable();
            $table->integer('patient_age')->nullable();
            $table->string('patient_gender')->nullable();
            $table->string('college')->nullable();
            $table->string('address')->nullable();
            $table->string('contact_number')->nullable();
            $table->text('complaints')->nullable();
            $table->text('diagnosis')->nullable(); // from assessment field
            $table->string('informant')->nullable();
            $table->string('consult_mode')->nullable();
            $table->string('course_section')->nullable(); // course + year_level

            // Additional metadata
            $table->json('properties')->nullable(); // Store any additional data as JSON
            $table->string('ip_address')->nullable();
            $table->string('user_agent')->nullable();

            $table->timestamps();

            // Indexes for better performance
            $table->index('user_id');
            $table->index('user_type');
            $table->index('subject_type');
            $table->index('subject_id');
            $table->index('action');
            $table->index('date');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('activity_logs');
    }
};
