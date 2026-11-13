<?php

namespace App\Models;

/**
 * The old name of StockOutView (the `used_medicines_view` database view), kept for the Used Medicine list and the patient
 * dashboard that still use it (re-audit 2026-11-13, DRY: it used to repeat the model; it is read-only, so gaining the
 * `dosage` attribute changes nothing that is shown).
 */
class UsedMedicineView extends StockOutView
{
}
