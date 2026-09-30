<?php

namespace App\Support;

/**
 * Philippine phone numbers (+63) - the only country the clinic works with.
 *
 * Storage convention used across the application:
 *   users.contact / settings.contact_no  -> national significant number, digits only
 *                                           (mobile "9171234567", landline "354221234")
 *   users.country_code / settings.country_code -> "63"
 *   users.emergency_contact_no           -> "+639171234567" (no separate country column)
 *
 * Accepted input forms: "+63 917 123 4567", "63917...", "0917 123 4567", "917...", "0063917...",
 * "(035) 422-1234", and the legacy "+63" . "09171234567" join that older screens produced.
 */
final class PhilippinePhone
{
    public const COUNTRY_CODE = '63';

    public const DEFAULT_COUNTRY_ISO = 'ph';

    /**
     * Reduce whatever was typed to the national significant number, or null when it is not a
     * valid Philippine mobile / landline number.
     *
     * Mobile:   10 digits starting with 9 (9XX XXX XXXX).
     * Landline: area code + subscriber number, 8-10 digits starting 2-8 (Metro Manila "2" + 8 digits,
     *           provincial two-digit area code + 7 digits, and the older 7-digit Manila numbers).
     */
    public static function national(mixed $value): ?string
    {
        if (! is_string($value) && ! is_int($value)) {
            return null;
        }

        $digits = preg_replace('/\D+/', '', (string) $value) ?? '';

        if ($digits === '') {
            return null;
        }

        if (str_starts_with($digits, '0063')) {
            $digits = substr($digits, 4);
        } elseif (str_starts_with($digits, '63') && strlen($digits) >= 10) {
            $digits = substr($digits, 2);
        }

        // Trunk prefix ("0917..." / "035...") - also covers the old "+63" . "0917..." double prefix.
        $digits = preg_replace('/^0/', '', $digits);

        if (preg_match('/^9\d{9}$/', $digits)) {
            return $digits;
        }

        if (preg_match('/^[2-8]\d{7,9}$/', $digits)) {
            return $digits;
        }

        return null;
    }

    public static function isValid(mixed $value): bool
    {
        return self::national($value) !== null;
    }

    public static function isMobile(?string $national): bool
    {
        return $national !== null && (bool) preg_match('/^9\d{9}$/', $national);
    }

    /**
     * "+639171234567" or null.
     */
    public static function e164(mixed $value): ?string
    {
        $national = self::national($value);

        return $national === null ? null : '+' . self::COUNTRY_CODE . $national;
    }

    /**
     * Human readable form: "+63 917 123 4567" (mobile) / "+63 35 4221234" (landline).
     */
    public static function format(mixed $value): ?string
    {
        $national = self::national($value);

        if ($national === null) {
            return null;
        }

        if (self::isMobile($national)) {
            return '+' . self::COUNTRY_CODE . ' ' . substr($national, 0, 3) . ' ' . substr($national, 3, 3) . ' ' . substr($national, 6);
        }

        $areaLength = $national[0] === '2' ? 1 : 2;

        return '+' . self::COUNTRY_CODE . ' ' . substr($national, 0, $areaLength) . ' ' . substr($national, $areaLength);
    }
}
