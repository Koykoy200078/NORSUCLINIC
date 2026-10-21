<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class QueueTicketService
{
    public function allocate(string $date): int
    {
        return DB::transaction(function () use ($date): int {
            DB::table('queue_ticket_sequences')->insertOrIgnore(['ticket_date' => $date, 'last_number' => 0]);
            $sequence = DB::table('queue_ticket_sequences')->where('ticket_date', $date)->lockForUpdate()->first();
            $number = (int) $sequence->last_number + 1;
            DB::table('queue_ticket_sequences')->where('ticket_date', $date)->update(['last_number' => $number]);

            return $number;
        }, 5);
    }
}
