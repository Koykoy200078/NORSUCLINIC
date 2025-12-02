<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * App\Models\Medicine
 *
 * @property int $id
 * @property int|null $category_id
 * @property int|null $generic_id
 * @property string $name
 * @property int $quantity
 * @property int $available_quantity
 * @property string $salt_composition
 * @property string|null $description
 * @property string|null $side_effects
 * @property string|null $currency_symbol
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Generic|null $generic
 * @property-read \App\Models\Category|null $category
 * @property-read \App\Models\PrescriptionMedicineModal|null $prescriptionMedicines
 * @property-read \App\Models\PurchasedMedicine|null $purchasedMedicine
 * @property-read \App\Models\UsedMedicine|null $usedMedicines
 * @method static \Illuminate\Database\Eloquent\Builder|Medicine newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Medicine newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Medicine query()
 * @method static \Illuminate\Database\Eloquent\Builder|Medicine whereAvailableQuantity($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Medicine whereGenericId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Medicine whereBuyingPrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Medicine whereCategoryId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Medicine whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Medicine whereCurrencySymbol($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Medicine whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Medicine whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Medicine whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Medicine whereQuantity($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Medicine whereSaltComposition($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Medicine whereSellingPrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Medicine whereSideEffects($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Medicine whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Medicine extends Model
{
    public $table = 'medicines';

    public $fillable = [
        'category_id',
        'generic_id',
        'name',
        'side_effects',
        'description',
        'salt_composition',
        'currency_symbol',
        'quantity',
        'available_quantity',
        'minimum_stock_alert',
        'stock_alert_percentage',
    ];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'id' => 'integer',
        'category_id' => 'integer',
        'generic_id' => 'integer',
        'name' => 'string',
        'side_effects' => 'string',
        'description' => 'string',
        'salt_composition' => 'string',
        'currency_symbol' => 'string',
        'quantity' => 'integer',
        'available_quantity' => 'integer',
        'minimum_stock_alert' => 'integer',
        'stock_alert_percentage' => 'decimal:2',
    ];

    /**
     * Validation rules
     *
     * @var array
     */
    public static $rules = [
        'category_id' => 'required',
        'generic_id' => 'required',
        'name' => 'required|min:2|unique:medicines,name',
        'side_effects' => 'nullable',
        'salt_composition' => 'nullable|string',
        // 'quantity'    => 'required|integer',
        // 'available_quantity' => 'required|integer|lte:quantity'
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function generic(): BelongsTo
    {
        return $this->belongsTo(Generic::class);
    }

    public function prescriptionMedicines(): BelongsTo
    {
        return $this->belongsTo(PrescriptionMedicineModal::class, 'medicine');
    }

    public function usedMedicines(): BelongsTo
    {
        return $this->belongsTo(UsedMedicine::class);
    }

    public function purchasedMedicine(): BelongsTo
    {
        return $this->belongsTo(PurchasedMedicine::class);
    }

    /**
     * Get the earliest expiry date for this medicine from purchased medicines
     */
    public function getEarliestExpiryDateAttribute()
    {
        return PurchasedMedicine::where('medicine_id', $this->id)
            ->whereNotNull('expiry_date')
            ->min('expiry_date');
    }
}
