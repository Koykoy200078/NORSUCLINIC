<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;

class QueueBoardChanged implements ShouldBroadcastNow
{
    public function __construct(private string $channel, private bool $revoked = false)
    {
    }

    public function broadcastOn(): array
    {
        return [new Channel($this->channel)];
    }

    public function broadcastAs(): string
    {
        return 'QueueBoardChanged';
    }

    public function broadcastWith(): array
    {
        return $this->revoked ? ['revoked' => true] : ['changed' => true];
    }
}
