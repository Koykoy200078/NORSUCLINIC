<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * One medicine on a count sheet: the amount the system showed, the amount counted, the difference, why, and when the shelf
 * stock was corrected from it (if it was).
 */
class InventoryCountLine extends Model
{
    /** What the clinic can give as the reason of a difference ("other" needs a note). */
    public const REASONS = [
        'expired' => 'Expired',
        'damaged' => 'Damaged',
        'lost' => 'Lost or missing',
        'unrecorded_dispense' => 'Dispensed but not recorded',
        'unrecorded_stock_in' => 'Received but not recorded',
        'counting_error' => 'Earlier counting error',
        'other' => 'Other (explain in the note)',
    ];

    protected $fillable = [
        'inventory_count_id', 'medicine_id', 'medicine_name', 'strength', 'unit', 'system_quantity', 'counted_quantity', 'difference',
        'reason', 'note', 'adjusted_at', 'adjusted_by', 'adjusted_audit_id',
    ];

    protected $casts = [
        'system_quantity' => 'integer',
        'counted_quantity' => 'integer',
        'difference' => 'integer',
        'adjusted_at' => 'datetime',
    ];

    public function sheet(): BelongsTo
    {
        return $this->belongsTo(InventoryCount::class, 'inventory_count_id');
    }

    public function medicine(): BelongsTo
    {
        return $this->belongsTo(Medicine::class);
    }

    public function isCounted(): bool
    {
        return $this->counted_quantity !== null;
    }

    public function hasDifference(): bool
    {
        return $this->difference !== null && $this->difference !== 0;
    }

    public function reasonLabel(): ?string
    {
        return $this->reason ? (self::REASONS[$this->reason] ?? $this->reason) : null;
    }
}
