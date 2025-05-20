<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\YearLevel
 *
 * @property int $id
 * @property string $year_level_name
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|YearLevel newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|YearLevel newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|YearLevel query()
 * @method static \Illuminate\Database\Eloquent\Builder|YearLevel whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|YearLevel whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|YearLevel whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|YearLevel whereYearLevelName($value)
 * @mixin \Eloquent
 */
class YearLevel extends Model
{
    use HasFactory;
    public $table = 'year_levels';
    protected $fillable = [
        'year_level_name',
    ];
}
