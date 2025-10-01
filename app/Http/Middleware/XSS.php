<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use HTMLPurifier;
use HTMLPurifier_Config;
use Illuminate\Support\Facades\Cache;

/**
 * Class XSS - Enhanced XSS Protection Middleware
 */
class XSS
{
    /**
     * HTML Purifier instance (cached)
     */
    private static $purifier;

    public function handle(Request $request, Closure $next): Response
    {
        // Skip XSS cleaning for safe routes
        $skipRoutes = [
            'cms.update',
            'dashboard',
            'admin.dashboard',
            'staff.dashboard',
            'doctors.dashboard',
            'patients.dashboard'
        ];

        if (in_array($request->route()->getName(), $skipRoutes)) {
            return $next($request);
        }

        // Only process POST, PUT, PATCH requests with input data
        if (!in_array($request->method(), ['POST', 'PUT', 'PATCH']) || empty($request->all())) {
            return $next($request);
        }

        $input = $request->all();

        // Get or create cached purifier instance
        if (!self::$purifier) {
            self::$purifier = $this->getPurifier();
        }

        // Only clean text inputs, skip arrays and files
        $cleanedInput = $this->cleanInputRecursively($input, self::$purifier);
        $request->merge($cleanedInput);

        return $next($request);
    }

    /**
     * Get cached HTML Purifier instance
     */
    private function getPurifier(): HTMLPurifier
    {
        return Cache::remember('htmlpurifier_instance', 3600, function () {
            $config = HTMLPurifier_Config::createDefault();
            $config->set('HTML.Allowed', ''); // Strip all HTML tags by default

            // Ensure cache directory exists
            $cachePath = storage_path('app/purifier');
            if (!is_dir($cachePath)) {
                mkdir($cachePath, 0755, true);
            }

            $config->set('Cache.SerializerPath', $cachePath);

            // Disable caching if directory is not writable
            if (!is_writable($cachePath)) {
                $config->set('Cache.DefinitionImpl', null);
            }

            return new HTMLPurifier($config);
        });
    }

    /**
     * Clean input data recursively but efficiently
     */
    private function cleanInputRecursively(array $input, HTMLPurifier $purifier): array
    {
        $cleaned = [];

        foreach ($input as $key => $value) {
            if (is_array($value)) {
                $cleaned[$key] = $this->cleanInputRecursively($value, $purifier);
            } elseif (is_string($value) && strlen($value) > 0) {
                // Only purify non-empty strings
                $cleaned[$key] = $purifier->purify($value);
            } else {
                $cleaned[$key] = $value;
            }
        }

        return $cleaned;
    }
}
