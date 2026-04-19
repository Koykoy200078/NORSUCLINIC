@php
$availableQty = (int) $row->available_quantity;
$totalQty = (int) $row->quantity;
$baselineQty = isset($row->baseline_quantity) && $row->baseline_quantity !== null ? (int) $row->baseline_quantity : 0;
$reorderLevel = isset($row->reorder_level) && $row->reorder_level !== null ? (int) $row->reorder_level : 0;
$minAlert = isset($row->minimum_stock_alert) && $row->minimum_stock_alert !== null ? (int) $row->minimum_stock_alert : 0;
$percentAlert = isset($row->stock_alert_percentage) && $row->stock_alert_percentage !== null ? (float) $row->stock_alert_percentage : 0;

// Use a stable denominator so percentage does not mirror live stock.
$stableDenominator = $baselineQty > 0
? $baselineQty
: ($reorderLevel > 0 ? $reorderLevel : ($minAlert > 0 ? $minAlert : max($totalQty, 1)));

$percentageRemaining = round((floatval($availableQty) / floatval($stableDenominator)) * 100, 2);

$badgeClass = 'bg-light-success';
$textClass = 'text-success';
$showWarning = false;
$warningMessage = '';

$debugInfo = "Avail=$availableQty, StableBase=$stableDenominator, %Remain=$percentageRemaining%, Alert=$percentAlert%";

if ($availableQty === 0) {
$badgeClass = 'bg-light-danger';
$textClass = 'text-danger';
$showWarning = true;
$warningMessage = 'Out of stock!';
} elseif ($minAlert > 0 && $availableQty <= $minAlert) {
    $badgeClass='bg-light-warning' ;
    $textClass='text-warning' ;
    $showWarning=true;
    $warningMessage='Low stock! Only ' . $availableQty . ' left (Min: ' . $minAlert . ')' ;
    } elseif ($percentAlert> 0 && $percentageRemaining <= $percentAlert) {
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
        <i class="fas fa-exclamation-triangle {{ $availableQty === 0 ? 'text-danger' : 'text-warning' }} ms-2"
            title="{{ $warningMessage }}"
            style="font-size: 1rem;"></i>
        @endif
        </div>