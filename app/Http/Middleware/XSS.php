<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use HTMLPurifier;
use HTMLPurifier_Config;

/**
 * Class XSS - Enhanced XSS Protection Middleware
 */
class XSS
{
    public function handle(Request $request, Closure $next): Response
    {
        // Skip XSS cleaning for CMS content updates that might contain HTML
        if ($request->route()->getName() == 'cms.update') {
            return $next($request);
        }

        $input = $request->all();

        // Configure HTMLPurifier with proper error handling
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

        $purifier = new HTMLPurifier($config);

        // Recursively clean input data
        array_walk_recursive($input, function (&$input) use ($purifier) {
            if (is_string($input)) {
                $input = $purifier->purify($input);
            }
        });

        $request->merge($input);

        return $next($request);
    }
}
