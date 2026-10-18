<?php

namespace Tests\Feature\Regression;

use App\Livewire\Dashboard;
use App\Livewire\DoctorDashboardTable;
use App\Models\Patient;
use App\Models\PatientQueue;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\Concerns\BuildsClinicData;
use Tests\TestCase;

/**
 * Plan Phase 5, row 5.3 (Queue, Pass A): the number of people in today's queue is the same wherever it is shown (the
 * sidebar badge, both dashboards and the queue screens) and follows every add / call / complete / cancel at once, and
 * the queue pages do not query once per row. (Who may do what, the atomic calls and the consultation attachment are in
 * PatientQueueTest and QueueConsultationAttachmentTest.)
 */
class QueuePassATest extends TestCase
{
    use RefreshDatabase;
    use BuildsClinicData;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedAccessControl();
    }

    private function entry(Patient $patient, User $addedBy, array $overrides = []): PatientQueue
    {
        $entry = (new PatientQueue())->forceFill(array_merge([
            'patient_id' => $patient->id, 'added_by' => $addedBy->id, 'status' => PatientQueue::STATUS_WAITING,
        ], $overrides));
        $entry->save();

        return $entry;
    }

    /** The number the sidebar shows next to "Queue" on the page of $user, or null when there is no badge. */
    private function badge(User $user): ?int
    {
        $html = $this->actingAs($user)->get(route($user->hasRole('doctor') ? 'doctors.dashboard' : 'staff.dashboard'))->assertOk()->getContent();

        return preg_match('/aside-menu-title">Queue<\/span>\s*(?:<span class="ms-auto d-flex" data-live-badge="queue">\s*)?(?:<span class="badge[^>]*>\s*(?:<i[^>]*><\/i>\s*)?(\d*)\s*<\/span>)?/s', $html, $m) && ($m[1] ?? '') !== '' ? (int) $m[1] : null;
    }

    public function test_the_sidebar_badge_follows_every_change_of_the_queue_at_once(): void
    {
        $nurse = $this->makeNurse();
        $doctor = $this->makeDoctor();
        $first = $this->entry($this->makePatient(), $nurse);
        $this->entry($this->makePatient(), $nurse);

        $this->assertSame(2, $this->badge($nurse), 'two people are waiting');

        $this->entry($this->makePatient(), $nurse);
        $this->assertSame(3, $this->badge($nurse), 'a third one was added a moment ago');

        $this->actingAs($doctor)->post(route('doctors.patient-queue.complete', $first))->assertRedirect();
        $this->assertSame(2, $this->badge($nurse), 'one was completed');

        $second = PatientQueue::where('status', PatientQueue::STATUS_WAITING)->first();
        $this->actingAs($nurse)->put(route('staff.patient-queue.update', $second), ['status' => PatientQueue::STATUS_CANCELLED])->assertRedirect();
        $this->assertSame(1, $this->badge($nurse), 'one was cancelled');
    }

    public function test_the_badge_the_dashboards_and_the_queue_screen_count_the_same_people(): void
    {
        $nurse = $this->makeNurse();
        $doctor = $this->makeDoctor();

        $this->entry($this->makePatient(), $nurse);                                              // waiting
        $this->entry($this->makePatient(), $nurse, ['status' => PatientQueue::STATUS_IN_PROGRESS]); // being seen
        $this->entry($this->makePatient(), $nurse, ['status' => PatientQueue::STATUS_COMPLETED]);   // done
        $this->entry($this->makePatient(), $nurse, ['status' => PatientQueue::STATUS_CANCELLED]);   // cancelled

        $yesterday = $this->entry($this->makePatient(), $nurse);                                 // left over from yesterday
        $yesterday->forceFill(['created_at' => now()->subDay()])->saveQuietly();

        $orphan = $this->makePatient();                                                          // the account was archived on its own
        $this->entry($orphan, $nurse);
        $orphan->user->delete();

        // The counts are read first: the queue screens close yesterday's leftovers when they open, and the counts
        // themselves must not need that to be right.
        $this->assertSame(2, $this->badge($nurse), 'sidebar badge: the waiting and the in-progress patient of today');
        $this->actingAs($this->makeAdmin());
        $this->assertSame(2, (int) \Livewire\Livewire::test(Dashboard::class)->get('todayQueueCount'), 'admin dashboard');
        $this->actingAs($doctor);
        $this->assertSame(2, (int) \Livewire\Livewire::test(DoctorDashboardTable::class)->get('patientQueuesCount'), 'doctor dashboard');

        $this->assertSame(2, count($this->actingAs($nurse)->get(route('staff.patient-queue.index'))->assertOk()->viewData('queues')), 'staff queue screen');
        $this->assertSame(2, count($this->actingAs($doctor)->get(route('doctors.patient-queue.index'))->assertOk()->viewData('queues')), 'doctor queue screen');
    }

    public function test_the_queue_pages_do_not_query_once_per_entry(): void
    {
        $nurse = $this->makeNurse();
        $doctor = $this->makeDoctor();

        $add = fn () => $this->entry($this->makePatient(), $nurse, ['is_priority' => (bool) random_int(0, 1)]);

        $queries = function (User $user, string $route) {
            $this->actingAs($user)->get(route($route))->assertOk(); // warms the permission and settings caches
            DB::flushQueryLog();
            DB::enableQueryLog();
            $this->actingAs($user)->get(route($route))->assertOk();
            $count = count(DB::getQueryLog());
            DB::disableQueryLog();

            return $count;
        };

        foreach (range(1, 3) as $i) {
            $add();
        }
        $few = [
            'staff.patient-queue.index' => $queries($nurse, 'staff.patient-queue.index'),
            'staff.patient-queue.refresh' => $queries($nurse, 'staff.patient-queue.refresh'),
            'doctors.patient-queue.index' => $queries($doctor, 'doctors.patient-queue.index'),
            'doctors.patient-queue.refresh' => $queries($doctor, 'doctors.patient-queue.refresh'),
        ];
        foreach (range(4, 12) as $i) {
            $add();
        }

        foreach ($few as $route => $count) {
            $guard = str_starts_with($route, 'doctors') ? $doctor : $nurse;
            $this->assertSame($count, $queries($guard, $route), "{$route}: the number of queries must not grow with the number of people in the queue");
        }
    }
}
