<?php

namespace Tests\Feature\Regression;

use App\Models\MedicineBatch;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\Concerns\BuildsClinicData;
use Tests\TestCase;

/**
 * The stock-in form takes a date as YYYY-MM or YYYY-MM-DD (its "Month Only" switch). A month-only date was read with
 * "today's day of the month", so on the 29th, 30th and 31st a month that is shorter (February always; April, June,
 * September and November on the 31st) spilled into the next month and a perfectly good date was refused as "Invalid expiry
 * date format". It happened on the clinic's current code too; found in the verification pass after the Laravel 12 upgrade.
 *
 * Not decided here: what day a month-only expiry is STORED as (today the first of the month, while the validation treats it
 * as the last): that is a question for the owner, so the test only asks that the typed month is the month that is kept.
 */
class StockInMonthOnlyDateTest extends TestCase
{
    use RefreshDatabase;
    use BuildsClinicData;

    /** @return array<string, array{string, string}> the day "today" is, and the month typed as the expiry */
    public static function days(): array
    {
        return [
            'february typed on the 31st' => ['2026-01-31', '2027-02'],
            'february typed on the 29th' => ['2026-05-29', '2028-02'],
            'february typed on the 30th' => ['2026-04-30', '2027-02'],
            'april typed on the 31st' => ['2026-03-31', '2027-04'],
            'a 31-day month on the 30th' => ['2026-04-30', '2027-12'],
            'a normal day' => ['2026-03-10', '2027-02'],
        ];
    }

    #[DataProvider('days')]
    public function test_a_month_only_expiry_is_accepted_on_every_day_of_the_month(string $today, string $typed): void
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
        $this->assertSame($typed, $kept->format('Y-m'), 'the month that was typed is the month that is kept');
    }
}
