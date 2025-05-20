<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\Campus
 *
 * @property int $id
 * @property string $campus_name
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|Campus newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Campus newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Campus query()
 * @method static \Illuminate\Database\Eloquent\Builder|Campus whereCampusName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Campus whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Campus whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Campus whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Campus extends Model
{
    use HasFactory;
    public $table = 'campuses';
    protected $fillable = [
        'campus_name',
    ];
}
