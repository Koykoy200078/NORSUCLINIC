<?php

namespace Tests\Feature\Regression;

use App\Events\ModuleChanged;
use App\Models\ActivityLog;
use App\Models\PatientQueue;
use App\Models\Role;
use App\Support\Realtime;
use Illuminate\Broadcasting\BroadcastManager;
use Illuminate\Database\DatabaseTransactionsManager;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Log;
use Spatie\Permission\PermissionRegistrar;
use Tests\Concerns\BuildsClinicData;
use Tests\TestCase;

/**
 * Phase 7 (real-time updates with Laravel Reverb), server side: a notice per committed save, private channels that only the
 * users of a module may listen to, no patient data in the notice, and a stopped Reverb that never breaks a save.
 */
class RealtimeServerTest extends TestCase
{
    use RefreshDatabase;
    use BuildsClinicData;

    protected function setUp(): void
    {
        parent::setUp();
        // RefreshDatabase wraps the whole test in a transaction, so no commit ever reaches level 0 and the commit callbacks
        // would never run. This manager treats the test's own transaction as the outermost one (what production sees).
        $manager = new class extends DatabaseTransactionsManager {
            public function afterCommitCallbacksShouldBeExecuted($level)
            {
                return $level <= 1;
            }
        };
        $this->app->instance('db.transactions', $manager);
        DB::connection()->setTransactionManager($manager);

        $this->seedAccessControl();
        Cache::forget('realtime.down');
    }

    /** @return array<int, string> the modules a callback's writes notified, in order */
    private function modulesNotifiedBy(callable $work): array
    {
        Event::fake([ModuleChanged::class]);
        $work();

        return Event::dispatched(ModuleChanged::class)->map(fn (array $call) => $call[0]->module)->values()->all();
    }

    private function queueEntry(?int $patientId = null, ?int $nurseId = null): PatientQueue
    {
        return PatientQueue::create([
            'patient_id' => $patientId ?? $this->makePatient()->id, 'added_by' => $nurseId ?? $this->makeNurse()->id,
            'status' => PatientQueue::STATUS_WAITING, 'is_priority' => false,
        ]);
    }

    public function test_queue_alert_is_generated_only_after_commit_and_announced_without_patient_details(): void
    {
        $doctor = $this->makeDoctor();
        $patient = $this->makePatient();
        $nurse = $this->makeNurse();
        $modules = $this->modulesNotifiedBy(function () use ($doctor, $patient, $nurse) {
            DB::transaction(function () use ($doctor, $patient, $nurse) {
                $this->queueEntry($patient->id, $nurse->id);
                $this->assertSame(0, \App\Models\Notification::where('user_id', $doctor->id)->count());
            });
        });
        $this->app->terminate();
        $this->assertContains('queue', $modules);
        $this->assertSame(1, \App\Models\Notification::where('user_id', $doctor->id)->where('type', 'queue_new')->count());
        try {
            DB::transaction(function () use ($patient, $nurse) {
                $this->queueEntry($patient->id, $nurse->id);
                throw new \RuntimeException('Rollback fixture');
            });
        } catch (\RuntimeException $e) {
            $this->assertSame('Rollback fixture', $e->getMessage());
        }
        $this->assertSame(1, \App\Models\Notification::where('user_id', $doctor->id)->count());
    }

    // ------------------------------------------------------------------ a notice per committed save

    public function test_public_queue_board_notice_waits_for_commit_and_has_no_clinical_payload(): void
    {
        app(\App\Services\QueueBoardService::class)->rotate();
        $patient = $this->makePatient();
        $nurse = $this->makeNurse();
        Event::fake([ModuleChanged::class, \App\Events\QueueBoardChanged::class]);
        DB::transaction(function () use ($patient, $nurse) {
            $this->queueEntry($patient->id, $nurse->id);
            Event::assertNotDispatched(\App\Events\QueueBoardChanged::class);
        });
        Event::assertDispatched(\App\Events\QueueBoardChanged::class, fn ($event) => $event->broadcastWith() === ['changed' => true]);
    }

