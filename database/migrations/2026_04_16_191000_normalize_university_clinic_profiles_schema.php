<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private function indexExists(string $table, string $indexName): bool
    {
        $result = DB::select(
            'SELECT 1 FROM information_schema.statistics WHERE table_schema = DATABASE() AND table_name = ? AND index_name = ? LIMIT 1',
            [$table, $indexName]
        );

        return ! empty($result);
    }

    public function up(): void
    {
        if (! Schema::hasTable('patient_types')) {
            Schema::create('patient_types', function (Blueprint $table) {
                $table->id();
                $table->string('code', 50)->unique();
                $table->string('name', 100)->unique();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('insurance_providers')) {
            Schema::create('insurance_providers', function (Blueprint $table) {
                $table->id();
                $table->string('name', 191)->unique();
                $table->string('contact_no', 100)->nullable();
                $table->string('email', 191)->nullable();
                $table->string('website', 191)->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('staff_designations')) {
            Schema::create('staff_designations', function (Blueprint $table) {
                $table->id();
                $table->string('code', 60)->unique();
                $table->string('name', 120)->unique();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('clinic_stations')) {
            Schema::create('clinic_stations', function (Blueprint $table) {
                $table->id();
                $table->string('code', 60)->unique();
                $table->string('name', 120)->unique();
                $table->text('description')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('staff_profiles')) {
            Schema::create('staff_profiles', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('user_id')->unique();
                $table->unsignedBigInteger('role_designation_id')->nullable();
                $table->unsignedBigInteger('assigned_station_id')->nullable();
                $table->text('shift_schedule')->nullable();
                $table->timestamps();

                $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade')->onUpdate('cascade');
                $table->foreign('role_designation_id')->references('id')->on('staff_designations')->nullOnDelete()->cascadeOnUpdate();
                $table->foreign('assigned_station_id')->references('id')->on('clinic_stations')->nullOnDelete()->cascadeOnUpdate();
            });
        }

        if (Schema::hasTable('users')) {
            $addUniversityIdNumber = ! Schema::hasColumn('users', 'university_id_number');
            $addEmployeeId = ! Schema::hasColumn('users', 'employee_id');
            $addNationalityCitizenship = ! Schema::hasColumn('users', 'nationality_citizenship');

            if ($addUniversityIdNumber || $addEmployeeId || $addNationalityCitizenship) {
                Schema::table('users', function (Blueprint $table) use ($addUniversityIdNumber, $addEmployeeId, $addNationalityCitizenship) {
                    if ($addUniversityIdNumber) {
                        $table->string('university_id_number', 100)->nullable()->after('country_code');
                    }

                    if ($addEmployeeId) {
                        $table->string('employee_id', 100)->nullable()->after('university_id_number');
                    }

                    if ($addNationalityCitizenship) {
                        $table->string('nationality_citizenship', 120)->nullable()->after('gender');
                    }
                });
            }

            Schema::table('users', function (Blueprint $table) {
                if (! $this->indexExists('users', 'idx_users_university_id_number')) {
                    $table->unique('university_id_number', 'idx_users_university_id_number');
                }

                if (! $this->indexExists('users', 'idx_users_employee_id')) {
                    $table->unique('employee_id', 'idx_users_employee_id');
                }
            });
        }

        if (Schema::hasTable('doctors')) {
            $addPrcLicenseNumber = ! Schema::hasColumn('doctors', 'prc_license_number');
            $addPtrNumber = ! Schema::hasColumn('doctors', 'ptr_number');
            $addS2LicenseNumber = ! Schema::hasColumn('doctors', 's2_license_number');
            $addConsultationHours = ! Schema::hasColumn('doctors', 'consultation_hours');

            if ($addPrcLicenseNumber || $addPtrNumber || $addS2LicenseNumber || $addConsultationHours) {
                Schema::table('doctors', function (Blueprint $table) use ($addPrcLicenseNumber, $addPtrNumber, $addS2LicenseNumber, $addConsultationHours) {
                    if ($addPrcLicenseNumber) {
                        $table->string('prc_license_number', 100)->nullable()->after('experience');
                    }

                    if ($addPtrNumber) {
                        $table->string('ptr_number', 100)->nullable()->after('prc_license_number');
                    }

                    if ($addS2LicenseNumber) {
                        $table->string('s2_license_number', 100)->nullable()->after('ptr_number');
                    }

                    if ($addConsultationHours) {
                        $table->text('consultation_hours')->nullable()->after('s2_license_number');
                    }
                });
            }

            Schema::table('doctors', function (Blueprint $table) {
                if (! $this->indexExists('doctors', 'idx_doctors_prc_license_number')) {
                    $table->unique('prc_license_number', 'idx_doctors_prc_license_number');
                }
            });
        }

        if (Schema::hasTable('patients')) {
            $addPatientTypeId = ! Schema::hasColumn('patients', 'patient_type_id');
            $addImmunizationRecord = ! Schema::hasColumn('patients', 'immunization_record');
            $addInsuranceProviderId = ! Schema::hasColumn('patients', 'insurance_provider_id');
            $addInsurancePolicyNumber = ! Schema::hasColumn('patients', 'insurance_policy_number');
            $addPrimaryCarePhysicianName = ! Schema::hasColumn('patients', 'primary_care_physician_name');
            $addPrimaryCarePhysicianContact = ! Schema::hasColumn('patients', 'primary_care_physician_contact');
            $addPrimaryCarePhysicianEmail = ! Schema::hasColumn('patients', 'primary_care_physician_email');

            if (
                $addPatientTypeId ||
                $addImmunizationRecord ||
                $addInsuranceProviderId ||
                $addInsurancePolicyNumber ||
                $addPrimaryCarePhysicianName ||
                $addPrimaryCarePhysicianContact ||
                $addPrimaryCarePhysicianEmail
            ) {
                Schema::table('patients', function (Blueprint $table) use (
                    $addPatientTypeId,
                    $addImmunizationRecord,
                    $addInsuranceProviderId,
                    $addInsurancePolicyNumber,
                    $addPrimaryCarePhysicianName,
                    $addPrimaryCarePhysicianContact,
                    $addPrimaryCarePhysicianEmail
                ) {
                    if ($addPatientTypeId) {
                        $table->unsignedBigInteger('patient_type_id')->nullable()->after('user_id');
                    }

                    if ($addImmunizationRecord) {
                        $table->text('immunization_record')->nullable()->after('covid_vaccination');
                    }

                    if ($addInsuranceProviderId) {
                        $table->unsignedBigInteger('insurance_provider_id')->nullable()->after('immunization_record');
                    }

                    if ($addInsurancePolicyNumber) {
                        $table->string('insurance_policy_number', 120)->nullable()->after('insurance_provider_id');
                    }

                    if ($addPrimaryCarePhysicianName) {
                        $table->string('primary_care_physician_name', 191)->nullable()->after('insurance_policy_number');
                    }

                    if ($addPrimaryCarePhysicianContact) {
                        $table->string('primary_care_physician_contact', 100)->nullable()->after('primary_care_physician_name');
                    }

                    if ($addPrimaryCarePhysicianEmail) {
                        $table->string('primary_care_physician_email', 191)->nullable()->after('primary_care_physician_contact');
                    }
                });
            }

            Schema::table('patients', function (Blueprint $table) {
                if (! $this->indexExists('patients', 'idx_patients_patient_type_id')) {
                    $table->index('patient_type_id', 'idx_patients_patient_type_id');
                }

                if (! $this->indexExists('patients', 'idx_patients_insurance_provider_id')) {
                    $table->index('insurance_provider_id', 'idx_patients_insurance_provider_id');
                }

                if (! $this->indexExists('patients', 'idx_patients_primary_care_physician_email')) {
                    $table->index('primary_care_physician_email', 'idx_patients_primary_care_physician_email');
                }
            });

            if ($addPatientTypeId) {
                Schema::table('patients', function (Blueprint $table) {
                    $table->foreign('patient_type_id')->references('id')->on('patient_types')->nullOnDelete()->cascadeOnUpdate();
                });
            }

            if ($addInsuranceProviderId) {
                Schema::table('patients', function (Blueprint $table) {
                    $table->foreign('insurance_provider_id')->references('id')->on('insurance_providers')->nullOnDelete()->cascadeOnUpdate();
                });
            }
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('patients')) {
            Schema::table('patients', function (Blueprint $table) {
                if ($this->indexExists('patients', 'idx_patients_primary_care_physician_email')) {
                    $table->dropIndex('idx_patients_primary_care_physician_email');
                }
                if ($this->indexExists('patients', 'idx_patients_insurance_provider_id')) {
                    $table->dropIndex('idx_patients_insurance_provider_id');
                }
                if ($this->indexExists('patients', 'idx_patients_patient_type_id')) {
                    $table->dropIndex('idx_patients_patient_type_id');
                }
            });

            Schema::table('patients', function (Blueprint $table) {
                if (Schema::hasColumn('patients', 'patient_type_id')) {
                    $table->dropForeign(['patient_type_id']);
                }
                if (Schema::hasColumn('patients', 'insurance_provider_id')) {
                    $table->dropForeign(['insurance_provider_id']);
                }
            });

            Schema::table('patients', function (Blueprint $table) {
                $dropColumns = [];

                foreach (
                    [
                        'patient_type_id',
                        'immunization_record',
                        'insurance_provider_id',
                        'insurance_policy_number',
                        'primary_care_physician_name',
                        'primary_care_physician_contact',
                        'primary_care_physician_email',
                    ] as $column
                ) {
                    if (Schema::hasColumn('patients', $column)) {
                        $dropColumns[] = $column;
                    }
                }

                if (! empty($dropColumns)) {
                    $table->dropColumn($dropColumns);
                }
            });
        }

        if (Schema::hasTable('doctors')) {
            Schema::table('doctors', function (Blueprint $table) {
                if ($this->indexExists('doctors', 'idx_doctors_prc_license_number')) {
                    $table->dropIndex('idx_doctors_prc_license_number');
                }
            });

            Schema::table('doctors', function (Blueprint $table) {
                $dropColumns = [];

                foreach (['prc_license_number', 'ptr_number', 's2_license_number', 'consultation_hours'] as $column) {
                    if (Schema::hasColumn('doctors', $column)) {
                        $dropColumns[] = $column;
                    }
                }

                if (! empty($dropColumns)) {
                    $table->dropColumn($dropColumns);
                }
            });
        }

        if (Schema::hasTable('users')) {
            Schema::table('users', function (Blueprint $table) {
                if ($this->indexExists('users', 'idx_users_employee_id')) {
                    $table->dropIndex('idx_users_employee_id');
                }
                if ($this->indexExists('users', 'idx_users_university_id_number')) {
                    $table->dropIndex('idx_users_university_id_number');
                }
            });

            Schema::table('users', function (Blueprint $table) {
                $dropColumns = [];

                foreach (['university_id_number', 'employee_id', 'nationality_citizenship'] as $column) {
                    if (Schema::hasColumn('users', $column)) {
                        $dropColumns[] = $column;
                    }
                }

                if (! empty($dropColumns)) {
                    $table->dropColumn($dropColumns);
                }
            });
        }

        if (Schema::hasTable('staff_profiles')) {
            Schema::dropIfExists('staff_profiles');
        }

        Schema::dropIfExists('clinic_stations');
        Schema::dropIfExists('staff_designations');
        Schema::dropIfExists('insurance_providers');
        Schema::dropIfExists('patient_types');
    }
};
