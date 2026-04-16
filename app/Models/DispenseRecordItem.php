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
        'medicine_bill_id',
        'medicine_id',
        'sale_quantity',
        'sale_price',
        'tax',
    ];

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