    public function test_a_save_notifies_the_modules_that_show_it_and_nothing_else(): void
    {
        $patient = $this->makePatient();
        $nurse = $this->makeNurse();

        $this->assertSame(['queue'], $this->modulesNotifiedBy(fn () => PatientQueue::create([
            'patient_id' => $patient->id, 'added_by' => $nurse->id, 'status' => PatientQueue::STATUS_WAITING, 'is_priority' => false,
        ])), 'a queue entry');

        $entry = PatientQueue::firstOrFail();
        $this->assertSame(['queue'], $this->modulesNotifiedBy(fn () => PatientQueue::whereKey($entry->id)->update(['status' => PatientQueue::STATUS_IN_PROGRESS])), 'a query-builder UPDATE counts too (the atomic "call next")');

        $this->assertSame(['consultations', 'certificates'], $this->modulesNotifiedBy(fn () => \App\Models\DocumentIssuance::create([
            'document_type' => 'consultation_form', 'document_creator_id' => $nurse->id, 'user_id' => $patient->user_id, 'name' => 'X', 'age' => 20, 'gender' => 'Male',
            'address' => 'D', 'requested_at' => now()->toDateString(),
        ])), 'a document');

        $this->assertSame(['inventory'], $this->modulesNotifiedBy(fn () => $this->makeMedicine('Zz Realtime')), 'a medicine');
        $this->assertSame(['notifications'], $this->modulesNotifiedBy(fn () => ActivityLog::create(['user_id' => null, 'user_name' => 'T', 'user_type' => 'staff', 'action' => 'test', 'description' => 'd', 'date' => now()->toDateString(), 'time' => '10:00:00'])), 'the activity log');

        // what is not clinic data changes nothing: the session, the cache and reads
        $this->assertSame([], $this->modulesNotifiedBy(function () {
            DB::table('sessions')->insert(['id' => 'x', 'payload' => '', 'last_activity' => time()]);
            DB::table('migrations')->count();
            PatientQueue::count();
        }));
    }

    public function test_one_notice_per_module_for_all_the_writes_of_a_transaction_and_none_for_a_rolled_back_one(): void
    {
        $patient = $this->makePatient();
        $nurse = $this->makeNurse();

        $this->assertSame(['queue'], $this->modulesNotifiedBy(function () use ($patient, $nurse) {
            DB::transaction(function () use ($patient, $nurse) {
                $this->queueEntry($patient->id, $nurse->id);
                $this->queueEntry($patient->id, $nurse->id);
                $this->queueEntry($patient->id, $nurse->id);
            });
        }), 'three writes to the queue in one transaction are one notice, sent when it commits');

        $this->assertSame([], $this->modulesNotifiedBy(function () use ($patient, $nurse) {
            try {
                DB::transaction(function () use ($patient, $nurse) {
                    $this->queueEntry($patient->id, $nurse->id);
                    throw new \RuntimeException('the save fails after the write');
                });
            } catch (\RuntimeException) {
            }
        }), 'a rolled-back save notifies nobody');

        // and the next real save after a rollback is still announced
        $this->assertSame(['queue'], $this->modulesNotifiedBy(fn () => $this->queueEntry($patient->id, $nurse->id)));
    }

    public function test_the_notice_carries_the_module_and_the_time_only_on_the_private_channel_of_the_module(): void
    {
        $event = new ModuleChanged('queue', 1700000000);

        $this->assertSame(['module' => 'queue', 'at' => 1700000000], $event->broadcastWith(), 'no patient, no name, no record');
        $this->assertSame('private-module.queue', $event->broadcastOn()[0]->name);
        $this->assertSame('ModuleChanged', $event->broadcastAs());
    }

    // ------------------------------------------------------------------ who may listen

    private function useReverbDriver(int $port = 8080): void
    {
        config([
            'broadcasting.default' => 'reverb',
            'broadcasting.connections.reverb.key' => 'test-key', 'broadcasting.connections.reverb.secret' => 'test-secret', 'broadcasting.connections.reverb.app_id' => '1',
            'broadcasting.connections.reverb.options.host' => '127.0.0.1', 'broadcasting.connections.reverb.options.port' => $port,
            'broadcasting.connections.reverb.options.scheme' => 'http', 'broadcasting.connections.reverb.options.useTLS' => false,
        ]);
        app(BroadcastManager::class)->purge('reverb');
        // the channels were registered on the test driver (null) when the application booted; register them on this one too
        require base_path('routes/channels.php');
    }

    private function authorise(string $channel): \Illuminate\Testing\TestResponse
    {
        return $this->postJson('/broadcasting/auth', ['channel_name' => 'private-' . $channel, 'socket_id' => '123.456']);
    }

