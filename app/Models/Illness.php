<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * One line of the clinic's illness list (e.g. "Cough/colds" under "Respiratory System / Upper Respiratory
 * Infections"). The "Others" line of each system takes free text.
 *
 * @property int $id
 * @property int $illness_system_id
 * @property string|null $group_label
 * @property string $name
 * @property bool $is_other
 * @property bool $is_active
 */
class Illness extends Model
{
    protected $table = 'illnesses';

    protected $fillable = ['illness_system_id', 'group_label', 'name', 'is_other', 'sort_order', 'is_active'];

    protected $casts = [
        'is_other' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function system(): BelongsTo
    {
        return $this->belongsTo(IllnessSystem::class, 'illness_system_id');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }
}
