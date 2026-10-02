<?php

use Database\Seeders\IllnessSeeder;
use Database\Seeders\ServiceTypeSeeder;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * ACCOMPLISHMENT REPORT (2026-10-01): a consultation now records WHICH illness and WHICH services, picked from the
 * clinic's own lists, instead of only free text. The lists are loaded here from the clinic's report form so every
 * clinic copy has them after `php artisan migrate`; the admin can add or switch off lines later.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('illness_systems', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100)->unique();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('illnesses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('illness_system_id')->constrained('illness_systems')->cascadeOnDelete();
            $table->string('group_label', 120)->nullable();
            $table->string('name', 150);
            $table->boolean('is_other')->default(false);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['illness_system_id', 'name']);
        });

        Schema::create('consultation_illnesses', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('document_issuance_id');
            $table->foreignId('illness_id')->constrained('illnesses')->restrictOnDelete();
            $table->string('other_text', 150)->nullable();
            $table->timestamps();

            $table->unique(['document_issuance_id', 'illness_id'], 'cons_illness_doc_illness_unique');
            $table->foreign('document_issuance_id')->references('id')->on('document_issuances')->cascadeOnDelete();
        });

        Schema::create('service_types', function (Blueprint $table) {
            $table->id();
            $table->string('category', 30);
            $table->string('name', 150);
            $table->string('auto_rule', 40)->nullable();
            $table->boolean('is_other')->default(false);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['category', 'name']);
        });

        Schema::create('consultation_services', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('document_issuance_id');
            $table->foreignId('service_type_id')->constrained('service_types')->restrictOnDelete();
            $table->timestamps();

            $table->unique(['document_issuance_id', 'service_type_id'], 'cons_service_doc_service_unique');
            $table->foreign('document_issuance_id')->references('id')->on('document_issuances')->cascadeOnDelete();
        });

        (new IllnessSeeder())->run();
        (new ServiceTypeSeeder())->run();
    }

    public function down(): void
    {
        Schema::dropIfExists('consultation_services');
        Schema::dropIfExists('service_types');
        Schema::dropIfExists('consultation_illnesses');
        Schema::dropIfExists('illnesses');
        Schema::dropIfExists('illness_systems');
    }
};
