<?php

namespace Tests\Feature\Regression;

use App\Models\Medicine;
use App\Models\MedicineBatch;
use App\Models\PurchasedMedicine;
use App\Services\MedicineInventoryService;
use App\Services\MonthOnlyExpiryCorrection;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Tests\Concerns\BuildsClinicData;
use Tests\TestCase;

/**
 * `inventory:month-only-expiry`: stock entered with the stock-in form's "Month Only" switch before the owner's decision of
 * 2026-11-05 (a month-only expiry is the LAST day of the month) sits on the 1st of the month. The command moves exactly
 * those batches to the last day, and only when the stock-in line still says it was month-only. A dry run unless --apply.
 */
class MonthOnlyExpiryCommandTest extends TestCase
{
    use RefreshDatabase;
    use BuildsClinicData;

    private ?\App\Models\User $admin = null;

    /**
     * What the form stored before the decision: the line says "2027-02", the batch sits on the 1st of that month.
     */
    private function oldMonthOnlyBatch(Medicine $medicine, string $month, int $quantity = 10): MedicineBatch
    {
        $this->admin ??= $this->makeAdmin();
        $this->actingAs($this->admin)->post(route('stock-in.store'), [
            'medicine' => [$medicine->id], 'quantity' => [$quantity], 'dosage' => ['500mg'],
            'manufacturing_date' => ['2020-01'], 'expiry_date' => [$month],
        ])->assertSessionHasNoErrors();

        $batch = MedicineBatch::query()->where('medicine_id', $medicine->id)->orderByDesc('id')->firstOrFail();
        $batch->forceFill(['expiration_date' => $month . '-01'])->save();
        app(MedicineInventoryService::class)->syncMedicineTotals($medicine->id);

        return $batch->fresh();
    }

    private function expiry(MedicineBatch $batch): string
    {
        return $batch->fresh()->expiration_date->toDateString();
    }

    public function test_a_dry_run_lists_the_batch_and_changes_nothing(): void
    {
        $this->seedAccessControl();
        $batch = $this->oldMonthOnlyBatch($this->makeMedicine('Dryrun'), '2027-02');
        $ledgerRows = DB::table('medicine_transactions')->count();

        $this->artisan('inventory:month-only-expiry')
            ->expectsOutputToContain('Dry run: 1 batch(es) (10 unit(s)) would move')
            ->assertSuccessful();

        $this->assertSame('2027-02-01', $this->expiry($batch));
        $this->assertSame($ledgerRows, DB::table('medicine_transactions')->count());
    }

    public function test_apply_moves_the_batch_to_the_last_day_and_nothing_else_changes(): void
    {
        $this->seedAccessControl();
        $medicine = $this->makeMedicine('Applyone');
        $feb = $this->oldMonthOnlyBatch($medicine, '2027-02', 10);
        $leap = $this->oldMonthOnlyBatch($medicine, '2028-02', 6);
        $ledgerRows = DB::table('medicine_transactions')->count();

        $this->artisan('inventory:month-only-expiry', ['--apply' => true])
            ->expectsOutputToContain('Moved 2 batch(es)')
            ->assertSuccessful();

        $this->assertSame('2027-02-28', $this->expiry($feb));
        $this->assertSame('2028-02-29', $this->expiry($leap), 'a leap year');
        $this->assertSame([10, 6], [(int) $feb->fresh()->quantity, (int) $leap->fresh()->quantity], 'no quantity moves');
        $this->assertSame($ledgerRows, DB::table('medicine_transactions')->count(), 'no ledger row is written: the units did not move');
        $this->assertSame('2027-02', PurchasedMedicine::query()->where('batch_id', $feb->id)->value('expiry_date'), 'the line keeps what was typed');
        $this->assertSame(16, (int) $medicine->fresh()->quantity);
    }

    public function test_a_second_run_finds_nothing_left_to_do(): void
    {
        $this->seedAccessControl();
        $this->oldMonthOnlyBatch($this->makeMedicine('Twice'), '2027-02');

        $this->artisan('inventory:month-only-expiry', ['--apply' => true])->assertSuccessful();

        $this->artisan('inventory:month-only-expiry', ['--apply' => true])
            ->expectsOutputToContain('No batch needs correcting')
            ->assertSuccessful();
    }

    public function test_a_batch_whose_line_was_typed_as_a_full_date_is_left_alone(): void
    {
        $this->seedAccessControl();
        $medicine = $this->makeMedicine('Fullline');
        $this->actingAs($this->makeAdmin())->post(route('stock-in.store'), [
            'medicine' => [$medicine->id], 'quantity' => [5], 'dosage' => ['500mg'],
            'manufacturing_date' => ['2020-01-05'], 'expiry_date' => ['2027-02-01'],
        ])->assertSessionHasNoErrors();
        $batch = MedicineBatch::query()->where('medicine_id', $medicine->id)->firstOrFail();

        $this->artisan('inventory:month-only-expiry', ['--apply' => true])
            ->expectsOutputToContain('No batch needs correcting')
            ->assertSuccessful();

        $this->assertSame('2027-02-01', $this->expiry($batch), 'someone typed the 1st on purpose');
    }

    public function test_a_batch_with_no_stock_in_line_is_left_alone_and_counted(): void
    {
        $this->seedAccessControl();
        $batch = $this->stockIn($this->makeMedicine('Nolink'), 10, '2027-02-01');   // opening balance / imported: nobody knows how it was typed

        $this->artisan('inventory:month-only-expiry')
            ->expectsOutputToContain('1 batch(es) on the 1st of a month have no stock-in line')
            ->assertSuccessful();
        $this->artisan('inventory:month-only-expiry', ['--apply' => true])->assertSuccessful();

        $this->assertSame('2027-02-01', $this->expiry($batch));
    }

