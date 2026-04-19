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
 * @property float $sale_price
 * @property float $tax
 * @property string $expiry_date
 * @property float $amount
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Medicine $medicine
 * @property-read \App\Models\MedicineBill|null $medicineBill
 * @method static \Illuminate\Database\Eloquent\Builder|SaleMedicine newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|SaleMedicine newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|SaleMedicine query()
 * @method static \Illuminate\Database\Eloquent\Builder|SaleMedicine whereAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SaleMedicine whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SaleMedicine whereExpiryDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SaleMedicine whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SaleMedicine whereMedicineBillId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SaleMedicine whereMedicineId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SaleMedicine whereSalePrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SaleMedicine whereSaleQuantity($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SaleMedicine whereTax($value)
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
        'unit_price',
        'charge_amount',
        'expires_at',
        'line_total',
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

    public function setQuantityAttribute($value): void
    {
        $this->attributes['sale_quantity'] = $value;
    }

    public function setUnitPriceAttribute($value): void
    {
        $this->attributes['sale_price'] = $value;
    }

    public function setChargeAmountAttribute($value): void
    {
        $this->attributes['tax'] = $value;
    }

    public function setExpiresAtAttribute($value): void
    {
        $this->attributes['expiry_date'] = $value;
    }

    public function setLineTotalAttribute($value): void
    {
        $this->attributes['amount'] = $value;
    }

    public function medicine(): BelongsTo
    {
        return $this->belongsTo(Medicine::class);
    }

    public function medicineBill(): BelongsTo
    {
        return $this->belongsTo(MedicineBill::class, 'medicine_bill_id');
    }
}
