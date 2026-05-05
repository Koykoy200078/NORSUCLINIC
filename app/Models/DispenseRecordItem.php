<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * Renamed from SaleMedicine → DispenseRecordItem.
 * Table stays `sale_medicines` (old workspace — DB not yet migrated).
 * Legacy pricing semantics are removed in the recode.
 */
class DispenseRecordItem extends Model
{
    use HasFactory;

    protected $table = 'sale_medicines';

    protected $fillable = [
        // Generalized app-layer keys
        'dispense_id',
        'quantity',
        'expires_at',
        'dosage',
        // Legacy DB columns kept for backward compatibility
        'medicine_bill_id',
        'medicine_id',
        'sale_quantity',
        'expiry_date',
    ];

    public function setDispenseIdAttribute($value): void
    {
        $this->attributes['medicine_bill_id'] = $value;
    }

    public function getDispenseIdAttribute(): ?int
    {
        return isset($this->attributes['medicine_bill_id'])
            ? (int) $this->attributes['medicine_bill_id']
            : null;
    }

    public function setQuantityAttribute($value): void
    {
        $this->attributes['sale_quantity'] = $value;
    }

    public function getQuantityAttribute(): ?int
    {
        return isset($this->attributes['sale_quantity'])
            ? (int) $this->attributes['sale_quantity']
            : null;
    }

    public function setExpiresAtAttribute($value): void
    {
        $this->attributes['expiry_date'] = $value;
    }

    public function getExpiresAtAttribute(): ?string
    {
        return $this->attributes['expiry_date'] ?? null;
    }

    public function medicine(): BelongsTo
    {
        return $this->belongsTo(Medicine::class);
    }

    public function dispenseRecord(): BelongsTo
    {
        return $this->belongsTo(DispenseRecord::class, 'medicine_bill_id');
    }

    /** @deprecated Use dispenseRecord() */
    public function medicineBill(): BelongsTo
    {
        return $this->dispenseRecord();
    }
}
