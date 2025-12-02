<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * App\Models\Generic
 *
 * @property int $id
 * @property string $name
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Category|null $category
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Medicine> $medicines
 * @property-read int|null $medicines_count
 * @method static \Illuminate\Database\Eloquent\Builder|Generic newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Generic newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Generic query()
 * @method static \Illuminate\Database\Eloquent\Builder|Generic whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Generic whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Generic whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Generic whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Generic extends Model
{
    public $table = 'generics';

    public $fillable = [
        'name',
    ];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'id' => 'integer',
        'name' => 'string',
    ];

    /**
     * Validation rules
     *
     * @var array
     */
    public static $rules = [
        'name' => 'required|unique:generics,name',
    ];

    public function medicines(): HasMany
    {
        return $this->hasMany(Medicine::class, 'generic_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'category_id');
    }
}
