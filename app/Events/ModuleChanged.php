<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;

/**
 * "Something in this module changed": the one event of the real-time updates (plan Phase 7). It carries the module name and
 * the time and NOTHING else, never a patient, a name or a record's content: every screen asks the server again for what it
 * shows, through the same permission checks as any other request. It goes out on the private channel of the module, which
 * only users who can use the module may listen to (routes/channels.php), and only after the change was committed
 * (App\Support\Realtime).
 */
class ModuleChanged implements ShouldBroadcastNow
{
    use Dispatchable;
    use InteractsWithSockets;

    public function __construct(public string $module, public ?int $at = null)
    {
        $this->at ??= now()->getTimestamp();
    }

    /** @return array<int, PrivateChannel> */
    public function broadcastOn(): array
    {
        return [new PrivateChannel('module.' . $this->module)];
    }

    public function broadcastAs(): string
    {
        return 'ModuleChanged';
    }

    /** @return array{module: string, at: int} */
    public function broadcastWith(): array
    {
        return ['module' => $this->module, 'at' => $this->at];
    }
}
