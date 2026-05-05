<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * App\Models\SaleMedicine
 *
 * @property int $id
 * @property int $medicine_bill_id
 * @property int $medicine_id
 * @property int $sale_quantity
 * @property string $expiry_date
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Medicine $medicine
 * @property-read \App\Models\MedicineBill|null $medicineBill
 * @method static \Illuminate\Database\Eloquent\Builder|SaleMedicine newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|SaleMedicine newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|SaleMedicine query()
 * @method static \Illuminate\Database\Eloquent\Builder|SaleMedicine whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SaleMedicine whereExpiryDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SaleMedicine whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SaleMedicine whereMedicineBillId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SaleMedicine whereMedicineId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SaleMedicine whereSaleQuantity($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SaleMedicine whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class SaleMedicine extends Model
{
    use HasFactory;

    protected $table = 'sale_medicines';

    protected $fillable = [
        'dispense_id',
        'quantity',
        'expires_at',
        'medicine_bill_id',
        'medicine_id',
        'sale_quantity',
        'expiry_date',
    ];

    public function setDispenseIdAttribute($value): void
    {
        $this->attributes['medicine_bill_id'] = $value;
    }

    public function setQuantityAttribute($value): void
    {
        $this->attributes['sale_quantity'] = $value;
    }

    public function setExpiresAtAttribute($value): void
    {
        $this->attributes['expiry_date'] = $value;
    }

    public function medicine(): BelongsTo
    {
        return $this->belongsTo(Medicine::class);
    }

    /** @deprecated Use dispenseRecord() */
    public function medicineBill(): BelongsTo
    {
        return $this->belongsTo(DispenseRecord::class, 'medicine_bill_id');
    }

    public function dispenseRecord(): BelongsTo
    {
        return $this->belongsTo(DispenseRecord::class, 'medicine_bill_id');
    }
}
