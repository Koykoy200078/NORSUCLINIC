<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * A body system of the ACCOMPLISHMENT REPORT (Respiratory System, Circulatory System, ...).
 *
 * @property int $id
 * @property string $name
 * @property int $sort_order
 */
class IllnessSystem extends Model
{
    protected $table = 'illness_systems';

    protected $fillable = ['name', 'sort_order'];

    public function illnesses(): HasMany
    {
        return $this->hasMany(Illness::class, 'illness_system_id')->orderBy('sort_order')->orderBy('id');
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order')->orderBy('id');
    }
}
