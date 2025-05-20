<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\College
 *
 * @property int $id
 * @property string $college_name
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|College newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|College newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|College query()
 * @method static \Illuminate\Database\Eloquent\Builder|College whereCollegeName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|College whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|College whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|College whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class College extends Model
{
    use HasFactory;
    public $table = 'colleges';
    protected $fillable = [
        'college_name',
    ];
}
