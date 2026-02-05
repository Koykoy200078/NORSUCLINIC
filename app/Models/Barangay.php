<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * App\Models\Barangay
 *
 * @property int $id
 * @property string $name
 * @property int $city_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read \App\Models\City $city
 * @method static \Illuminate\Database\Eloquent\Builder|Barangay newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Barangay newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Barangay query()
 * @method static \Illuminate\Database\Eloquent\Builder|Barangay whereCityId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Barangay whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Barangay whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Barangay whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Barangay whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Barangay extends Model
{
    use HasFactory;

    protected $table = 'barangays';

    public $fillable = [
        'name',
        'city_id',
    ];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'name' => 'string',
        'city_id' => 'integer',
    ];

    /**
     * Validation rules
     *
     * @var array
     */
    public static $rules = [
        'name' => 'required|string|max:255',
        'city_id' => 'required|exists:cities,id',
    ];

    /**
     * Get the city that the barangay belongs to.
     */
    public function city(): BelongsTo
    {
        return $this->belongsTo(City::class, 'city_id');
    }
}
