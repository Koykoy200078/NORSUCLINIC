<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * App\Models\State
 *
 * @property int $id
 * @property string $name
 * @property int $country_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read \App\Models\Country $country
 * @method static \Illuminate\Database\Eloquent\Builder|State newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|State newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|State query()
 * @method static \Illuminate\Database\Eloquent\Builder|State whereCountryId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|State whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|State whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|State whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|State whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class State extends Model
{
    use HasFactory;

    protected $table = 'states';

    public $fillable = [
        'name',
        'country_id',
    ];

    const STATE_ARRAY = [
        'Batangas' => 'Philippines',
        'Bicol' => 'Philippines',
        'Bulacan' => 'Philippines',
        'Cagayan' => 'Philippines',
        'Caraga' => 'Philippines',
        'Central Luzon' => 'Philippines',
        'Central Mindanao' => 'Philippines',
        'Central Visayas' => 'Philippines',
        'Cordillera' => 'Philippines',
        'Davao' => 'Philippines',
        'Eastern Visayas' => 'Philippines',
        'Greater Metropolitan Area' => 'Philippines',
        'Ilocos' => 'Philippines',
        'Laguna' => 'Philippines',
        'Luzon' => 'Philippines',
        'Mactan' => 'Philippines',
        'Metropolitan Manila Area' => 'Philippines',
        'Muslim Mindanao' => 'Philippines',
        'Northern Mindanao' => 'Philippines',
        'Southern Mindanao' => 'Philippines',
        'Southern Tagalog' => 'Philippines',
        'Western Mindanao' => 'Philippines',
        'Western Visayas' => 'Philippines',
    ];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'name' => 'string',
        'country_id' => 'integer',
    ];

    /**
     * Validation rules
     *
     * @var array
     */
    public static $rules = [
        'name' => 'required|unique:states,name',
    ];

    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class, 'country_id');
    }
}
