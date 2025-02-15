<?php

namespace App\Models;

use Eloquent as Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * Class City
 *
 * @version July 31, 2021, 7:41 am UTC
 *
 * @property string $name
 * @property string $state_id
 * @property int $id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read State $state
 *
 * @method static \Database\Factories\CityFactory factory(...$parameters)
 * @method static \Illuminate\Database\Eloquent\Builder|City newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|City newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|City query()
 * @method static \Illuminate\Database\Eloquent\Builder|City whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|City whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|City whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|City whereStateId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|City whereUpdatedAt($value)
 *
 * @mixin Model
 */
class City extends Model
{
    use HasFactory;

    public $table = 'cities';

    public $fillable = [
        'name',
        'state_id',
    ];


    const CITY_ARRAY = [
    "Manila",
    "Quezon City",
    "Caloocan",
    "Davao City",
    "Cebu City",
    "Zamboanga City",
    "Taguig",
    "Antipolo",
    "Pasig",
    "Cagayan de Oro",
    "Parañaque",
    "Dasmariñas",
    "Valenzuela",
    "Las Piñas",
    "General Santos",
    "Makati",
    "Bacolod",
    "Muntinlupa",
    "San Jose del Monte",
    "Iloilo City",
    "Pasay",
    "Malabon",
    "Mandaluyong",
    "Lapu-Lapu",
    "Baguio",
    "Butuan",
    "Biñan",
    "Imus",
    "Iligan",
    "Tarlac City",
    "Lucena",
    "Mandaue",
    "Santa Rosa",
    "Cabanatuan",
    "San Fernando",
    "Olongapo",
    "Cotabato City",
    "Tacloban",
    "Santiago",
    "Dagupan",
    "Navotas",
    "Marikina",
    "Angeles",
    "Bacoor",
    "Calamba",
    "San Pablo",
    "Tuguegarao",
    "Ormoc",
    "San Carlos",
    "Kabankalan",
    "Malaybalay",
    "Roxas",
    "Panabo",
    "Dipolog",
    "Pagadian",
    "Legazpi",
    "Naga",
    "Tagum",
    "Surigao",
    "Kidapawan",
    "Koronadal",
    "Baybay",
    "Talisay",
    "Valencia",
    "Gapan",
    "San Pedro",
    "Silay",
    "Cauayan",
    "Calapan",
    "Ligao",
    "Sorsogon",
    "Tabaco",
    "Urdaneta",
    "Vigan",
    "Puerto Princesa",
    "Sagay",
    "Sorsogon City",
    "Tayabas",
    "Trece Martires",
    "Tuguegarao City",
    "Urdaneta City",
    "Valencia City",
    "Victorias",
    "Vigan City",
    "Zamboanga City"
];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'name' => 'string',
        'state_id' => 'string',
    ];

    /**
     * Validation rules
     *
     * @var array
     */
    public static $rules = [
        'name' => 'required|unique:cities,name',
    ];

    public function state(): BelongsTo
    {
        return $this->belongsTo(State::class, 'state_id');
    }
}
