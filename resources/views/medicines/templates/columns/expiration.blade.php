@php
$availableQuantity = $row->available_quantity ?? 0;
$expiryDate = $row->earliest_expiry_date;
$isCritical = false;
$isExpiring = false;
$isFresh = false;
$badgeClass = 'bg-success';
$textClass = 'text-white';
$daysRemaining = null;
$isMonthOnly = false;
$displayDate = '';

// Only show expiry info if there's available quantity
if ($availableQuantity > 0 && $expiryDate) {
// Check if expiry date is in Y-m format (7 chars) or Y-m-d format (10 chars)
if (strlen($expiryDate) === 7 && substr_count($expiryDate, '-') === 1) {
// Month-only format (Y-m): Use last day of the month for expiry calculation
$isMonthOnly = true;
$expiryCarbon = \Carbon\Carbon::parse($expiryDate . '-01')->endOfMonth();
$displayDate = \Carbon\Carbon::parse($expiryDate . '-01')->format('M Y');
} else {
// Full date format (Y-m-d)
$isMonthOnly = false;
$expiryCarbon = \Carbon\Carbon::parse($expiryDate);
$displayDate = $expiryCarbon->format('M d, Y');
}

$today = \Carbon\Carbon::now()->startOfDay();
$expiryDateOnly = $expiryCarbon->copy()->startOfDay();

// Calculate days remaining (positive = future, negative = past)
$daysRemaining = $today->diffInDays($expiryDateOnly, false);

// Check if date is in the past
if ($expiryDateOnly->isPast()) {
$daysRemaining = -$daysRemaining;
}

if ($daysRemaining < 0) {
    // Already expired
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
        {{ $displayDate }}
    </span>
    @if($isCritical)
    <i class="fas fa-exclamation-triangle text-danger ms-2" title="Critical: {{ $daysRemaining < 0 ? 'Expired!' : $daysRemaining . ' day(s) left!' }}" style="font-size: 1.1rem;"></i>
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