<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * A service rendered at a visit (the "OTHER SERVICES" rows of the ACCOMPLISHMENT REPORT). A line with an
 * auto_rule is counted from data the system already records; the others are ticked on the consultation form.
 *
 * @property int $id
 * @property string $category clinical_procedure | health_promotion
 * @property string $name
 * @property string|null $auto_rule
 * @property bool $is_other
 */
class ServiceType extends Model
{
    public const CLINICAL = 'clinical_procedure';

    public const PROMOTION = 'health_promotion';

    public const CATEGORY_LABELS = [
        self::CLINICAL => 'Clinical procedures',
        self::PROMOTION => 'Health promotion and wellness',
    ];

    protected $table = 'service_types';

    protected $fillable = ['category', 'name', 'auto_rule', 'is_other', 'sort_order', 'is_active'];

    protected $casts = [
        'is_other' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered(Builder $query): Builder
    {
        // The two lists (clinical procedures, then health promotion) each keep their own sort order.
        return $query->orderBy('category')->orderBy('sort_order')->orderBy('id');
    }
}
