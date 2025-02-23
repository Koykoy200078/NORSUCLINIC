<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Collections\MediaCollection;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class Slider extends Model implements HasMedia
{
    use HasFactory, InteractsWithMedia;

    const SLIDER_IMAGE = 'image';

    /**
     * @var string[]
     */
    public static $rules = [
        'title' => 'required',
        'image' => 'required|mimes:jpeg,png,jpg',
        'short_description' => 'required',
    ];

    /**
     * @var string[]
     */
    public static $editRules = [
        'title' => 'required',
        'image' => 'nullable|mimes:jpeg,png,jpg',
        'short_description' => 'required|max:55',
    ];

    /**
     * @var string
     */
    protected $table = 'sliders';

    /**
     * @var string[]
     */
    protected $fillable = [
        'title',
        'short_description',
        'is_default',
    ];

    protected $casts = [
        'title' => 'string',
        'short_description' => 'string',
        'is_default' => 'boolean',
    ];

    protected $appends = ['slider_image'];

    public function getSliderImageAttribute(): string
    {
        /** @var Media $media */
        $media = $this->getMedia(self::SLIDER_IMAGE)->first();

        if ($media) {
            $fullUrl = $media->getFullUrl();
            if (str_starts_with($fullUrl, 'http://localhost')) {
                $fullUrl = request()->getSchemeAndHttpHost() . parse_url($fullUrl, PHP_URL_PATH);
            }
            return $fullUrl;
        }

        return asset('assets/image/norsu_logo.png');
    }
}
