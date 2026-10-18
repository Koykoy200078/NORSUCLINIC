<?php

namespace App\Support;

use App\Models\Medicine;
use App\Models\MedicineBatch;
use Carbon\Carbon;

/**
 * The inventory alerts of the sidebar: how many medicines have expired stock on the shelf, how many expire within 7
 * days, within 30 days, and how many are low in stock. The numbers are counted when the page is drawn (a stock change
 * must be on the badge at once) and only for a role that can open the inventory.
 *
 * Each count is a number of MEDICINES, not of batches. A batch counts only while it still holds units.
 */
final class InventoryAlerts
{
    /** Expiring within this many days is "critical" (today counts); the next stretch up to WARNING_DAYS is "warning". */
    public const CRITICAL_DAYS = 7;

    public const WARNING_DAYS = 30;

    /** @return array{expired: int, critical: int, warning: int, low_stock: int} */
    public static function counts(?Carbon $now = null): array
    {
        $today = ($now ?? Carbon::now())->copy()->startOfDay();

        $batches = fn () => MedicineBatch::query()->where('quantity', '>', 0)->whereNotNull('expiration_date');

        return [
            'expired' => $batches()->whereDate('expiration_date', '<', $today->toDateString())->distinct()->count('medicine_id'),
            'critical' => $batches()
                ->whereDate('expiration_date', '>=', $today->toDateString())
                ->whereDate('expiration_date', '<=', $today->copy()->addDays(self::CRITICAL_DAYS)->toDateString())
                ->distinct()->count('medicine_id'),
            'warning' => $batches()
                ->whereDate('expiration_date', '>=', $today->copy()->addDays(self::CRITICAL_DAYS + 1)->toDateString())
                ->whereDate('expiration_date', '<=', $today->copy()->addDays(self::WARNING_DAYS)->toDateString())
                ->distinct()->count('medicine_id'),
            'low_stock' => Medicine::lowStock()->count(),
        ];
    }
}
