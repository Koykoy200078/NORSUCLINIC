<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Carbon;

/**
 * App\Models\Currency
 *
 * @property int $id
 * @property string $currency_name
 * @property string $currency_icon
 * @property string $currency_code
 * @property int $is_default
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @method static \Database\Factories\CurrencyFactory factory($count = null, $state = [])
 * @method static Builder|Currency newModelQuery()
 * @method static Builder|Currency newQuery()
 * @method static Builder|Currency query()
 * @method static Builder|Currency whereCreatedAt($value)
 * @method static Builder|Currency whereCurrencyCode($value)
 * @method static Builder|Currency whereCurrencyIcon($value)
 * @method static Builder|Currency whereCurrencyName($value)
 * @method static Builder|Currency whereId($value)
 * @method static Builder|Currency whereIsDefault($value)
 * @method static Builder|Currency whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Currency extends Model
{
    use HasFactory;

    public $table = 'currencies';

    public $fillable = [
        'currency_name',
        'currency_icon',
        'currency_code',
    ];

    const CURRENCY_ARRAY = [
        'PHP' => 'Philippine Peso',
    ];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'currency_name' => 'string',
        'currency_icon' => 'string',
        'currency_code' => 'string',
    ];

    /**
     * Validation rules
     *
     * @var array
     */
    public static $rules = [
        'currency_name' => 'required|unique:currencies',
        'currency_icon' => 'required',
        'currency_code' => 'required|unique:currencies|min:3|max:3',
    ];
}
