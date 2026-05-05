<div class="d-flex float-start">
    <div class="ms-3">
        <select class="form-select form-select-solid" id="patientStatusFilter" wire:model.live="statusFilter">
            <option value="active">Active Patients</option>
            <option value="archived">Archived Patients</option>
        </select>
    </div>
    <div class="ms-3">
        <input type="text" class="form-control form-control-solid custom-width px-3 flatpickr-input"
            placeholder="{{ __('messages.common.pick_date_range') }}" id="patientDateFilter" />
    </div>
</div>