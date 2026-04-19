<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

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
        'generic_name',
        'brand_name',
        'category',
        'category_name',
        'dosage',
        'uom',
        'sku',
        'reorder_level',
        'baseline_quantity',
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
        'generic_name' => 'string',
        'brand_name' => 'string',
        'category' => 'string',
        'category_name' => 'string',
        'dosage' => 'string',
        'uom' => 'string',
        'sku' => 'string',
        'reorder_level' => 'integer',
        'baseline_quantity' => 'integer',
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
        'generic_name' => 'required|string|max:255',
        'brand_name' => 'nullable|string|max:255',
        'category' => 'required|string|max:255',
        'category_name' => 'nullable|string|max:255',
        'dosage' => 'required|string|max:100',
        'uom' => 'required|string|max:50',
        'sku' => 'nullable|string|max:100|unique:medicines,sku',
        'reorder_level' => 'nullable|integer|min:0',
        'name' => 'nullable|min:2',
        'side_effects' => 'nullable',
        'salt_composition' => 'nullable|string',
        // 'quantity'    => 'required|integer',
        // 'available_quantity' => 'required|integer|lte:quantity'
    ];

    public function category(): BelongsTo
    {
        return $this->medicineCategory();
    }

    public function medicineCategory(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    public function generic(): BelongsTo
    {
        return $this->belongsTo(Generic::class);
    }

    public function prescriptionMedicines(): BelongsTo
    {
        return $this->belongsTo(PrescriptionMedicine::class, 'medicine');
    }

    public function usedMedicines(): BelongsTo
    {
        return $this->belongsTo(UsedMedicine::class);
    }

    public function purchasedMedicine(): BelongsTo
    {
        return $this->belongsTo(PurchasedMedicine::class);
    }

    public function batches(): HasMany
    {
        return $this->hasMany(MedicineBatch::class, 'medicine_id');
    }

    public function transactions(): HasManyThrough
    {
        return $this->hasManyThrough(
            MedicineTransaction::class,
            MedicineBatch::class,
            'medicine_id',
            'batch_id',
            'id',
            'id'
        );
    }

    public function getDisplayNameAttribute(): string
    {
        $brand = trim((string) $this->brand_name);
        if ($brand !== '') {
            return $brand;
        }

        $name = trim((string) $this->name);
        if ($name !== '') {
            return $name;
        }

        return (string) $this->generic_label;
    }

    public function getGenericLabelAttribute(): string
    {
        return $this->generic_name ?: optional($this->generic)->name ?: 'N/A';
    }

    public function getCategoryLabelAttribute(): string
    {
        return $this->category ?: $this->category_name ?: optional($this->medicineCategory)->name ?: 'Uncategorized';
    }

    public function setCategoryAttribute($value): void
    {
        $normalized = trim((string) $value);
        $normalized = $normalized !== '' ? $normalized : null;

        $this->attributes['category'] = $normalized;

        if (empty($this->attributes['category_name']) && $normalized !== null) {
            $this->attributes['category_name'] = $normalized;
        }
    }

    public function setCategoryNameAttribute($value): void
    {
        $normalized = trim((string) $value);
        $normalized = $normalized !== '' ? $normalized : null;

        $this->attributes['category_name'] = $normalized;

        if (empty($this->attributes['category']) && $normalized !== null) {
            $this->attributes['category'] = $normalized;
        }
    }

    /**
     * Get earliest expiry from the batch ledger.
     */
    public function getEarliestExpiryDateAttribute()
    {
        return MedicineBatch::where('medicine_id', $this->id)
            ->whereNotNull('expiration_date')
            ->min('expiration_date');
    }
}
