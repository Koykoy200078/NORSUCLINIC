<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Facades\Cache;

class SettingsService
{
    const CACHE_KEY = 'application_settings';
    const CACHE_TTL = 3600; // 1 hour

    /**
     * Get setting value(s) from cache or database
     *
     * @param string|null $key
     * @return mixed
     */
    public static function get($key = null)
    {
        $settings = Cache::remember(self::CACHE_KEY, self::CACHE_TTL, function () {
            return Setting::pluck('value', 'key')->toArray();
        });

        return $key ? ($settings[$key] ?? null) : $settings;
    }

    /**
     * Set a setting value and clear cache
     *
     * @param string $key
     * @param mixed $value
     * @return void
     */
    public static function set($key, $value)
    {
        Setting::updateOrCreate(['key' => $key], ['value' => $value]);
        self::clearCache();
    }

    /**
     * Clear settings cache
     *
     * @return void
     */
    public static function clearCache()
    {
        Cache::forget(self::CACHE_KEY);
    }

    /**
     * Refresh cache with fresh data
     *
     * @return array
     */
    public static function refresh()
    {
        self::clearCache();
        return self::get();
    }

    /**
     * Get multiple settings by keys
     *
     * @param array $keys
     * @return array
     */
    public static function getMultiple(array $keys)
    {
        $settings = self::get();

        return array_intersect_key($settings, array_flip($keys));
    }

    /**
     * Check if a setting exists
     *
     * @param string $key
     * @return bool
     */
    public static function has($key)
    {
        $settings = self::get();

        return isset($settings[$key]);
    }
}
