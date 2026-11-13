<?php

namespace App\Support;

/**
 * The blood types a user can have (`users.blood_type` stores the number). One list for the doctor, patient and profile
 * forms, their show pages and the validation (re-audit 2026-11-13, DRY: the Doctor and Patient models each had a copy).
 */
final class BloodType
{
    public const O_POSITIVE = 1;

    public const A_POSITIVE = 2;

    public const B_POSITIVE = 3;

    public const AB_POSITIVE = 4;

    public const O_NEGATIVE = 5;

    public const A_NEGATIVE = 6;

    public const B_NEGATIVE = 7;

    public const AB_NEGATIVE = 8;

    /** @var array<int, string> the stored number => the label shown */
    public const LABELS = [
        self::O_POSITIVE => 'O+',
        self::A_POSITIVE => 'A+',
        self::B_POSITIVE => 'B+',
        self::AB_POSITIVE => 'AB+',
        self::O_NEGATIVE => 'O-',
        self::A_NEGATIVE => 'A-',
        self::B_NEGATIVE => 'B-',
        self::AB_NEGATIVE => 'AB-',
    ];
}
