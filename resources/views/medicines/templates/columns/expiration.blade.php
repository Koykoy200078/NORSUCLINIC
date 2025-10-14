@php
$availableQuantity = $row->available_quantity ?? 0;
$expiryDate = $row->earliest_expiry_date;
$isCritical = false;
$isExpiring = false;
$isFresh = false;
$badgeClass = 'bg-success';
$textClass = 'text-white';
$daysRemaining = null;

// Only show expiry info if there's available quantity
if ($availableQuantity > 0 && $expiryDate) {
$expiryCarbon = \Carbon\Carbon::parse($expiryDate);
$today = \Carbon\Carbon::now();
$daysRemaining = $today->diffInDays($expiryCarbon, false);

if ($daysRemaining <= 0) {
    // Expired or expires today
    $isCritical=true;
    $badgeClass='bg-danger' ;
    $textClass='text-white' ;
    } elseif ($daysRemaining <=7) {
    // 7 days or less - Critical (Red)
    $isCritical=true;
    $badgeClass='bg-danger' ;
    $textClass='text-white' ;
    } elseif ($daysRemaining <=30) {
    // 1 month or less - Warning (Yellow)
    $isExpiring=true;
    $badgeClass='bg-warning' ;
    $textClass='text-dark' ;
    } else {
    // More than 30 days - Fresh (Green)
    $isFresh=true;
    $badgeClass='bg-success' ;
    $textClass='text-white' ;
    }
    }
    @endphp

    <div class="d-flex align-items-center">
    @if($availableQuantity > 0 && $expiryDate)
    <span class="badge {{ $badgeClass }} {{ $textClass }}" style="font-size: 0.75rem; min-width: 80px;">
        {{ \Carbon\Carbon::parse($expiryDate)->format('M d, Y') }}
    </span>
    @if($isCritical)
    <i class="fas fa-exclamation-triangle text-danger ms-2" title="Critical: {{ $daysRemaining <= 0 ? 'Expired!' : $daysRemaining . ' day(s) left!' }}" style="font-size: 1.1rem;"></i>
    @elseif($isExpiring)
    <i class="fas fa-exclamation-circle text-warning ms-2" title="Warning: {{ $daysRemaining }} day(s) until expiry" style="font-size: 1.1rem;"></i>
    @else
    <i class="fas fa-check-circle text-success ms-2" title="Fresh: {{ $daysRemaining }} day(s) remaining" style="font-size: 1.1rem;"></i>
    @endif
    @else
    <span class="text-muted" style="font-size: 0.85rem;">
        <i class="fas fa-info-circle me-1"></i>No expiry data
    </span>
    @endif
    </div>