    public function test_a_batch_that_one_full_date_line_and_one_month_only_line_share_is_left_alone(): void
    {
        $this->seedAccessControl();
        $batch = $this->oldMonthOnlyBatch($this->makeMedicine('Mixed'), '2027-02');
        $line = PurchasedMedicine::query()->where('batch_id', $batch->id)->firstOrFail();
        PurchasedMedicine::create([
            'medicine_availabilities_id' => $line->medicine_availabilities_id, 'medicine_id' => $line->medicine_id, 'batch_id' => $batch->id,
            'dosage' => '500mg', 'manufacturing_date' => '2020-01-01', 'expiry_date' => '2027-02-01', 'quantity' => 1,
        ]);

        $this->artisan('inventory:month-only-expiry')
            ->expectsOutputToContain('1 batch(es) have lines that disagree')
            ->assertSuccessful();
        $this->artisan('inventory:month-only-expiry', ['--apply' => true])->assertSuccessful();

        $this->assertSame('2027-02-01', $this->expiry($batch));
    }

    public function test_a_batch_whose_line_names_another_month_is_left_alone(): void
    {
        $this->seedAccessControl();
        $batch = $this->oldMonthOnlyBatch($this->makeMedicine('Othermonth'), '2027-02');
        PurchasedMedicine::query()->where('batch_id', $batch->id)->update(['expiry_date' => '2027-03']);

        $this->artisan('inventory:month-only-expiry', ['--apply' => true])->assertSuccessful();

        $this->assertSame('2027-02-01', $this->expiry($batch));
    }

    public function test_a_batch_already_on_the_last_day_is_not_a_candidate(): void
    {
        $this->seedAccessControl();
        $medicine = $this->makeMedicine('Newrule');
        $this->actingAs($this->makeAdmin())->post(route('stock-in.store'), [
            'medicine' => [$medicine->id], 'quantity' => [5], 'dosage' => ['500mg'],
            'manufacturing_date' => ['2020-01'], 'expiry_date' => ['2027-02'],
        ])->assertSessionHasNoErrors();

        $this->assertSame([], app(MonthOnlyExpiryCorrection::class)->plan()['moves']);
    }

    public function test_a_batch_that_was_expired_on_the_1st_and_is_good_until_the_last_day_is_counted_as_usable_again(): void
    {
        $this->seedAccessControl();
        $this->travelTo(Carbon::parse('2026-11-05 09:00:00'));
        $medicine = $this->makeMedicine('Usable');
        $batch = $this->oldMonthOnlyBatch($medicine, '2026-11', 12);   // Nov 1 has passed: the 12 units are not available
        $this->assertSame(0, (int) $medicine->fresh()->available_quantity);

        $this->artisan('inventory:month-only-expiry')
            ->expectsOutputToContain('12 unit(s) now counted as expired would be usable again')
            ->assertSuccessful();
        $this->assertSame(0, (int) $medicine->fresh()->available_quantity, 'a dry run recalculates nothing');

        $this->artisan('inventory:month-only-expiry', ['--apply' => true])->assertSuccessful();

        $this->assertSame('2026-11-30', $this->expiry($batch));
        $this->assertSame(12, (int) $medicine->fresh()->available_quantity, 'the medicine counts the units again');
    }

    public function test_a_batch_that_stays_expired_does_not_become_available(): void
    {
        $this->seedAccessControl();
        $this->travelTo(Carbon::parse('2026-11-05 09:00:00'));
        $medicine = $this->makeMedicine('Stillold');
        $batch = $this->oldMonthOnlyBatch($medicine, '2026-09', 7);   // September ended on the 30th

        $this->artisan('inventory:month-only-expiry', ['--apply' => true])->assertSuccessful();

        $this->assertSame('2026-09-30', $this->expiry($batch));
        $this->assertSame(0, (int) $medicine->fresh()->available_quantity);
    }

    public function test_apply_drops_the_cached_expiring_count_and_logs_every_move(): void
    {
        $this->seedAccessControl();
        $batch = $this->oldMonthOnlyBatch($this->makeMedicine('Logged'), '2027-02');
        Cache::put('expiring_medicines_count', 99, 3600);

        // The clinic logs at LOG_LEVEL=warning (.env.example): a line below that level is never written, so the move must be
        // logged at warning or above. A real log file is read back (Log::spy() would see a line the clinic never keeps).
        $logFile = storage_path('logs/month-only-expiry-test-' . getmypid() . '.log');
        config([
            'logging.channels.clinic_level' => ['driver' => 'single', 'path' => $logFile, 'level' => 'warning'],
            'logging.default' => 'clinic_level',
        ]);

        try {
            $this->artisan('inventory:month-only-expiry', ['--apply' => true])->assertSuccessful();

            $this->assertFalse(Cache::has('expiring_medicines_count'), 'the menu badge is recalculated');
            $this->assertFileExists($logFile, 'the move was not written to the log at the clinic\'s log level');
            $lines = array_values(array_filter(file($logFile), fn (string $line) => str_contains($line, 'month-only expiry')));
            $this->assertCount(1, $lines, 'one log line per moved batch');
            $this->assertStringContainsString('"batch_id":' . $batch->id . ',', $lines[0]);
            $this->assertStringContainsString('"from":"2027-02-01"', $lines[0]);
            $this->assertStringContainsString('"to":"2027-02-28"', $lines[0]);
        } finally {
            app('log')->forgetChannel('clinic_level');
            @unlink($logFile);
        }
    }
}
