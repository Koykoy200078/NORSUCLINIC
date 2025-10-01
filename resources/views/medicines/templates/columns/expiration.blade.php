@php
$expiryDate = $row->earliest_expiry_date;
$isExpiring = false;
$isExpired = false;
$badgeClass = 'bg-light-success';
$textClass = 'text-success';

if ($expiryDate) {
$expiryCarbon = \Carbon\Carbon::parse($expiryDate);
$today = \Carbon\Carbon::now();
$oneMonthFromNow = \Carbon\Carbon::now()->addMonth();

if ($expiryCarbon->lt($today)) {
// Expired
$isExpired = true;
$badgeClass = 'bg-danger';
$textClass = 'text-white';
} elseif ($expiryCarbon->lte($oneMonthFromNow)) {
// Expiring within a month
$isExpiring = true;
$badgeClass = 'bg-warning';
$textClass = 'text-dark';
}
}
@endphp

<div class="d-flex align-items-center">
    @if($expiryDate)
    <span class="badge {{ $badgeClass }} {{ $textClass }}" style="font-size: 0.75rem; min-width: 80px;">
        {{ \Carbon\Carbon::parse($expiryDate)->format('M d, Y') }}
    </span>
    @if($isExpired)
    <i class="fas fa-exclamation-triangle text-danger ms-2" title="Medicine Expired!" style="font-size: 1.1rem;"></i>
    @elseif($isExpiring)
    <i class="fas fa-exclamation-circle text-warning ms-2" title="Expiring Within 30 Days!" style="font-size: 1.1rem;"></i>
    @else
    <i class="fas fa-check-circle text-success ms-2" title="Medicine is fresh" style="font-size: 1.1rem;"></i>
    @endif
    @else
    <span class="text-muted" style="font-size: 0.85rem;">
        <i class="fas fa-info-circle me-1"></i>No expiry data
    </span>
    @endif
</div>