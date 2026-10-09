<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Generic;
use App\Models\Medicine;
use App\Models\Patient;
use App\Models\User;
use App\Models\Doctor;
use App\Models\PatientQueue;
use App\Models\Prescription;
use App\Models\PatientCase;
use App\Models\DocumentIssuance;
use App\Models\MedicineBatch;
use App\Models\MedicineTransaction;
use App\Livewire\ReportGeneration;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;
use Tests\TestCase;
use Carbon\Carbon;

class ReportCrudIntegrationTest extends TestCase
{
    use RefreshDatabase;
    use \Tests\Concerns\BuildsClinicData;

    protected $admin;
    protected $doctor;

    protected function setUp(): void
    {
        parent::setUp();

        // Create Admin
        $this->admin = User::create([
            'first_name' => 'Admin',
            'last_name' => 'User',
            'email' => 'admin@norsuclinic.com',
            'password' => Hash::make('123456'),
            'type' => User::ADMIN,
        ]);

        // Create Doctor
        $this->doctor = User::create([
            'first_name' => 'Doctor',
            'last_name' => 'User',
            'email' => 'doctor@norsuclinic.com',
            'password' => Hash::make('123456'),
            'type' => User::DOCTOR,
        ]);
        Doctor::create(['user_id' => $this->doctor->id]);
    }

    /** @test */
    public function it_verifies_patient_visits_reporting_after_crud()
    {
        $this->actingAs($this->admin);

        // 1. Create Patient
        $user = User::create([
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john@example.com',
            'type' => User::PATIENT,
            'university_id_number' => 'STU-001',
        ]);
        $patient = Patient::create([
            'user_id' => $user->id,
            'patient_unique_id' => 'STU-001',
        ]);

        // 2. Create Consultation (Visit) - ReportGeneration uses DocumentIssuance
        $visit = DocumentIssuance::create([
            'document_type' => 'consultation_form',
            'user_id' => $user->id,
            'document_creator_id' => $this->doctor->id,
            'name' => 'John Doe',
            'age' => 21,
            'gender' => 'Male',
            'address' => 'N/A',
            'complaints' => 'Fever and chills',
            'assessment' => 'Viral Infection',
        ]);

        // 3. Verify in Report
        Livewire::test(ReportGeneration::class)
            ->set('tab', 'visits')
            ->assertSee('John Doe')
            ->assertSee('Fever and chills');
    }

    /** @test */
    public function it_verifies_medicine_inventory_reporting_after_crud()
    {
        $this->actingAs($this->admin);

        // 1. Setup Dependencies
        $category = Category::create(['name' => 'Analgesic', 'is_active' => 1]);
        $generic = Generic::create(['name' => 'Paracetamol', 'is_active' => 1]);

        // 2. Create Medicine (Inventory CRUD)
        $medicine = Medicine::create([
            'name' => 'Biogesic',
            'category' => 'Analgesic',
            'category_id' => $category->id,
            'generic_id' => $generic->id,
            'quantity' => 100,
            'reorder_level' => 10,
        ]);

        // 3. Verify in Report
        Livewire::test(ReportGeneration::class)
            ->set('tab', 'inventory')
            ->assertSee('Biogesic')
            ->assertSee('100');
    }

    /** @test */
    public function it_verifies_dispensing_reports_after_crud()
    {
        $this->actingAs($this->admin);

        // 1. Setup Patient and Medicine
        $user = User::create(['first_name' => 'Jane', 'last_name' => 'Doe', 'type' => User::PATIENT]);
        $patient = Patient::create(['user_id' => $user->id, 'patient_unique_id' => 'STU-002']);
        $medicine = Medicine::create([
            'name' => 'Amoxicillin',
            'category' => 'Antibiotics',
            'quantity' => 50,
        ]);

        // 2. Record a dispense in the stock LEDGER. The Dispensing report reads medicine_transactions
        //    (the legacy used_medicines table is no longer written to by the application).
        $batch = MedicineBatch::create([
            'medicine_id' => $medicine->id,
            'batch_number' => 'TEST-BATCH-1',
            'dosage' => '500mg',
            'quantity' => 40,
            'expiration_date' => '2030-01-01',
            'date_received' => '2026-01-01',
        ]);
        MedicineTransaction::create([
            'batch_id' => $batch->id,
            'user_id' => $this->admin->id,
            'transaction_type' => MedicineTransaction::TYPE_DISPENSE,
            'quantity' => 10,
            'balance_after' => 40,
            'reference_type' => \App\Models\Prescription::class,
            'reference_id' => 1,
        ]);

        // 3. Verify in Report
        Livewire::test(ReportGeneration::class)
            ->set('tab', 'dispensing')
            ->assertSee('Amoxicillin')
            ->assertSee('TEST-BATCH-1');
    }

    /** @test */
    public function it_verifies_schedules_reporting_after_crud()
    {
        $this->actingAs($this->admin);

        // 1. Setup Patient
        $user = User::create(['first_name' => 'Bob', 'last_name' => 'Builder', 'type' => User::PATIENT]);
        $patient = Patient::create(['user_id' => $user->id, 'patient_unique_id' => 'STU-003']);

        // 2. Create Appointment/Queue
        $queue = PatientQueue::create([
            'patient_id' => $patient->id,
            'added_by' => $this->admin->id,
            'status' => PatientQueue::STATUS_WAITING,
            'scheduled_at' => Carbon::tomorrow(),
        ]);

        // 3. Verify in Report
        Livewire::test(ReportGeneration::class)
            ->set('tab', 'appointments')
            ->assertSee('Bob Builder');
    }

    /** @test */
    public function it_verifies_global_search_across_all_modules()
    {
        // The global search only returns what the signed-in role may use, so this administrator must be a real one
        // (the setUp account has no role at all).
        $this->seedAccessControl();
        $this->admin->assignRole('clinic_admin');
        $this->actingAs($this->admin->fresh());

        // 1. Create a unique target
        $user = User::create(['first_name' => 'Xylophone', 'last_name' => 'Player', 'type' => User::PATIENT]);
        $patient = Patient::create(['user_id' => $user->id, 'patient_unique_id' => 'XYLO-001']);

        // 2. Search globally
        Livewire::test(ReportGeneration::class)
            ->set('tab', 'global_search')
            ->set('search', 'Xylophone')
            ->assertSee('Xylophone Player')
            ->assertSee('XYLO-001');
    }
}
