<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * App\Models\MedicineAvailability
 *
 * @property int $id
 * @property string $availability_no
 * @property string|null $note
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\PurchasedMedicine> $purchasedMedcines
 * @property-read int|null $purchased_medcines_count
 * @method static \Illuminate\Database\Eloquent\Builder|MedicineAvailability newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|MedicineAvailability newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|MedicineAvailability query()
 * @method static \Illuminate\Database\Eloquent\Builder|MedicineAvailability whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MedicineAvailability whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MedicineAvailability whereNote($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MedicineAvailability wherePurchaseNo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MedicineAvailability whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class MedicineAvailability extends Model
{
    protected $fillable =
    [
        'availability_no',
    ];

    // Payment method constants removed - no longer tracking payment information
    // Medicine availability now only tracks stock quantities and dates

    public function purchasedMedcines(): HasMany
    {
        return $this->hasMany(PurchasedMedicine::class, 'medicine_availabilities_id');
    }
}
