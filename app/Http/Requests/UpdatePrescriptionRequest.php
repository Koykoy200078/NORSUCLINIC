<?php

namespace App\Http\Requests;

/**
 * Editing a prescription is checked by the same rules as creating one (re-audit 2026-11-13, DRY: this class used to be a
 * line-for-line copy of CreatePrescriptionRequest).
 */
class UpdatePrescriptionRequest extends CreatePrescriptionRequest
{
}