    public function test_a_module_channel_is_for_the_users_of_that_module_only(): void
    {
        $this->useReverbDriver();
        $nurse = $this->makeNurse();
        $doctor = $this->makeDoctor();
        $admin = $this->makeAdmin();

        // a guest cannot
        $this->authorise('module.queue')->assertStatus(401);

        // a nurse with patients + documents, without medicines
        Role::findByName('staff')->syncPermissions(['manage_patients', 'manage_request_documents']);
        app(PermissionRegistrar::class)->forgetCachedPermissions();
        $this->actingAs($nurse);
        foreach (['queue', 'patients', 'consultations', 'certificates', 'prescriptions', 'lab_requests', 'notifications'] as $module) {
            $this->authorise('module.' . $module)->assertOk();
        }
        foreach (['inventory', 'dispensing', 'doctors', 'settings'] as $module) {
            $this->authorise('module.' . $module)->assertForbidden();
        }
        $this->authorise('module.not-a-module')->assertForbidden();
        // names the permission map knows, or that every staff account may use, but that have no channel
        foreach (['dashboard', 'reports', 'document_issuances', 'roles'] as $notAChannel) {
            $this->authorise('module.' . $notAChannel)->assertForbidden();
        }

        // the same nurse once the medicines permission is given
        Role::findByName('staff')->syncPermissions(['manage_patients', 'manage_request_documents', 'manage_medicines']);
        app(PermissionRegistrar::class)->forgetCachedPermissions();
        $this->authorise('module.inventory')->assertOk();

        // a doctor, and the administrator (settings only for the administrator)
        $this->actingAs($doctor);
        $this->authorise('module.queue')->assertOk();
        $this->authorise('module.settings')->assertForbidden();
        $this->actingAs($admin);
        $this->authorise('module.settings')->assertOk();
        $this->authorise('module.inventory')->assertOk();

        // the private channel of a user is that user's alone
        $this->authorise('user.' . $admin->id)->assertOk();
        $this->authorise('user.' . $nurse->id)->assertForbidden();
    }

    public function test_the_browser_is_told_only_the_modules_its_user_may_listen_to(): void
    {
        Role::findByName('staff')->syncPermissions(['manage_patients']);
        app(PermissionRegistrar::class)->forgetCachedPermissions();
        $nurse = $this->makeNurse();

        $modules = Realtime::modulesFor($nurse);

        $this->assertContains('queue', $modules);
        $this->assertContains('patients', $modules);
        $this->assertContains('notifications', $modules);
        $this->assertNotContains('inventory', $modules);
        $this->assertNotContains('consultations', $modules);
        $this->assertNotContains('settings', $modules);
    }

    // ------------------------------------------------------------------ what the pages are told

    public function test_a_page_tells_the_browser_the_modules_it_may_listen_to_and_the_ones_the_page_shows(): void
    {
        Role::findByName('staff')->syncPermissions(['manage_patients']);
        app(PermissionRegistrar::class)->forgetCachedPermissions();
        $this->actingAs($this->makeNurse());

        $html = $this->get(route('staff.patient-queue.index'))->assertOk()->getContent();

        $this->assertStringContainsString('id="live-config"', $html);
        $this->assertStringContainsString('data-page-modules="queue"', $html);
        $this->assertMatchesRegularExpression('/data-modules="(?:[^"]*,)?queue(?:,[^"]*)?"/', $html);
        $this->assertDoesNotMatchRegularExpression('/data-modules="(?:[^"]*,)?inventory(?:,[^"]*)?"/', $html, 'no Manage Medicines, no inventory channel');
        $this->assertStringContainsString('id="live-updates-badge"', $html);
        $this->assertStringContainsString('js/realtime.js', $html);
        $this->assertStringContainsString('data-live-badge="queue"', $html, 'the sidebar badge can be redrawn');
    }

    public function test_the_live_updates_badge_says_why_it_is_off_instead_of_only_that_it_is(): void
    {
        $this->actingAs($this->makeAdmin());

        config(['broadcasting.default' => 'null']);
        $switchedOff = $this->get(route('medicine-inventory.index'))->assertOk()->getContent();
        $this->assertMatchesRegularExpression('/id="live-updates-badge" title="[^"]*switched off on the server \(BROADCAST_DRIVER/', $switchedOff);
        $this->assertStringContainsString('data-enabled="0"', $switchedOff);

        config(['broadcasting.default' => 'reverb', 'broadcasting.connections.reverb.options.port' => 8099]);
        $unreachable = $this->get(route('medicine-inventory.index'))->assertOk()->getContent();
        $this->assertMatchesRegularExpression('/id="live-updates-badge" title="[^"]*cannot reach the real-time service[^"]*port 8099[^"]*Windows Firewall/', $unreachable);
        $this->assertStringContainsString('data-enabled="1"', $unreachable);
        $this->assertStringContainsString('data-port="8099"', $unreachable, 'the browser connects to the same port the title names');
    }

