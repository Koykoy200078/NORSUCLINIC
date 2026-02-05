<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * App\Models\City
 *
 * @property int $id
 * @property string $name
 * @property string $type
 * @property string $state_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read \App\Models\State $state
 * @method static \Database\Factories\CityFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder|City newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|City newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|City query()
 * @method static \Illuminate\Database\Eloquent\Builder|City whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|City whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|City whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|City whereStateId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|City whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class City extends Model
{
    use HasFactory;

    public $table = 'cities';

    public $fillable = [
        'name',
        'type',
        'state_id',
    ];




    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'name' => 'string',
        'type' => 'string',
        'state_id' => 'string',
    ];

    const TYPE_CITY = 'city';
    const TYPE_MUNICIPALITY = 'municipality';

    /**
     * Validation rules
     *
     * @var array
     */
    public static $rules = [
        'name' => 'required|string|max:255',
        'type' => 'required|in:city,municipality',
        'state_id' => 'required|exists:states,id',
    ];

    /**
     * Get the available types
     *
     * @return array
     */
    public static function getTypes(): array
    {
        return [
            self::TYPE_CITY => 'City',
            self::TYPE_MUNICIPALITY => 'Municipality',
        ];
    }

    public function state(): BelongsTo
    {
        return $this->belongsTo(State::class, 'state_id');
    }

    /**
     * Get the barangays for the city.
     */
    public function barangays(): HasMany
    {
        return $this->hasMany(Barangay::class, 'city_id');
    }
}
