<?php

namespace Tests\Feature\Regression;

use App\Models\DocumentIssuance;
use App\Models\Notification;
use App\Models\PatientQueue;
use App\Services\ClinicNotificationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\BuildsClinicData;
use Tests\TestCase;

class NotificationCenterTest extends TestCase
{
    use RefreshDatabase, BuildsClinicData;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedAccessControl();
        $this->travelTo(now()->startOfSecond());
    }

    public function test_stock_alerts_are_per_user_deduplicated_and_resolve_without_rearming_read_alerts(): void
    {
        $admin = $this->makeAdmin();
        $staff = $this->makeNurse();
        $medicine = $this->makeMedicine('Alert medicine', ['minimum_stock_alert' => 5, 'baseline_quantity' => 10]);
        $batch = $this->stockIn($medicine, 3, today()->toDateString());
        $service = app(ClinicNotificationService::class);
        $service->refresh('inventory');
        $this->assertSame(2, Notification::where('user_id', $admin->id)->count());
        $alert = Notification::where('user_id', $admin->id)->where('type', 'stock_low')->firstOrFail();
        $alert->update(['read_at' => now()]);
        $service->refresh('inventory');
        $this->assertSame(4, Notification::count());
        $this->assertNotNull($alert->fresh()->read_at);
        $this->actingAs($admin)->get(route('notifications.index', ['filter' => 'all']))->assertOk()
            ->assertSee(route('medicines.index'));
        $this->travel(1)->days();
        $service->refresh('inventory');
        $this->assertDatabaseHas('notifications', ['user_id' => $staff->id, 'type' => 'stock_expired', 'resolved_at' => null]);
        $this->assertNotNull(Notification::where('user_id', $admin->id)->where('type', 'stock_expiring')->first()->resolved_at);
        app(\App\Services\MedicineInventoryService::class)->recordBatchCount($batch->id, 0, $admin->id, 'Removed expired stock', $medicine, true);
        $service->refresh('inventory');
        $this->assertSame(0, Notification::where('type', 'stock_expired')->whereNull('resolved_at')->count());
    }

    public function test_doctors_receive_queue_and_incomplete_alerts_without_patient_details_and_completion_resolves_them(): void
    {
        $doctor = $this->makeDoctor();
        $staff = $this->makeNurse();
        $patient = $this->makePatient();
        $queue = PatientQueue::create(['patient_id' => $patient->id, 'added_by' => $staff->id, 'status' => 'waiting']);
        $visit = DocumentIssuance::create(['user_id' => $patient->user_id, 'document_creator_id' => $staff->id,
            'name' => 'PRIVATE NAME', 'age' => 22, 'gender' => 'Male', 'address' => 'Synthetic', 'requested_at' => today(), 'document_type' => 'consultation_form']);
        $service = app(ClinicNotificationService::class);
        $service->refresh();
        $this->assertSame(['consultation_incomplete', 'queue_new'], Notification::where('user_id', $doctor->id)->orderBy('type')->pluck('type')->all());
        $this->assertSame(0, Notification::where('user_id', $staff->id)->count());
        $this->actingAs($doctor)->get(route('doctors.notifications.index'))->assertOk()->assertDontSee('PRIVATE NAME')->assertSee('Consultation needs assessment');
        $queue->update(['status' => 'completed']);
        $visit->update(['assessment' => '0', 'plan' => 'Done']);
        $service->refresh();
        $this->assertSame(0, Notification::whereNull('resolved_at')->count());
    }

    public function test_read_actions_are_owner_scoped_and_center_filters_unread_and_history(): void
    {
        $a = $this->makeAdmin();
        $b = $this->makeAdmin();
        $mine = Notification::create(['user_id' => $a->id, 'title' => 'My alert']);
        $other = Notification::create(['user_id' => $b->id, 'title' => 'Other alert']);
        $this->actingAs($a)->postJson(route('notifications.read', $other))->assertForbidden();
        $this->get(route('notifications.index'))->assertOk()->assertSee('My alert')->assertDontSee('Other alert');
        $this->postJson(route('notifications.read', $mine))->assertOk();
        $this->get(route('notifications.index', ['filter' => 'unread']))->assertDontSee('My alert');
        $this->get(route('notifications.index', ['filter' => 'all']))->assertSee('My alert');
        $this->postJson(route('notifications.read.all'))->assertOk();
        $this->assertNull($other->fresh()->read_at);
    }

    public function test_revoked_module_hides_alerts_and_mark_all_cannot_touch_another_user(): void
    {
        $staff = $this->makeNurse();
        $doctor = $this->makeDoctor();
        $this->makeMedicine('Restricted alert', ['minimum_stock_alert' => 5, 'baseline_quantity' => 10]);
        app(ClinicNotificationService::class)->refresh('inventory');
        $this->assertGreaterThan(0, Notification::where('user_id', $staff->id)->count());
        $staff->roles->first()->revokePermissionTo('manage_medicines');
        app(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();
        $this->actingAs($staff->fresh())->get(route('staff.notifications.index'))->assertOk()->assertDontSee('Restricted alert');
        $this->postJson(route('notifications.read.all'))->assertOk();
        $this->assertSame(0, Notification::where('user_id', $doctor->id)->whereNotNull('read_at')->count());
    }

    public function test_a_refresh_resolves_the_alerts_of_a_user_who_lost_the_module_and_creates_none_for_them(): void
    {
        $admin = $this->makeAdmin();
        $staff = $this->makeNurse();
        $this->makeMedicine('Guarded alert', ['minimum_stock_alert' => 5, 'baseline_quantity' => 10]);
        $service = app(ClinicNotificationService::class);
        $service->refresh('inventory');
        $this->assertGreaterThan(0, Notification::where('user_id', $staff->id)->whereNull('resolved_at')->count(), 'precondition: with the module the nurse is alerted');

        $staff->roles->first()->revokePermissionTo('manage_medicines');
        app(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();
        $service->refresh('inventory');

        $this->assertSame(0, Notification::where('user_id', $staff->id)->whereNull('resolved_at')->count(), 'the alerts they already had are resolved, not left open');
        $this->assertGreaterThan(0, Notification::where('user_id', $admin->id)->whereNull('resolved_at')->count(), 'users who still have the module keep theirs');

        $service->refresh('inventory');
        $this->assertSame(0, Notification::where('user_id', $staff->id)->whereNull('resolved_at')->count(), 'and a later refresh creates none for them');
    }

    /**
     * The stock and queue conditions do not depend on who is alerted, only on whether they may see the module; the refresh
     * used to rebuild them (a full medicine + batch scan, the day's queue) once per recipient, after every write.
     */
    public function test_a_refresh_reads_the_stock_and_the_queue_once_however_many_users_it_serves(): void
    {
        $this->makeAdmin();
        $this->makeNurse();
        $this->makeNurse();
        $this->makeDoctor();
        $this->makeDoctor();
        $medicine = $this->makeMedicine('Counted', ['minimum_stock_alert' => 5, 'baseline_quantity' => 10]);
        $this->stockIn($medicine, 3, today()->addMonth()->toDateString());
        $patient = $this->makePatient();
        PatientQueue::create(['patient_id' => $patient->id, 'added_by' => $this->makeNurse()->id, 'status' => 'waiting']);
        $reads = ['batches' => 0, 'queue' => 0];
        \Illuminate\Support\Facades\DB::listen(function ($query) use (&$reads) {
            $reads['batches'] += str_contains($query->sql, 'from `medicine_batches`') ? 1 : 0;
            $reads['queue'] += str_contains($query->sql, 'from `patient_queues`') ? 1 : 0;
        });

        app(ClinicNotificationService::class)->refresh();

        $this->assertSame(1, $reads['batches'], 'stock is scanned once, not once per recipient');
        $this->assertSame(1, $reads['queue'], 'the day\'s queue is read once, not once per doctor');
        $this->assertGreaterThan(0, Notification::where('type', 'stock_low')->count(), 'and everyone with the module still gets the alerts');
        $this->assertSame(2, Notification::where('type', 'queue_new')->count(), 'both doctors, and only doctors, get the queue alert');
    }

    public function test_scheduled_refresh_is_idempotent_and_counter_is_not_capped_at_twenty(): void
    {
        $admin = $this->makeAdmin();
        for ($i = 0; $i < 23; $i++) {
            Notification::create(['user_id' => $admin->id, 'title' => 'Alert '.$i]);
        }
        $this->actingAs($admin)->get(route('notifications.index'))->assertOk()->assertSee('data-unread-count="23"', false);
        $this->artisan('notifications:refresh')->assertExitCode(0);
        $this->artisan('notifications:refresh')->assertExitCode(0);
        $this->assertSame(23, Notification::count());
    }
}
