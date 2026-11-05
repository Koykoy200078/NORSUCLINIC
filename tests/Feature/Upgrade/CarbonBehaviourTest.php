<?php

namespace Tests\Feature\Upgrade;

use App\Models\Medicine;
use App\Models\MedicineBatch;
use App\Models\PatientQueue;
use App\Services\QueueBoardService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\Concerns\BuildsClinicData;
use Tests\TestCase;

/**
 * Pins what the four live places that subtract dates show today (Laravel 10, Carbon 2). Carbon 3 (Laravel 12) makes
 * diffIn*() return SIGNED, FRACTIONAL numbers, so a call that was right for "days between" can silently change meaning;
 * these tests stay green on Laravel 10 and must stay green after the hop (docs/laravel-13-upgrade-plan.md, finding C3).
 *
 * U2 adds the other places a Carbon major can change what people read: the time of a backup file (createFromTimestamp
 * decides the time zone), the "5 minutes ago" texts, and the age counted from a birth date.
 */
class CarbonBehaviourTest extends TestCase
{
    use RefreshDatabase, BuildsClinicData;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedAccessControl();
        $this->travelTo(Carbon::parse('2026-10-30 09:30:00'));
    }

    public function test_the_waiting_area_board_refreshes_after_a_whole_number_of_seconds_until_midnight(): void
    {
        $this->travelTo(Carbon::parse('2026-10-30 18:00:00'));

        $refreshIn = app(QueueBoardService::class)->state()['refresh_in'];

        $this->assertIsInt($refreshIn);
        $this->assertSame(6 * 3600, $refreshIn);
    }

    public function test_the_medicine_modal_counts_days_to_expiry_as_whole_signed_days(): void
    {
        $admin = $this->makeAdmin();
        $medicine = $this->makeMedicine('Daystest');
        $this->stockIn($medicine, 5, '2026-11-09');
        $expired = $this->stockIn($medicine, 3, '2026-11-20', '250mg');
        MedicineBatch::whereKey($expired->id)->update(['expiration_date' => '2026-10-28']);

        $data = $this->actingAs($admin)->getJson(route('medicines.show.modal', $medicine))->assertOk()->json('data');

        $this->assertSame(10, collect($data['available_purchased_medicines'])->firstWhere('dosage', '500mg')['remaining_days']);
        $this->assertSame(-2, collect($data['expired_purchased_medicines'])->firstWhere('dosage', '250mg')['remaining_days']);
    }

    #[DataProvider('expiryBands')]
    public function test_the_inventory_expiry_badge_uses_the_same_day_bands(int $daysAway, string $expected): void
    {
        $medicine = $this->makeMedicine('Bandtest');
        $batch = $this->stockIn($medicine, 5, Carbon::today()->addDays(30)->toDateString());
        MedicineBatch::whereKey($batch->id)->update(['expiration_date' => Carbon::today()->addDays($daysAway)->toDateString()]);

        $row = Medicine::with('batches')->findOrFail($medicine->id);
        $html = view('medicines.templates.columns.expiration', ['row' => $row])->render();

        $this->assertStringContainsString($expected, $html);
        foreach (array_diff(['bg-danger', 'bg-warning', 'bg-success'], [$expected]) as $other) {
            $this->assertStringNotContainsString($other, $html);
        }
    }

    /**
     * An expired batch never reaches the badge (the earliest-expiry accessor only looks at batches that can still be
     * dispensed), so "expired" shows as no expiry data; today and the next 7 days are the red band.
     *
     * @return array<string, array{int, string}>
     */
    public static function expiryBands(): array
    {
        return [
            'expired yesterday' => [-1, 'No expiry data'],
            'expires today' => [0, 'bg-danger'],
            'in 7 days' => [7, 'bg-danger'],
            'in 8 days' => [8, 'bg-warning'],
            'in 30 days' => [30, 'bg-warning'],
            'in 31 days' => [31, 'bg-success'],
        ];
    }

    public function test_the_doctors_queue_shows_the_average_wait_as_positive_whole_minutes(): void
    {
        $doctor = $this->makeDoctor();
        foreach ([10, 20] as $minutes) {
            $queue = (new PatientQueue())->forceFill([
                'patient_id' => $this->makePatient()->id,
                'added_by' => $doctor->id,
                'status' => 'waiting',
                'is_priority' => false,
            ]);
            $queue->save();
            PatientQueue::whereKey($queue->id)->update(['created_at' => now()->subMinutes($minutes)]);
        }

        $html = $this->actingAs($doctor)->view('patient_queue.doctor_view_partial', [
            'queues' => PatientQueue::with('patient.user')->get(),
        ])->__toString();

        $this->assertStringContainsString('15 min', $html);
        $this->assertStringNotContainsString('-15 min', $html);
    }

    public function test_the_backups_screen_shows_the_time_of_each_file_as_philippine_time(): void
    {
        $admin = $this->makeAdmin();
        $directory = storage_path('app/backups');
        File::ensureDirectoryExists($directory);
        $probe = $directory . DIRECTORY_SEPARATOR . 'zz-u2-time-probe.sql';
        file_put_contents($probe, "-- probe
");
        touch($probe, Carbon::parse('2026-03-01 08:30:00', 'Asia/Manila')->getTimestamp());

        try {
            $this->actingAs($admin)->get(route('backups.index'))->assertOk()->assertSee('Sun, Mar 1, 2026 8:30 AM');
        } finally {
            @unlink($probe);
        }
    }

    #[DataProvider('relativeTimes')]
    public function test_relative_times_read_the_same_everywhere_they_are_shown(int $secondsAgo, string $expected): void
    {
        $this->assertSame($expected, now()->subSeconds($secondsAgo)->diffForHumans());
    }

    /** @return array<string, array{int, string}> the "x ago" texts of the notification bell, queue and detail pages */
    public static function relativeTimes(): array
    {
        return [
            '30 seconds' => [30, '30 seconds ago'],
            '5 minutes' => [5 * 60, '5 minutes ago'],
            '90 minutes' => [90 * 60, '1 hour ago'],
            '3 hours' => [3 * 3600, '3 hours ago'],
            '2 days' => [2 * 86400, '2 days ago'],
            '20 days' => [20 * 86400, '2 weeks ago'],
            '40 days' => [40 * 86400, '1 month ago'],
            '400 days' => [400 * 86400, '1 year ago'],
        ];
    }

    #[DataProvider('ages')]
    public function test_age_counts_completed_years_from_the_birth_date(string $dob, int $years): void
    {
        // the expression of the prescription page and print (PrescriptionController, show_fields_with_medicine)
        $this->assertSame($years, Carbon::parse($dob)->diff(Carbon::now())->y);
    }

    /** @return array<string, array{string, int}> "now" is 2026-10-30 09:30 */
    public static function ages(): array
    {
        return [
            'birthday long ago' => ['2003-05-01', 23],
            'birthday tomorrow' => ['2000-10-31', 25],
            'birthday today' => ['2000-10-30', 26],
            'born yesterday' => ['2026-10-29', 0],
        ];
    }
}
