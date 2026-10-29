<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * One quarterly count sheet: the final remaining count of the medicines for a quarter (or for everything, counted on a day).
 * A draft can be changed; a finalized sheet is frozen and is what the reports use.
 */
class InventoryCount extends Model
{
    public const STATUS_DRAFT = 'draft';

    public const STATUS_FINALIZED = 'finalized';

    public const SCOPE_ALL = 'all';

    public const SCOPE_SELECTED = 'selected';

    protected $fillable = ['year', 'quarter', 'scope', 'counted_on', 'status', 'notes', 'created_by', 'finalized_by', 'finalized_at'];

    protected $casts = [
        'year' => 'integer',
        'quarter' => 'integer',
        'counted_on' => 'date',
        'finalized_at' => 'datetime',
    ];

    public function lines(): HasMany
    {
        return $this->hasMany(InventoryCountLine::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by')->withTrashed();
    }

    public function finalizer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'finalized_by')->withTrashed();
    }

    public function isDraft(): bool
    {
        return $this->status === self::STATUS_DRAFT;
    }

    public function isFinalized(): bool
    {
        return $this->status === self::STATUS_FINALIZED;
    }

    /** "2026 Q2", or "2026, all medicines counted 2026-10-29" for a count of everything. */
    public function label(): string
    {
        return self::labelFor($this->year, $this->quarter, $this->counted_on?->toDateString());
    }

    public static function labelFor(int $year, ?int $quarter, ?string $countedOn): string
    {
        return $quarter ? "{$year} Q{$quarter}" : "{$year}, all medicines counted {$countedOn}";
    }
}
