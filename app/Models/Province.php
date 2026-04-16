<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Renamed from State → Province (guide terminology).
 * Table stays `states` (old workspace — DB not yet migrated to `provinces`).
 */
class Province extends Model
{
    use HasFactory;

    protected $table = 'states';

    public $fillable = [
        'name',
        'country_id',
    ];

    protected $casts = [
        'name'       => 'string',
        'country_id' => 'integer',
    ];

    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class, 'country_id');
    }

    public function cities()
    {
        return $this->hasMany(City::class, 'state_id');
    }
}
