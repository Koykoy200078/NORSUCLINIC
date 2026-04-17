<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * Renamed from MedicineBill → DispenseRecord.
 * Table stays `medicine_bills` (old workspace — DB not yet migrated).
 * No payment semantics: discount/net_amount/payment_status columns exist in DB
 * but are not used in the recode logic.
 */
class DispenseRecord extends Model
{
    use HasFactory;

    public const STATUS_UNPAID = 0;

    public const STATUS_FULL_PAID = 1;

    public const STATUS_PARTIAL_PAID = 2;

    protected $table = 'medicine_bills';

    protected $fillable = [
        'history_number',
        'patient_id',
        'doctor_id',
        'model_type',
        'model_id',
        'case_id',
        'admission_id',
        'discount',
        'net_amount',
        'payment_status',
        'payment_type',
        'tax_amount',
        'total',
        'note',
        'bill_date',
    ];

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class, 'patient_id');
    }

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(Doctor::class, 'doctor_id');
    }

    /** Line items dispensed in this record. */
    public function dispenseItems(): HasMany
    {
        return $this->hasMany(DispenseRecordItem::class, 'medicine_bill_id');
    }

    /** @deprecated Use dispenseItems() */
    public function saleMedicine(): HasMany
    {
        return $this->dispenseItems();
    }
}
