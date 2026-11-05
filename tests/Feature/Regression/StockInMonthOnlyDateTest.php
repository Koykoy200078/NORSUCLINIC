<?php

namespace Tests\Feature\Regression;

use App\Models\MedicineBatch;
use App\Models\PurchasedMedicine;
use App\Services\MedicineInventoryService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\Concerns\BuildsClinicData;
use Tests\TestCase;

/**
 * The stock-in form takes a date as YYYY-MM or YYYY-MM-DD (its "Month Only" switch).
 *
 * 1. A month-only date was read with "today's day of the month", so on the 29th, 30th and 31st a month that is shorter
 *    (February always; April, June, September and November on the 31st) spilled into the next month and a perfectly good
 *    date was refused as "Invalid expiry date format". It happened on the clinic's current code too; found in the
 *    verification pass after the Laravel 12 upgrade.
 * 2. The owner decided (2026-11-05) that a month-only expiry means the LAST day of that month: the stored batch date is
 *    that day, on create and on edit, and taking a stock-in line back finds the batch by it. Stock entered before the
 *    decision was stored on the 1st: it is still found.
 */
class StockInMonthOnlyDateTest extends TestCase
{
    use RefreshDatabase;
    use BuildsClinicData;

    /** @return array<string, array{string, string, string}> the day "today" is, the month typed as the expiry, the date kept */
    public static function days(): array
    {
        return [
            'february typed on the 31st' => ['2026-01-31', '2027-02', '2027-02-28'],
            'february typed on the 29th' => ['2026-05-29', '2028-02', '2028-02-29'],
            'february typed on the 30th' => ['2026-04-30', '2027-02', '2027-02-28'],
            'april typed on the 31st' => ['2026-03-31', '2027-04', '2027-04-30'],
            'a 31-day month on the 30th' => ['2026-04-30', '2027-12', '2027-12-31'],
            'a normal day' => ['2026-03-10', '2027-02', '2027-02-28'],
        ];
    }

    #[DataProvider('days')]
    public function test_a_month_only_expiry_is_accepted_and_kept_as_the_last_day_of_that_month(string $today, string $typed, string $lastDay): void
    {
        $this->seedAccessControl();
        $this->travelTo(Carbon::parse($today . ' 09:00:00'));
        $medicine = $this->makeMedicine('Monthonly');

        $this->actingAs($this->makeAdmin())->post(route('stock-in.store'), [
            'medicine' => [$medicine->id], 'quantity' => [5], 'dosage' => ['500mg'],
            'manufacturing_date' => ['2026-01'], 'expiry_date' => [$typed],
        ])->assertSessionHasNoErrors();

        $kept = MedicineBatch::query()->where('medicine_id', $medicine->id)->value('expiration_date');
        $this->assertNotNull($kept, 'the stock-in was recorded');
        $this->assertSame($lastDay, $kept->toDateString(), 'a month-only expiry means the last day of that month');
    }

    public function test_a_full_expiry_date_is_kept_exactly_as_typed(): void
    {
        $this->seedAccessControl();
        $medicine = $this->makeMedicine('Fulldate');

        $this->actingAs($this->makeAdmin())->post(route('stock-in.store'), [
            'medicine' => [$medicine->id], 'quantity' => [5], 'dosage' => ['500mg'],
            'manufacturing_date' => ['2026-01-05'], 'expiry_date' => ['2027-02-10'],
        ])->assertSessionHasNoErrors();

        $this->assertSame('2027-02-10', MedicineBatch::query()->where('medicine_id', $medicine->id)->value('expiration_date')->toDateString());
    }

    public function test_editing_a_month_only_stock_in_moves_the_quantity_and_keeps_the_last_day(): void
    {
        $this->seedAccessControl();
        $admin = $this->makeAdmin();
        $medicine = $this->makeMedicine('Editmonth');
        $payload = fn (int $quantity, array $extra = []) => array_merge([
            'medicine' => [$medicine->id], 'quantity' => [$quantity], 'dosage' => ['500mg'],
            'manufacturing_date' => ['2026-01'], 'expiry_date' => ['2027-02'],
        ], $extra);

        $this->actingAs($admin)->post(route('stock-in.store'), $payload(5))->assertSessionHasNoErrors();
        $line = PurchasedMedicine::firstOrFail();
        $stockInId = $line->medicine_availabilities_id;

        $this->put(route('stock-in.update', $stockInId), $payload(8, ['purchased_medicine_id' => [$line->id]]))
            ->assertSessionHasNoErrors()->assertSessionMissing('error');

        $batches = MedicineBatch::query()->where('medicine_id', $medicine->id)->get();
        $this->assertCount(1, $batches, 'the same expiry is the same batch: no second batch appears');
        $this->assertSame(8, (int) $batches->first()->quantity);
        $this->assertSame('2027-02-28', $batches->first()->expiration_date->toDateString());
    }

