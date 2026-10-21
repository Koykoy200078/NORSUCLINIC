<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('queue_ticket_sequences', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->date('ticket_date')->primary();
            $table->unsignedInteger('last_number')->default(0);
        });
        Schema::table('patient_queues', function (Blueprint $table) {
            $table->date('ticket_date')->nullable();
            $table->unsignedInteger('ticket_number')->nullable();
            $table->unique(['ticket_date', 'ticket_number']);
        });
        $numbers = [];
        foreach (DB::table('patient_queues')->orderBy('created_at')->orderBy('id')->cursor() as $entry) {
            $date = substr($entry->created_at ?? now()->toDateTimeString(), 0, 10);
            $numbers[$date] = ($numbers[$date] ?? 0) + 1;
            DB::table('patient_queues')->where('id', $entry->id)->update(['ticket_date' => $date, 'ticket_number' => $numbers[$date]]);
        }
        foreach ($numbers as $date => $number) {
            DB::table('queue_ticket_sequences')->insert(['ticket_date' => $date, 'last_number' => $number]);
        }
        if (! DB::table('settings')->where('key', 'queue_board_secret')->exists()) {
            DB::table('settings')->insert(['key' => 'queue_board_secret', 'value' => '', 'created_at' => now(), 'updated_at' => now()]);
        }
    }

    public function down(): void
    {
        Schema::table('patient_queues', function (Blueprint $table) {
            $table->dropUnique(['ticket_date', 'ticket_number']);
            $table->dropColumn(['ticket_date', 'ticket_number']);
        });
        Schema::dropIfExists('queue_ticket_sequences');
        DB::table('settings')->where('key', 'queue_board_secret')->delete();
    }
};
