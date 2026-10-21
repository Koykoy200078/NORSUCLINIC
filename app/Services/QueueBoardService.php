<?php

namespace App\Services;

use App\Events\QueueBoardChanged;
use App\Models\PatientQueue;
use App\Models\Setting;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class QueueBoardService
{
    public function token(): ?string
    {
        // Read directly: rotating a link must revoke it immediately, across cached browser sessions.
        $encrypted = Setting::where('key', 'queue_board_secret')->value('value');
        if (! $encrypted) {
            return null;
        }
        try {
            return Crypt::decryptString($encrypted);
        } catch (\Throwable $e) {
            return null;
        }
    }

    public function channel(): ?string
    {
        $token = $this->token();

        return $token ? 'queue-board.'.hash('sha256', $token) : null;
    }

    public function rotate(): void
    {
        $oldChannel = DB::transaction(function (): ?string {
            $setting = Setting::where('key', 'queue_board_secret')->lockForUpdate()->firstOrFail();
            $old = $this->channel();
            $setting->update(['value' => Crypt::encryptString(Str::random(64))]);
            DB::afterCommit(fn () => SettingsService::clearCache());

            return $old;
        }, 5);
        if ($oldChannel) {
            DB::afterCommit(fn () => $this->broadcast($oldChannel, true));
        }
    }

    public function state(): array
    {
        $queues = PatientQueue::openToday()->whereNotNull('ticket_number')->orderByQueue()->orderBy('id')
            ->get(['patient_queues.ticket_number', 'patient_queues.room_number', 'patient_queues.called_at', 'patient_queues.status']);
        $row = function (PatientQueue $queue, bool $serving): array {
            $room = trim((string) $queue->room_number);
            $safe = preg_match('/^(?:room\s*)?([a-z]?[0-9]{1,4}[a-z]?(?:-[0-9]{1,3})?)$/i', $room, $match) === 1 ? strtoupper($match[1]) : 'Clinic room';
            $data = ['number' => (int) $queue->ticket_number, 'room' => $safe];
            if ($serving) {
                $data['call'] = hash('sha256', today()->toDateString().':'.$queue->ticket_number.':'.$queue->called_at?->toIso8601String());
            }

            return $data;
        };

        return ['day' => today()->toDateString(), 'refresh_in' => now()->diffInSeconds(today()->addDay()),
            'serving' => $queues->where('status', PatientQueue::STATUS_IN_PROGRESS)->map(fn ($q) => $row($q, true))->values()->all(),
            'next' => $queues->where('status', PatientQueue::STATUS_WAITING)->take(8)->map(fn ($q) => $row($q, false))->values()->all()];
    }

    public function publish(): void
    {
        $channel = $this->channel();
        if ($channel) {
            $this->broadcast($channel);
        }
    }

    private function broadcast(string $channel, bool $revoked = false): void
    {
        try {
            event(new QueueBoardChanged($channel, $revoked));
        } catch (\Throwable $e) {
            Log::warning('Queue board live update unavailable; browser fallback remains active.', ['error' => class_basename($e)]);
        }
    }
}
