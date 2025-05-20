<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\Vaccination
 *
 * @property int $id
 * @property string|null $vaccination_status
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|Vaccination newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Vaccination newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Vaccination query()
 * @method static \Illuminate\Database\Eloquent\Builder|Vaccination whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Vaccination whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Vaccination whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Vaccination whereVaccinationStatus($value)
 * @mixin \Eloquent
 */
class Vaccination extends Model
{
    use HasFactory;
    public $table = 'vaccinations';
    protected $fillable = [
        'vaccination_status',
    ];
}
