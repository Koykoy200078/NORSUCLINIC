<?php

namespace Tests\Unit;

use App\Support\ExpiryDate;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * The owner's decision (2026-11-05): a month-only expiry typed in the stock-in form ("2027-02", the "Month Only" switch)
 * means the LAST day of that month, the way the packaging reads, and not the first.
 */
class ExpiryDateTest extends TestCase
{
    /** @return array<string, array{string, string}> */
    public static function typed(): array
    {
        return [
            'a month-only expiry is the last day' => ['2027-02', '2027-02-28'],
            'in a leap year' => ['2028-02', '2028-02-29'],
            'a 30-day month' => ['2027-04', '2027-04-30'],
            'a 31-day month' => ['2027-12', '2027-12-31'],
            'spaces around it are ignored' => ['  2027-06 ', '2027-06-30'],
            'a full date stays exactly as typed' => ['2027-02-10', '2027-02-10'],
            'the "never expires" date stays' => ['2099-12-31', '2099-12-31'],
            'a date with a time loses the time' => ['2027-02-10 14:30:00', '2027-02-10'],
        ];
    }

    #[DataProvider('typed')]
    public function test_the_date_a_typed_expiry_means(string $typed, string $expected): void
    {
        $this->assertSame($expected, ExpiryDate::toDateString($typed));
    }

    public function test_only_year_and_month_counts_as_month_only(): void
    {
        $this->assertTrue(ExpiryDate::isMonthOnly('2027-02'));
        $this->assertTrue(ExpiryDate::isMonthOnly(' 2027-12 '));
        $this->assertFalse(ExpiryDate::isMonthOnly('2027-02-10'));
        $this->assertFalse(ExpiryDate::isMonthOnly('2027-13'));
        $this->assertFalse(ExpiryDate::isMonthOnly(''));
        $this->assertFalse(ExpiryDate::isMonthOnly(null));
    }

    public function test_stock_entered_before_the_decision_sits_on_the_first_so_both_days_are_looked_up(): void
    {
        $this->assertSame(['2027-02-28', '2027-02-01'], ExpiryDate::lookupDates('2027-02'));
        $this->assertSame(['2027-02-10'], ExpiryDate::lookupDates('2027-02-10'));
    }
}
