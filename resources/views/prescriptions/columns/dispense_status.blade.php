@php
$status = $row->status ?? \App\Models\Prescription::DISPENSE_STATUS_PENDING;
if ($status === 1 || $status === true) {
$status = \App\Models\Prescription::DISPENSE_STATUS_PENDING;
}
if ($status === 0 || $status === false) {
$status = \App\Models\Prescription::DISPENSE_STATUS_CANCELLED;
}
$badgeClass = match ($status) {
\App\Models\Prescription::DISPENSE_STATUS_DISPENSED => 'bg-success',
\App\Models\Prescription::DISPENSE_STATUS_CANCELLED => 'bg-danger',
default => 'bg-warning text-dark',
};
@endphp

<span class="badge {{ $badgeClass }}">{{ ucfirst($status) }}</span>