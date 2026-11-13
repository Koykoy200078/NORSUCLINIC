<?php

namespace App\Http\Requests;

/**
 * Editing a staff account: the rules of CreateStaffRequest, with the account itself left out of the unique checks and the
 * password optional (re-audit 2026-11-13, DRY: this class used to repeat every rule).
 */
class UpdateStaffRequest extends CreateStaffRequest
{
    protected function editedUserId(): ?int
    {
        return (int) $this->route('staff')->id;
    }

    protected function passwordRule(): string
    {
        return 'nullable|same:password_confirmation|min:6';
    }
}
