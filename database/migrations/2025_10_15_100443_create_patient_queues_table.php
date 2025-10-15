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
        Schema::create('patient_queues', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('patient_id');
            $table->unsignedBigInteger('added_by');
            $table->string('room_number')->nullable();
            $table->boolean('is_priority')->default(false);
            $table->enum('status', ['waiting', 'in_progress', 'completed', 'cancelled'])->default('waiting');
            $table->text('notes')->nullable();
            $table->timestamp('called_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->foreign('patient_id')->references('id')->on('patients')->onDelete('cascade');
            $table->foreign('added_by')->references('id')->on('users')->onDelete('cascade');

            $table->index(['status', 'is_priority', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('patient_queues');
    }
};