    public function test_the_pages_map_to_the_modules_they_show(): void
    {
        $admin = $this->makeAdmin();

        $this->assertSame(['queue'], Realtime::pageModules('staff/patient-queue', $admin));
        $this->assertSame(['queue'], Realtime::pageModules('doctors/patient-queue/refresh', $admin));
        $this->assertSame(['inventory'], Realtime::pageModules('admin/medicines', $admin));
        $this->assertContains('dispensing', Realtime::pageModules('admin/medicine-dispensing-management', $admin));
        $this->assertContains('notifications', Realtime::pageModules('admin/activity-logs', $admin));
        $this->assertSame([], Realtime::pageModules('profile', $admin), 'a page without live data refreshes for nothing');

        // only the modules the user may use
        Role::findByName('staff')->syncPermissions(['manage_patients']);
        app(PermissionRegistrar::class)->forgetCachedPermissions();
        $this->assertSame([], Realtime::pageModules('staff/medicines', $this->makeNurse()));
    }

    public function test_the_menu_alone_is_served_to_signed_in_users_for_the_badges(): void
    {
        $this->get(route('live.menu'))->assertRedirect();

        $this->actingAs($this->makeNurse());
        $this->get(route('live.menu'))->assertOk()->assertSee('data-live-badge="queue"', false);
    }

    public function test_no_page_polls_or_reloads_on_a_timer_any_more(): void
    {
        $offenders = [];
        $roots = [base_path('resources/views'), base_path('resources/assets/js')];
        foreach ($roots as $root) {
            foreach (new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($root, \FilesystemIterator::SKIP_DOTS)) as $file) {
                $path = str_replace('\\', '/', $file->getPathname());
                if (! preg_match('/\.(php|js)$/', $path) || str_contains($path, '/views/vendor/') || str_contains($path, '/assets/js/realtime/')) {
                    continue;
                }
                $code = file_get_contents($path);
                if (preg_match('/wire:poll|setInterval\s*\(/', $code) === 1) {
                    $offenders[] = substr($path, strlen(base_path()) + 1);
                }
            }
        }

        $this->assertSame([], $offenders, 'real-time notices replaced the timers (the only timer is the 60 s fallback in resources/assets/js/realtime)');
    }

    /**
     * A screen redraws when its module is announced. If merely OPENING a screen wrote to its own tables (a clean-up that
     * updates nothing, a "last seen" stamp ...) the announcement would come back and the screens would redraw each
     * other for ever. So reading a page may not announce anything.
     */
    public function test_opening_a_screen_announces_nothing(): void
    {
        $nurse = $this->makeNurse();

        $pages = [
            'admin' => ['/admin/dashboard', '/admin/patient-queue', '/admin/patient-queue/refresh', '/live/menu', '/admin/patients', '/admin/document-issuances',
                '/admin/prescriptions', '/admin/lab-requests', '/admin/medicines', '/admin/medicine-dispensing-management', '/admin/activity-logs'],
            'staff' => ['/staff/dashboard', '/staff/patient-queue', '/staff/patient-queue/refresh', '/live/menu', '/staff/medicines'],
            'doctors' => ['/doctors/dashboard', '/doctors/patient-queue', '/doctors/patient-queue/refresh', '/live/menu'],
        ];
        $users = ['admin' => $this->makeAdmin(), 'staff' => $nurse, 'doctors' => $this->makeDoctor()];

        foreach ($pages as $panel => $paths) {
            $this->actingAs($users[$panel]);
            foreach ($paths as $path) {
                $modules = $this->modulesNotifiedBy(fn () => $this->get($path)->assertSuccessful());
                $this->assertSame([], $modules, "opening {$path} as {$panel} announced a change");
            }
        }
    }

    // ------------------------------------------------------------------ a stopped Reverb never breaks a save

    public function test_a_save_still_succeeds_and_is_not_held_up_when_reverb_does_not_answer(): void
    {
        $this->useReverbDriver(1); // nothing listens on port 1
        Log::spy();
        $nurse = $this->makeNurse();
        $patient = $this->makePatient();
        $this->actingAs($nurse);

        $started = microtime(true);
        $response = $this->post(route('staff.patient-queue.store'), ['patient_id' => $patient->id]);
        $first = microtime(true) - $started;

        $this->assertSame(1, PatientQueue::count(), 'the entry was saved');
        $response->assertSessionHasNoErrors();
        $this->assertTrue(Realtime::isDown(), 'the failure is remembered');
        $this->assertLessThan(5.0, $first, 'a refused connection cannot hold the save');

        // while it is marked down, further saves do not even try (no second attempt, so no second warning)
        $this->queueEntry();
        $this->assertSame(2, PatientQueue::count());
        Log::shouldHaveReceived('warning')->withArgs(fn ($message) => str_contains($message, 'Real-time updates are unavailable'))->once();
    }
}
