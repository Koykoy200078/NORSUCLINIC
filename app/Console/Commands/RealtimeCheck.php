<?php

namespace App\Console\Commands;

use App\Services\RealtimeDiagnostics;
use Illuminate\Console\Command;

class RealtimeCheck extends Command
{
    protected $signature = 'realtime:check';

    protected $description = 'Check the real-time updates (Reverb): settings, the running server, this PC\'s addresses and publishing. Read-only.';

    public function handle(RealtimeDiagnostics $diagnostics): int
    {
        $failed = false;
        foreach ($diagnostics->report() as $line) {
            if ($line['level'] === 'problem') {
                $failed = true;
            }
            $this->line(match ($line['level']) {
                'ok' => '[ok] '.$line['text'],
                'problem' => '[!!] '.$line['text'],
                default => '     '.$line['text'],
            });
        }

        return $failed ? self::FAILURE : self::SUCCESS;
    }
}
