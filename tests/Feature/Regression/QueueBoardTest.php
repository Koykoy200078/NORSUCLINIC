<?php

namespace Tests\Feature\Regression;

use App\Events\QueueBoardChanged;
use App\Models\PatientQueue;
use App\Services\QueueBoardService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Tests\Concerns\BuildsClinicData;
use Tests\TestCase;

class QueueBoardTest extends TestCase
{
    use RefreshDatabase, BuildsClinicData;

    private \App\Models\User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedAccessControl();
        $this->admin = $this->makeAdmin();
        $this->travelTo(now()->startOfSecond());
    }

    private function entry(array $attributes = []): PatientQueue
    {
        return tap((new PatientQueue())->forceFill($attributes + ['patient_id' => $this->makePatient()->id,
            'added_by' => $this->admin->id, 'status' => 'waiting']), fn ($entry) => $entry->save());
    }

    private function rotate(): string
    {
        $this->actingAs($this->admin)->post(route('queue-board.rotate'))->assertRedirect(route('queue-board.settings'));

        return app(QueueBoardService::class)->token();
    }

    public function test_tickets_stay_stable_across_priority_call_completion_and_deletion_and_restart_each_day(): void
    {
        $first = $this->entry(['ticket_number' => 900]);
        $second = $this->entry(['is_priority' => true]);
        $this->assertSame([1, 2], [$first->ticket_number, $second->ticket_number]);
        $first->update(['status' => 'in_progress', 'called_at' => now()]);
        $this->assertSame(2, $second->fresh()->queue_number);
        $first->update(['status' => 'completed']);
        $first->delete();
        $this->assertSame(3, $this->entry()->ticket_number);
        $this->travel(1)->days();
        $tomorrow = $this->entry();
        $this->assertSame(1, $tomorrow->ticket_number);
        $this->assertSame(1, $tomorrow->queue_number);
        $this->assertSame(today()->toDateString(), $tomorrow->ticket_date->toDateString());
        $this->assertSame(2, $second->fresh()->ticket_number);
    }

    public function test_failed_transaction_does_not_consume_a_ticket_or_publish_a_board_update(): void
    {
        $this->rotate();
        Event::fake([QueueBoardChanged::class]);
        try {
            DB::transaction(function () {
                $this->entry();
                throw new \RuntimeException('Rollback fixture');
            });
        } catch (\RuntimeException $e) {
            $this->assertSame('Rollback fixture', $e->getMessage());
        }
        Event::assertNotDispatched(QueueBoardChanged::class);
        $this->assertSame(1, $this->entry()->ticket_number);
    }

    public function test_public_state_contains_only_today_live_numbers_safe_rooms_and_chime_keys(): void
    {
        $waiting = $this->entry(['notes' => 'PRIVATE COMPLAINT', 'room_number' => 'PRIVATE NAME']);
        $priority = $this->entry(['is_priority' => true, 'room_number' => 'Room 12']);
        $serving = $this->entry(['status' => 'in_progress', 'called_at' => now(), 'room_number' => '203A']);
        $old = $this->entry(['created_at' => today()->subDay()]);
        $this->entry(['status' => 'cancelled']);
        $archived = $this->entry();
        $archived->patient->user->delete();
        $token = $this->rotate();
        auth()->logout();
        $response = $this->getJson(route('queue-board.state', $token))->assertOk();
        $data = $response->json();
        $this->assertSame([$priority->ticket_number, $waiting->ticket_number], array_column($data['next'], 'number'));
        $this->assertSame('12', $data['next'][0]['room']);
        $this->assertSame('Clinic room', $data['next'][1]['room']);
        $this->assertSame($serving->ticket_number, $data['serving'][0]['number']);
        $this->assertSame('203A', $data['serving'][0]['room']);
        $this->assertSame(['number', 'room', 'call'], array_keys($data['serving'][0]));
        foreach (['PRIVATE', 'Juan', 'patient_id', 'notes', 'assessment', 'user_id', 'email', 'contact'] as $private) {
            $response->assertDontSee($private);
        }
        $this->assertSame(today()->subDay()->toDateString(), $old->ticket_date->toDateString());
        $this->assertSame('waiting', $old->fresh()->status, 'Public reads must not close or change clinical rows.');
        $this->assertStringContainsString('no-store', $response->headers->get('Cache-Control'));
        $this->assertSame('no-referrer', $response->headers->get('Referrer-Policy'));
        $this->get(route('queue-board.show', $token))->assertOk()->assertDontSee('Juan')->assertDontSee('PRIVATE');
    }

    public function test_link_rotation_revokes_old_http_access_and_changes_the_unguessable_public_channel(): void
    {
        $old = $this->rotate();
        $channel = app(QueueBoardService::class)->channel();
        Event::fake([QueueBoardChanged::class]);
        $new = $this->rotate();
        $this->assertNotSame($old, $new);
        $this->assertNotSame($channel, app(QueueBoardService::class)->channel());
        $this->get(route('queue-board.show', $old))->assertNotFound();
        $this->getJson(route('queue-board.state', $old))->assertNotFound();
        $this->get(route('queue-board.show', str_repeat('x', 64)))->assertNotFound();
        $this->getJson(route('queue-board.state', $new))->assertOk();
        Event::assertDispatched(QueueBoardChanged::class, fn ($event) => $event->broadcastWith() === ['revoked' => true]);
        $this->assertStringNotContainsString($new, DB::table('settings')->where('key', 'queue_board_secret')->value('value'));
    }

    public function test_only_admin_can_manage_the_link_and_ordinary_queue_screens_show_the_same_tickets(): void
    {
        $this->entry();
        $second = $this->entry(['is_priority' => true]);
        $this->actingAs($this->admin)->get(route('queue-board.settings'))->assertOk();
        $this->get(route('patient-queue.index'))->assertOk()->assertSee('Ticket');
        foreach ([$this->makeNurse(), $this->makeDoctor()] as $user) {
            $this->actingAs($user)->get(route('queue-board.settings'))->assertForbidden();
            $this->post(route('queue-board.rotate'))->assertForbidden();
        }
        $this->assertSame(2, $second->fresh()->queue_number);
    }

    public function test_board_event_payload_is_explicit_and_has_no_patient_information(): void
    {
        $this->rotate();
        $this->entry(['status' => 'in_progress', 'called_at' => now(), 'notes' => 'PRIVATE']);
        $service = app(QueueBoardService::class);
        $event = new QueueBoardChanged($service->channel());
        $this->assertInstanceOf(\Illuminate\Broadcasting\Channel::class, $event->broadcastOn()[0]);
        $this->assertSame(['changed' => true], $event->broadcastWith());
        $this->assertStringNotContainsString('PRIVATE', json_encode($event->broadcastWith()));
        $this->assertSame('QueueBoardChanged', $event->broadcastAs());
    }

    public function test_existing_ticket_cannot_be_renumbered(): void
    {
        $entry = $this->entry();
        $this->expectException(\LogicException::class);
        $entry->forceFill(['ticket_number' => 99])->save();
    }
}
