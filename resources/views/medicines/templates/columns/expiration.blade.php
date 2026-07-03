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

    // Build a per-batch expiry list so staff can see that one medicine can have several
    // batches with different expiry dates (the badge above shows only the earliest one).
    $batches = ($row->relationLoaded('batches') ? $row->batches : collect())
        ->filter(fn($b) => (int) $b->quantity > 0);
    $batchTooltip = $batches->map(function ($b) {
    $exp = $b->expiration_date ? \Carbon\Carbon::parse($b->expiration_date) : null;
    $expLabel = (!$exp || $exp->year >= 2099) ? 'No expiry' : $exp->format('M d, Y');
    $dose = trim((string) $b->dosage);
    return '• ' . (int) $b->quantity . ' pcs' . ($dose !== '' ? ' (' . $dose . ')' : '') . ' — exp ' . $expLabel;
    })->implode("\n");
    @endphp

    <div class="d-flex align-items-center">
    @if($availableQuantity > 0 && $expiryDate)
    <span class="badge {{ $badgeClass }} {{ $textClass }}" style="font-size: 0.75rem; min-width: 80px;">
        {{ $displayDate }}
    </span>
    @if($isCritical)
    <i class="fas fa-exclamation-triangle text-danger ms-2" title="Critical: {{ $daysRemaining < 0 ? 'Expired ' . abs($daysRemaining) . ' day(s) ago!' : $daysRemaining . ' day(s) left!' }}" style="font-size: 1.1rem;"></i>
    @elseif($isExpiring)
    <i class="fas fa-exclamation-circle text-warning ms-2" title="Warning: {{ $daysRemaining }} day(s) until expiry" style="font-size: 1.1rem;"></i>
    @else
    <i class="fas fa-check-circle text-success ms-2" title="Fresh: {{ $daysRemaining }} day(s) remaining" style="font-size: 1.1rem;"></i>
    @endif
    @if($batches->count() > 1)
    <span class="badge bg-light text-dark border ms-2" style="font-size: 0.7rem; white-space: pre-line; cursor: help;" title="{{ $batchTooltip }}">
        <i class="fas fa-layer-group me-1"></i>{{ $batches->count() }} batches
    </span>
    @endif
    @else
    <span class="text-muted" style="font-size: 0.85rem;">
        <i class="fas fa-info-circle me-1"></i>No expiry data
    </span>
    @endif
    </div>