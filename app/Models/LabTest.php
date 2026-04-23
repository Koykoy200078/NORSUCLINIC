<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\LabTest
 *
 * @property int $id
 * @property string $name
 * @property string $category  (Laboratory, Hematology, Microbiology, Radiology, Cardiac, Other)
 * @property string|null $unit
 * @property string|null $normal_range
 * @property bool $is_active
 */
class LabTest extends Model
{
    use HasFactory;

    protected $table = 'lab_tests';

    protected $fillable = [
        'name',
        'category',
        'unit',
        'normal_range',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    // -------------------------------------------------------------------------
    // Relationships
    // -------------------------------------------------------------------------

    public function requestItems()
    {
        return $this->hasMany(LabRequestItem::class, 'lab_test_id');
    }

    // -------------------------------------------------------------------------
    // Scopes
    // -------------------------------------------------------------------------

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByCategory($query, string $category)
    {
        return $query->where('category', $category);
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    /**
     * Get all distinct categories.
     */
    public static function categories(): array
    {
        return [
            'Laboratory',
            'Hematology',
            'Microbiology',
            'Radiology',
            'Cardiac',
            'Other',
        ];
    }

    /**
     * Get tests grouped by category.
     */
    public static function groupedByCategory()
    {
        return static::active()->orderBy('category')->orderBy('name')->get()->groupBy('category');
    }
}
