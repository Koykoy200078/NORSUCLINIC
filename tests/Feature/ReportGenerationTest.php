<?php

namespace Tests\Feature;

use App\Livewire\ReportGeneration;
use App\Models\ActivityLog;
use App\Models\User;
use App\Models\Patient;
use App\Models\Category;
use App\Models\Generic;
use App\Models\Medicine;
use App\Models\PatientQueue;
use App\Models\DocumentIssuance;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;
use Spatie\Permission\Models\Role;

class ReportGenerationTest extends TestCase
{
    use RefreshDatabase;

    protected $admin;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create necessary roles
        Role::create(['name' => 'clinic_admin', 'guard_name' => 'web']);
        Role::create(['name' => 'staff', 'guard_name' => 'web']);
        Role::create(['name' => 'doctor', 'guard_name' => 'web']);

        // Create an admin user
        $this->admin = User::create([
            'first_name' => 'Admin',
            'last_name' => 'User',
            'email' => 'admin@norsuclinic.com',
            'password' => bcrypt('password'),
            'type' => User::ADMIN,
            'status' => 1,
        ]);
        $this->admin->assignRole('clinic_admin');
    }

    /** @test */
    public function it_can_render_the_component()
    {
        $this->actingAs($this->admin);

        Livewire::test(ReportGeneration::class)
            ->assertStatus(200)
            ->assertSee('Report Generation');
    }

    /** @test */
    public function it_can_switch_tabs()
    {
        $this->actingAs($this->admin);

        Livewire::test(ReportGeneration::class)
            ->set('tab', 'logs')
            ->assertSee('Activity Logs')
            ->call('setTab', 'visits')
            ->assertSet('tab', 'visits')
            ->assertSee('Patient Visits')
            ->call('setTab', 'inventory')
            ->assertSet('tab', 'inventory')
            ->assertSee('Medicine Inventory')
            ->call('setTab', 'dispensing')
            ->assertSet('tab', 'dispensing')
            ->assertSee('Dispensing Reports')
            ->call('setTab', 'appointments')
            ->assertSet('tab', 'appointments')
            ->assertSee('Schedules');
    }

    /** @test */
    public function it_can_filter_logs_by_search()
    {
        $this->actingAs($this->admin);

        ActivityLog::create([
            'user_name' => 'John Doe',
            'action' => 'login',
            'description' => 'User John logged in',
            'user_type' => 'admin',
            'date' => now(),
        ]);

        ActivityLog::create([
            'user_name' => 'Jane Smith',
            'action' => 'logout',
            'description' => 'User Jane logged out',
            'user_type' => 'staff',
            'date' => now(),
        ]);

        Livewire::test(ReportGeneration::class)
            ->set('tab', 'logs')
            ->set('search', 'John')
            ->assertSee('John Doe')
            ->assertDontSee('Jane Smith');
    }

    /** @test */
    public function it_can_filter_logs_by_user_type()
    {
        $this->actingAs($this->admin);

        ActivityLog::create([
            'user_name' => 'Admin User',
            'action' => 'login',
            'description' => 'Admin activity',
            'user_type' => 'admin',
            'date' => now(),
        ]);

        ActivityLog::create([
            'user_name' => 'Staff User',
            'action' => 'login',
            'description' => 'Staff activity',
            'user_type' => 'staff',
            'date' => now(),
        ]);

        Livewire::test(ReportGeneration::class)
            ->set('tab', 'logs')
            ->set('user_type', 'admin')
            ->assertSee('Admin User')
            ->assertDontSee('Staff User');
    }

    /** @test */
    public function it_can_reset_filters()
    {
        $this->actingAs($this->admin);

        Livewire::test(ReportGeneration::class)
            ->set('search', 'something')
            ->set('user_type', 'staff')
            ->set('action', 'login')
            ->call('resetFilters')
            ->assertSet('search', '')
            ->assertSet('user_type', 'all')
            ->assertSet('action', 'all');
    }

    /** @test */
    public function it_can_filter_visits_by_search()
    {
        $this->actingAs($this->admin);

        DocumentIssuance::create([
            'name' => 'Visit Patient A',
            'document_type' => 'consultation_form',
            'document_creator_id' => $this->admin->id,
            'user_id' => $this->admin->id, // Just link to existing user
            'age' => 20,
            'gender' => 'Male',
            'address' => 'N/A',
        ]);

        DocumentIssuance::create([
            'name' => 'Other Record',
            'document_type' => 'consultation_form',
            'document_creator_id' => $this->admin->id,
            'user_id' => $this->admin->id,
            'age' => 20,
            'gender' => 'Male',
            'address' => 'N/A',
        ]);

        Livewire::test(ReportGeneration::class)
            ->set('tab', 'visits')
            ->set('search', 'Visit')
            ->assertSee('Visit Patient A')
            ->assertDontSee('Other Record');
    }

    /** @test */
    public function it_can_filter_inventory_by_low_stock()
    {
        $this->actingAs($this->admin);

        $cat = Category::create(['name' => 'General', 'is_active' => 1]);
        $gen = Generic::create(['name' => 'Paracetamol']);

        Medicine::create([
            'name' => 'Low Stock Pill',
            'category' => 'General',
            'available_quantity' => 5,
            'minimum_stock_alert' => 10,
            'category_id' => $cat->id,
            'generic_id' => $gen->id,
            'salt_composition' => 'test',
        ]);

        Medicine::create([
            'name' => 'Healthy Pill',
            'category' => 'General',
            'available_quantity' => 50,
            'minimum_stock_alert' => 10,
            'category_id' => $cat->id,
            'generic_id' => $gen->id,
            'salt_composition' => 'test',
        ]);

        Livewire::test(ReportGeneration::class)
            ->set('tab', 'inventory')
            ->set('status', 'low_stock')
            ->assertSee('Low Stock Pill')
            ->assertDontSee('Healthy Pill');
    }

    /** @test */
    public function it_can_filter_appointments_by_date()
    {
        $this->actingAs($this->admin);

        $patientUser = User::create([
            'first_name' => 'Patient',
            'last_name' => 'One',
            'email' => 'patient@test.com',
            'password' => bcrypt('password'),
            'type' => User::PATIENT,
        ]);
        $p = Patient::create(['user_id' => $patientUser->id, 'patient_unique_id' => 'P001']);

        PatientQueue::create([
            'patient_id' => $p->id,
            'scheduled_at' => '2026-05-01 10:00:00',
            'added_by' => $this->admin->id,
            'status' => PatientQueue::STATUS_WAITING,
        ]);

        PatientQueue::create([
            'patient_id' => $p->id,
            'scheduled_at' => '2026-06-01 10:00:00',
            'added_by' => $this->admin->id,
            'status' => PatientQueue::STATUS_WAITING,
        ]);

        Livewire::test(ReportGeneration::class)
            ->set('tab', 'appointments')
            ->set('date_from', '2026-05-01')
            ->set('date_to', '2026-05-15')
            ->assertSee('May 01, 2026')
            ->assertDontSee('Jun 01, 2026');
    }

    /** @test */
    public function it_denies_access_to_unauthenticated_users()
    {
        $this->get('/admin/activity-logs')
            ->assertRedirect('/login');
    }
}
