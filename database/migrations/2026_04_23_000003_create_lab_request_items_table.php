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
        Schema::create('lab_request_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('lab_request_id');
            $table->unsignedBigInteger('lab_test_id')->nullable(); // FK to lab_tests (null = ad-hoc/custom)
            $table->string('test_name');           // snapshot of lab_tests.name (or custom name)
            $table->string('test_category')->nullable(); // snapshot of lab_tests.category
            $table->string('unit')->nullable();          // snapshot of lab_tests.unit
            $table->string('normal_range')->nullable();  // snapshot of lab_tests.normal_range
            $table->text('result_value')->nullable();    // the actual result (can be multi-line)
            $table->enum('result_status', ['pending', 'normal', 'abnormal', 'critical'])->default('pending');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->foreign('lab_request_id')
                  ->references('id')
                  ->on('lab_requests')
                  ->onDelete('cascade');

            $table->index('lab_request_id');
            $table->index('lab_test_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lab_request_items');
    }
};
