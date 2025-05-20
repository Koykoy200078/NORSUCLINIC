<?php

namespace App\Models;

use Database\Factories\ServiceCategoryFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * App\Models\ServiceCategory
 *
 * @property int $id
 * @property string $name
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Service> $activatedServices
 * @property-read int|null $activated_services_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Service> $services
 * @property-read int|null $services_count
 * @method static \Database\Factories\ServiceCategoryFactory factory($count = null, $state = [])
 * @method static Builder|ServiceCategory newModelQuery()
 * @method static Builder|ServiceCategory newQuery()
 * @method static Builder|ServiceCategory query()
 * @method static Builder|ServiceCategory whereCreatedAt($value)
 * @method static Builder|ServiceCategory whereId($value)
 * @method static Builder|ServiceCategory whereName($value)
 * @method static Builder|ServiceCategory whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class ServiceCategory extends Model
{
    use HasFactory;

    protected $table = 'service_categories';

    public $fillable = [
        'name',
    ];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'name' => 'string',
    ];

    /**
     * Validation rules
     *
     * @var array
     */
    public static $rules = [
        'name' => 'required|unique:service_categories,name',
    ];

    public function services(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Service::class, 'category_id');
    }

    public function activatedServices(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Service::class, 'category_id')->where('status', Service::ACTIVE);
    }
}
