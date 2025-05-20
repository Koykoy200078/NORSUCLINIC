<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\Diagnose
 *
 * @property int $id
 * @property string $diagnoses
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|Diagnose newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Diagnose newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Diagnose query()
 * @method static \Illuminate\Database\Eloquent\Builder|Diagnose whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Diagnose whereDiagnoses($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Diagnose whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Diagnose whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Diagnose extends Model
{
    use HasFactory;

    public $table = 'diagnoses';
    protected $fillable = [
        'diagnoses',
    ];
}
