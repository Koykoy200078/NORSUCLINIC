<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * App\Models\PurchaseMedicine
 *
 * @property int $id
 * @property string $purchase_no
 * @property float $tax
 * @property float $total
 * @property float $net_amount
 * @property int $payment_type
 * @property float $discount
 * @property string|null $note
 * @property string|null $payment_note
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\PurchasedMedicine> $purchasedMedcines
 * @property-read int|null $purchased_medcines_count
 * @method static \Illuminate\Database\Eloquent\Builder|PurchaseMedicine newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|PurchaseMedicine newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|PurchaseMedicine query()
 * @method static \Illuminate\Database\Eloquent\Builder|PurchaseMedicine whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PurchaseMedicine whereDiscount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PurchaseMedicine whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PurchaseMedicine whereNetAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PurchaseMedicine whereNote($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PurchaseMedicine wherePaymentNote($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PurchaseMedicine wherePaymentType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PurchaseMedicine wherePurchaseNo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PurchaseMedicine whereTax($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PurchaseMedicine whereTotal($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PurchaseMedicine whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class PurchaseMedicine extends Model
{
    protected $fillable =
    [
        'purchase_no',
        'total',
        'discount',
        'tax',
        'net_amount',
        'payment_type',
        'payment_note',
        'note',
    ];

    const CASH = 0;

    const CHEQUE = 1;

    const OTHER = 2;

    const PAYMENT_METHOD = [
        self::CASH => 'Cash',
        self::CHEQUE => 'Cheque',
        self::OTHER => 'Other',
    ];

    public function purchasedMedcines(): HasMany
    {
        return $this->hasMany(PurchasedMedicine::class, 'purchase_medicines_id');
    }
}
