<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * Renamed from SaleMedicine → DispenseRecordItem.
 * Table stays `sale_medicines` (old workspace — DB not yet migrated).
 * No sale/payment semantics in the recode.
 */
class DispenseRecordItem extends Model
{
    use HasFactory;

    protected $table = 'sale_medicines';

    protected $fillable = [
        // Generalized app-layer keys
        'dispense_id',
        'quantity',
        'unit_price',
        'charge_amount',
        'expires_at',
        'line_total',
        // Legacy DB columns kept for backward compatibility
        'medicine_bill_id',
        'medicine_id',
        'sale_quantity',
        'sale_price',
        'tax',
        'expiry_date',
        'amount',
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

    public function setUnitPriceAttribute($value): void
    {
        $this->attributes['sale_price'] = $value;
    }

    public function getUnitPriceAttribute(): ?float
    {
        return isset($this->attributes['sale_price'])
            ? (float) $this->attributes['sale_price']
            : null;
    }

    public function setChargeAmountAttribute($value): void
    {
        $this->attributes['tax'] = $value;
    }

    public function getChargeAmountAttribute(): ?float
    {
        return isset($this->attributes['tax'])
            ? (float) $this->attributes['tax']
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

    public function setLineTotalAttribute($value): void
    {
        $this->attributes['amount'] = $value;
    }

    public function getLineTotalAttribute(): float
    {
        if (isset($this->attributes['amount'])) {
            return (float) $this->attributes['amount'];
        }

        $quantity = (float) ($this->attributes['sale_quantity'] ?? 0);
        $unitPrice = (float) ($this->attributes['sale_price'] ?? 0);
        $chargeAmount = (float) ($this->attributes['tax'] ?? 0);

        return ($quantity * $unitPrice) + $chargeAmount;
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
