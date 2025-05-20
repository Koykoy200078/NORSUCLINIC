<?php

namespace App\Models;

use Database\Factories\ServicesFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Carbon;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

/**
 * App\Models\Service
 *
 * @property int $id
 * @property int $category_id
 * @property string $name
 * @property string|null $charges
 * @property bool $status
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property string $short_description
 * @property-read string $icon
 * @property-read \Spatie\MediaLibrary\MediaCollections\Models\Collections\MediaCollection<int, \Spatie\MediaLibrary\MediaCollections\Models\Media> $media
 * @property-read int|null $media_count
 * @property-read \App\Models\ServiceCategory $serviceCategory
 * @property-read Collection<int, \App\Models\Doctor> $serviceDoctors
 * @property-read int|null $service_doctors_count
 * @method static Builder|Service newModelQuery()
 * @method static Builder|Service newQuery()
 * @method static Builder|Service query()
 * @method static Builder|Service whereCategoryId($value)
 * @method static Builder|Service whereCharges($value)
 * @method static Builder|Service whereCreatedAt($value)
 * @method static Builder|Service whereId($value)
 * @method static Builder|Service whereName($value)
 * @method static Builder|Service whereShortDescription($value)
 * @method static Builder|Service whereStatus($value)
 * @method static Builder|Service whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Service extends Model implements HasMedia
{
    use HasFactory, InteractsWithMedia;

    protected $table = 'services';

    public $fillable = [
        'category_id',
        'name',
        'charges',
        'status',
        'short_description',
    ];

    const ALL = 2;

    const ACTIVE = 1;

    const DEACTIVE = 0;

    const STATUS = [
        self::ALL => 'All',
        self::ACTIVE => 'Active',
        self::DEACTIVE => 'Deactive',
    ];

    const ICON = 'icon';

    protected $appends = ['icon'];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'category_id' => 'integer',
        'name' => 'string',
        'charges' => 'string',
        'status' => 'boolean',
        'short_description' => 'string',
    ];

    /**
     * Validation rules
     *
     * @var array
     */
    public static $rules = [
        'name' => 'required|unique:services,name',
        'category_id' => 'required',
        'charges' => 'required|min:0',
        'doctors' => 'required',
        'short_description' => 'required|max:60',
        'icon' => 'required|mimes:svg,jpeg,png,jpg',
    ];

    public function serviceDoctors(): BelongsToMany
    {
        return $this->belongsToMany(Doctor::class, 'service_doctor', 'service_id', 'doctor_id');
    }

    public function serviceCategory(): BelongsTo
    {
        return $this->belongsTo(ServiceCategory::class, 'category_id', 'id');
    }

    public function getIconAttribute(): string
    {
        /** @var Media $media */
        $media = $this->getMedia(self::ICON)->first();

        if ($media) {
            $fullUrl = $media->getFullUrl();
            if (str_starts_with($fullUrl, 'http://localhost')) {
                $fullUrl = request()->getSchemeAndHttpHost() . parse_url($fullUrl, PHP_URL_PATH);
            }
            return $fullUrl;
        }

        return asset('web/media/avatars/male.png');
    }
}
