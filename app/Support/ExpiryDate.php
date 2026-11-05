<?php

namespace App\Support;

use Carbon\Carbon;

/**
 * What an expiry date typed in the stock-in form means.
 *
 * The form has a "Month Only" switch, so an expiry arrives as `2027-02` or as `2027-02-10`. A medicine whose box says
 * "02/2027" is good until the END of February, so a month-only expiry is the LAST day of that month (the owner's decision of
 * 2026-11-05; the form's validation already compared it that way). Stock entered before that decision was stored on the 1st
 * of the month, so a lookup by a month-only value has to look for both days (lookupDates).
 */
final class ExpiryDate
{
    private const MONTH_ONLY = '/^\d{4}-(0[1-9]|1[0-2])$/';

    public static function isMonthOnly(?string $value): bool
    {
        return $value !== null && preg_match(self::MONTH_ONLY, trim($value)) === 1;
    }

    /** The calendar date (Y-m-d) a typed expiry stands for. */
    public static function toDateString(string $value): string
    {
        $value = trim($value);

        if (self::isMonthOnly($value)) {
            return Carbon::createFromFormat('!Y-m', $value)->endOfMonth()->toDateString();
        }

        return Carbon::parse($value)->toDateString();
    }

    /**
     * Every date a stored batch may carry for a typed expiry, the one the rule gives first. A month-only value also matches the
     * 1st of its month, where stock-ins entered before the decision were stored.
     *
     * @return array<int, string>
     */
    public static function lookupDates(string $value): array
    {
        $dates = [self::toDateString($value)];

        if (self::isMonthOnly($value)) {
            $dates[] = Carbon::createFromFormat('!Y-m', trim($value))->toDateString();
        }

        return $dates;
    }
}
