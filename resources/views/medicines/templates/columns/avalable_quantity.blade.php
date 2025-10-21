@php
$availableQty = (int) $row->available_quantity;
$totalQty = (int) $row->quantity;
$minAlert = isset($row->minimum_stock_alert) && $row->minimum_stock_alert !== null ? (int) $row->minimum_stock_alert : 0;
$percentAlert = isset($row->stock_alert_percentage) && $row->stock_alert_percentage !== null ? (float) $row->stock_alert_percentage : 0;

// Calculate percentage of available stock
$percentageRemaining = $totalQty > 0 ? round((floatval($availableQty) / floatval($totalQty)) * 100, 2) : 0;

// Determine alert level
$badgeClass = 'bg-light-success';
$textClass = 'text-success';
$showWarning = false;
$warningMessage = '';

// Debug info
$debugInfo = "Avail=$availableQty, Total=$totalQty, %Remain=$percentageRemaining%, Alert=$percentAlert%";

// Check if completely out of stock first
if ($availableQty == 0) {
$badgeClass = 'bg-light-danger';
$textClass = 'text-danger';
$showWarning = true;
$warningMessage = 'Out of stock!';
}
// Check minimum stock alert (higher priority)
elseif ($minAlert > 0 && $availableQty <= $minAlert) {
    $badgeClass='bg-light-warning' ;
    $textClass='text-warning' ;
    $showWarning=true;
    $warningMessage='Low stock! Only ' . $availableQty . ' left (Min: ' . $minAlert . ')' ;
    }
    // Check percentage alert
    elseif ($percentAlert> 0 && $percentageRemaining <= $percentAlert) {
        $badgeClass='bg-light-warning' ;
        $textClass='text-warning' ;
        $showWarning=true;
        $warningMessage='Low stock! ' . number_format($percentageRemaining, 2) . '% remaining (Alert at ' . number_format($percentAlert, 2) . '%)' ;
        }
        @endphp

        <div class="d-flex align-items-center">
        <span class="badge {{ $badgeClass }} {{ $textClass }}" style="font-size: 0.85rem; min-width: 60px;" title="{{ $debugInfo }}">
            {{ $availableQty }}
        </span>
        @if($showWarning)
        <i class="fas fa-exclamation-triangle {{ $availableQty == 0 ? 'text-danger' : 'text-warning' }} ms-2"
            title="{{ $warningMessage }}"
            style="font-size: 1rem;"></i>
        @endif
        </div>