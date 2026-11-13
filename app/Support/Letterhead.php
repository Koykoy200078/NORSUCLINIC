<?php

namespace App\Support;

/**
 * The printed letterhead of the clinic's documents (consultation, medical certificate, excuse slip: page, print and PDF).
 * One copy of the text since the re-audit of 2026-11-13 (DRY: nine views typed it out); the wording is still the owner's
 * open decision (docs/STATUS.md section 6, "Letterhead address line"), and changing it here changes every document.
 */
final class Letterhead
{
    public const UNIVERSITY = 'Negros Oriental State University';

    public const ADDRESS = 'University Medical Clinic, CNPAHS Bldg., Kagawasan Ave., Dumaguete City';

    public const PHONE = 'Tel #: 225-9400, then Local # 188, 09263829484';
}
