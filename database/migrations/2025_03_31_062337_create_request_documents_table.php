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
        Schema::create('request_documents', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('name');
            $table->integer('age');
            $table->string('gender');
            $table->string('status')->nullable();
            $table->date('date_of_birth')->nullable();
            $table->string('address');
            $table->string('religion')->nullable();
            $table->string('patient_contact')->nullable();
            $table->string('campus')->nullable();
            $table->string('college')->nullable();
            $table->string('course')->nullable();
            $table->string('year_level')->nullable();
            $table->string('informant')->nullable();
            $table->string('emergency_contact')->nullable();
            $table->date('requested_at')->nullable();
            $table->string('complaints')->nullable();
            $table->string('covid_vaccination')->nullable();
            $table->string('comorbidities')->nullable();
            $table->string('allergies')->nullable();
            $table->string('admissions_surgeries')->nullable();
            $table->string('maintenance')->nullable();
            $table->string('pregnancy_status')->nullable();
            $table->string('lmp_aog')->nullable();
            $table->string('vital_signs_bp')->nullable();
            $table->string('vital_signs_pr')->nullable();
            $table->string('vital_signs_temp')->nullable();
            $table->string('vital_signs_rr')->nullable();
            $table->string('vital_signs_o2_sat')->nullable();
            $table->string('vital_signs_height')->nullable();
            $table->string('vital_signs_weight')->nullable();
            $table->string('pertinent_exam')->nullable();
            $table->string('assessment')->nullable();
            $table->string('plan')->nullable();
            $table->string('document_type');
            $table->string('consult_mode')->nullable();
            $table->string('nursing_intervention')->nullable();
            $table->string('nursing_incharged_id')->nullable();


            // Medical Certificate Fields
            $table->date('examined_on')->nullable();
            $table->string('complaints_diagnosis')->nullable();
            $table->string('medical_cert_remarks')->nullable();
            $table->string('doc_lic_no')->nullable();
            $table->string('doc_prt_no')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('request_documents');
    }
};
