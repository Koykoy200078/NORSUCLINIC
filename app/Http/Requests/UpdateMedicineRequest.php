<?php

namespace App\Http\Requests;

/**
 * Editing a medicine: the rules of CreateMedicineRequest, with the medicine itself left out of the SKU unique check and the
 * edit form's wording for the batch number (re-audit 2026-11-13, DRY: this class used to repeat every rule).
 */
class UpdateMedicineRequest extends CreateMedicineRequest
{
    protected function editedMedicineId(): mixed
    {
        $medicine = $this->route('medicine');

        return is_object($medicine) ? $medicine->id : $medicine;
    }

    protected function batchNumberRequiredMessage(): string
    {
        return 'Batch number is required when stock quantity is provided.';
    }
}
