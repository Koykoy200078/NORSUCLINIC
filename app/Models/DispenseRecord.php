<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Schema;

/**
 * Renamed from MedicineBill → DispenseRecord.
 * Table stays `medicine_bills` (old workspace — DB not yet migrated).
 * No payment semantics in the recode logic.
 *
 * Legacy financial columns may still exist in older databases. Use
 * legacyFinancialDefaults() when creating records to keep compatibility.
 */
class DispenseRecord extends Model
{
    use HasFactory;

    private static ?array $legacyFinancialColumns = null;

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

    public static function legacyFinancialDefaults(): array
    {
        $defaults = [
            'net_amount' => 0,
            'discount' => 0,
            'payment_status' => 1,
            'payment_type' => 0,
            'total' => 0,
            'tax_amount' => 0,
        ];

        if (self::$legacyFinancialColumns === null) {
            $table = (new static())->getTable();
            self::$legacyFinancialColumns = [];

            if (Schema::hasTable($table)) {
                foreach (array_keys($defaults) as $column) {
                    if (Schema::hasColumn($table, $column)) {
                        self::$legacyFinancialColumns[] = $column;
                    }
                }
            }
        }

        return array_intersect_key($defaults, array_flip(self::$legacyFinancialColumns));
    }

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