    public function test_writing_the_same_day_in_full_is_not_a_change_of_expiry(): void
    {
        $this->seedAccessControl();
        $admin = $this->makeAdmin();
        $medicine = $this->makeMedicine('Sameday');
        $payload = fn (string $expiry) => [
            'medicine' => [$medicine->id], 'quantity' => [5], 'dosage' => ['500mg'],
            'manufacturing_date' => ['2026-01'], 'expiry_date' => [$expiry],
        ];

        $this->actingAs($admin)->post(route('stock-in.store'), $payload('2027-02'))->assertSessionHasNoErrors();
        $line = PurchasedMedicine::firstOrFail();

        // "2027-02" and "2027-02-28" are the same expiry: nothing is taken back and booked in again
        $this->put(route('stock-in.update', $line->medicine_availabilities_id), $payload('2027-02-28') + ['purchased_medicine_id' => [$line->id]])
            ->assertSessionHasNoErrors()->assertSessionMissing('error');

        $this->assertSame(1, MedicineBatch::query()->where('medicine_id', $medicine->id)->count(), 'no second batch');
        $this->assertSame(5, (int) MedicineBatch::query()->where('medicine_id', $medicine->id)->value('quantity'));
    }

    public function test_more_stock_for_a_batch_entered_on_the_1st_before_the_decision_is_added_to_that_batch(): void
    {
        $this->seedAccessControl();
        $medicine = $this->makeMedicine('Oldbatch');
        $old = $this->stockIn($medicine, 10, '2027-02-01');   // entered by month only, before the decision
        $service = app(MedicineInventoryService::class);

        // the same batch number with the same month typed again is the same expiry, not a different one
        $service->recordStockIn([
            'medicine_id' => $medicine->id, 'quantity' => 4, 'batch_number' => $old->batch_number,
            'expiration_date' => '2027-02', 'dosage' => '500mg',
        ]);

        $this->assertSame(1, MedicineBatch::query()->where('medicine_id', $medicine->id)->count(), 'no second batch');
        $this->assertSame(14, (int) $old->fresh()->quantity);
        $this->assertSame('2027-02-01', $old->fresh()->expiration_date->toDateString(), 'the units already on the shelf keep their date');
    }

    public function test_a_different_month_for_an_existing_batch_number_is_still_refused(): void
    {
        $this->seedAccessControl();
        $medicine = $this->makeMedicine('Othermonth');
        $old = $this->stockIn($medicine, 10, '2027-02-01');

        $this->expectException(\RuntimeException::class);
        app(MedicineInventoryService::class)->recordStockIn([
            'medicine_id' => $medicine->id, 'quantity' => 4, 'batch_number' => $old->batch_number,
            'expiration_date' => '2027-03', 'dosage' => '500mg',
        ]);
    }

    /** @return array<string, array{string}> where the batch sits */
    public static function whereTheBatchSits(): array
    {
        return [
            'on the last day (the rule now)' => ['2027-02-28'],
            'on the 1st (stock entered before the decision)' => ['2027-02-01'],
        ];
    }

    #[DataProvider('whereTheBatchSits')]
    public function test_taking_back_a_line_with_no_batch_link_finds_the_batch_of_that_month(string $storedExpiry): void
    {
        $this->seedAccessControl();
        $medicine = $this->makeMedicine('Takeback');
        $earlier = $this->stockIn($medicine, 10, '2026-12-15');   // the fallback (earliest first) would empty THIS one
        $target = $this->stockIn($medicine, 10, $storedExpiry);

        app(MedicineInventoryService::class)->reverseStockIn($medicine->id, 3, null, '500mg', '2027-02');

        $this->assertSame(7, (int) $target->fresh()->quantity, 'the batch of that month gave the units back');
        $this->assertSame(10, (int) $earlier->fresh()->quantity, 'no other batch was touched');
    }

    #[DataProvider('whereTheBatchSits')]
    public function test_restoring_stock_for_a_month_only_expiry_goes_back_to_the_batch_of_that_month(string $storedExpiry): void
    {
        $this->seedAccessControl();
        $medicine = $this->makeMedicine('Restoremonth');
        $target = $this->stockIn($medicine, 10, $storedExpiry);

        app(MedicineInventoryService::class)->restoreStock($medicine->id, 2, null, null, null, '500mg', '2027-02');

        $this->assertSame(12, (int) $target->fresh()->quantity);
        $this->assertSame(1, MedicineBatch::query()->where('medicine_id', $medicine->id)->count(), 'no separate "restore" batch was made');
    }
}
