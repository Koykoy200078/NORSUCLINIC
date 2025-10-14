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
        Schema::create('consultation_medicines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('request_document_id')->constrained('request_documents')->onDelete('cascade');
            $table->foreignId('medicine_id')->constrained('medicines')->onDelete('cascade');
            $table->integer('quantity')->default(1);
            $table->string('used_for')->nullable(); // 'plan' or 'nursing_intervention'
            $table->text('dosage_instructions')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('consultation_medicines');
    }
};
