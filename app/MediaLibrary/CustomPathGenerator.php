<?php

namespace App\MediaLibrary;

use App\Models\Patient;
use App\Models\Service;
use App\Models\Setting;
use App\Models\Slider;
use App\Models\User;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Spatie\MediaLibrary\Support\PathGenerator\PathGenerator;

/**
 * Class CustomPathGenerator
 */
class CustomPathGenerator implements PathGenerator
{
    public function getPath(Media $media): string
    {
        $path = '{PARENT_DIR}' . DIRECTORY_SEPARATOR . $media->id . DIRECTORY_SEPARATOR;

        switch ($media->collection_name) {
            case User::PROFILE:
                return str_replace('{PARENT_DIR}', User::PROFILE, $path);
            case Patient::PROFILE:
                return str_replace('{PARENT_DIR}', Patient::PROFILE, $path);
            case Setting::LOGO:
                return str_replace('{PARENT_DIR}', Setting::LOGO, $path);
            case Setting::FAVICON:
                return str_replace('{PARENT_DIR}', Setting::FAVICON, $path);
            case Slider::SLIDER_IMAGE:
                return str_replace('{PARENT_DIR}', Slider::SLIDER_IMAGE, $path);
            case Service::ICON:
                return str_replace('{PARENT_DIR}', Service::ICON, $path);
            case 'consultation_images':
                // Get custom upload path from media properties
                $uploadPath = $media->getCustomProperty('upload_path');
                if ($uploadPath) {
                    return $uploadPath . DIRECTORY_SEPARATOR;
                }
                // Fallback to default structure
                return 'consultation_images' . DIRECTORY_SEPARATOR . $media->model_id . DIRECTORY_SEPARATOR;
            case 'default':
                return '';
        }
        // Default return if no case matches
        return '';
    }

    public function getPathForConversions(Media $media): string
    {
        return $this->getPath($media) . 'thumbnails/';
    }

    public function getPathForResponsiveImages(Media $media): string
    {
        return $this->getPath($media) . 'rs-images/';
    }
}
