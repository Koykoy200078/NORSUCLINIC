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
 * @property-read \App\Models\PrescriptionMedicine|null $prescriptionMedicines
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

    /** The dispense form lists the medicines from a 10-minute cache: a medicine added or renamed must be in it at once. */
    protected static function booted(): void
    {
        $flush = fn () => \Illuminate\Support\Facades\Cache::forget('medicines_list');
        static::saved($flush);
        static::deleted($flush);
    }

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
        'generic_name' => 'required|string|max:191',
        'brand_name' => 'nullable|string|max:191',
        'category' => 'required|string|max:191',
        'category_name' => 'nullable|string|max:191',
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

    /**
     * LOW STOCK: the one definition behind the sidebar badge, the Report inventory tab (and its CSV) and the warning in
     * the medicine list. A medicine counts when it has ever held stock (units now, or a recorded baseline) and
     *  - its units are at or below its minimum alert, or
     *  - its units are at or below the alert percentage of its stable base (the baseline, else the reorder level, else the
     *    minimum alert, else 1).
     * A medicine that ran out counts too; a catalogue entry that never had any stock does not.
     * isLowStock() must give the same answer as this scope for every row (LowStockDefinitionTest).
     */
    public function scopeLowStock($query)
    {
        $base = 'COALESCE(NULLIF(medicines.baseline_quantity, 0), NULLIF(medicines.reorder_level, 0), NULLIF(medicines.minimum_stock_alert, 0), 1)';

        return $query
            ->where(function ($ever) {
                $ever->where('medicines.available_quantity', '>', 0)->orWhere('medicines.baseline_quantity', '>', 0);
            })
            ->where(function ($low) use ($base) {
                $low->whereRaw('medicines.minimum_stock_alert IS NOT NULL AND medicines.minimum_stock_alert > 0 AND medicines.available_quantity <= medicines.minimum_stock_alert')
                    ->orWhereRaw("medicines.stock_alert_percentage IS NOT NULL AND medicines.stock_alert_percentage > 0 AND (medicines.available_quantity / {$base} * 100) <= medicines.stock_alert_percentage");
            });
    }

    /** The same rule as scopeLowStock(), for a medicine that is already loaded. */
    public function isLowStock(): bool
    {
        $available = (int) $this->available_quantity;
        $baseline = (int) $this->baseline_quantity;
        $reorder = (int) $this->reorder_level;
        $minimum = (int) $this->minimum_stock_alert;
        $percent = (float) $this->stock_alert_percentage;

        if ($available <= 0 && $baseline <= 0) {
            return false;
        }

        if ($minimum > 0 && $available <= $minimum) {
            return true;
        }

        $base = $baseline > 0 ? $baseline : ($reorder > 0 ? $reorder : ($minimum > 0 ? $minimum : 1));

        return $percent > 0 && ($available / $base * 100) <= $percent;
    }

    /** "Out of Stock", "Low Stock" or "Healthy": the status the report, its CSV and the list use. */
    public function stockStatus(): string
    {
        if ((int) $this->available_quantity <= 0) {
            return 'Out of Stock';
        }

        return $this->isLowStock() ? 'Low Stock' : 'Healthy';
    }

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
        // The medicine list loads the in-stock batches of every row in one query: use them instead of asking again per row.
        if ($this->relationLoaded('batches')) {
            $today = now()->toDateString();

            return $this->batches
                ->filter(fn ($batch) => (int) $batch->quantity > 0 && $batch->expiration_date !== null)
                ->map(fn ($batch) => \Illuminate\Support\Carbon::parse($batch->expiration_date)->toDateString())
                ->filter(fn ($date) => $date >= $today)
                ->min();
        }

        return MedicineBatch::where('medicine_id', $this->id)
            ->where('quantity', '>', 0)
            ->whereNotNull('expiration_date')
            ->whereDate('expiration_date', '>=', now()->toDateString())
            ->min('expiration_date');
    }
}
