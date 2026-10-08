<?php

/*
|--------------------------------------------------------------------------
| Router for `php artisan serve` (PHP's built-in web server)
|--------------------------------------------------------------------------
|
| `php artisan serve` uses this file instead of Laravel's default router when it exists in the project root.
|
| The built-in server ignores public/.htaccess, which is where the Apache rules that protect uploaded files
| live. The same protections are repeated here so they also hold on the clinic PC:
|
|   - nothing under /uploads may be run as a script (uploaded files are data, never code);
|   - the legacy consultation photo folder (public/uploads/consultation_images) is never served directly:
|     clinical photos are streamed only through the signed-in /document-issuances/{id}/images/{n} route;
|   - no PHP file in public/ is run except the front controller, index.php.
|
| Keep this list in step with public/.htaccess.
*/

$publicPath = getcwd();

$uri = urldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?? '');

// Judge the path the way Windows will resolve it: forward slashes only, no "//", no trailing dots or spaces
// ("shell.php. " is shell.php on NTFS) and no alternate data stream suffix ("shell.php::$DATA").
$checked = preg_replace('#/+#', '/', str_replace('\\', '/', $uri));
$checked = preg_replace('/::.*$/', '', $checked);
$checked = rtrim($checked, ". \t");

$scriptExtensions = 'php[0-9]?|phtml|phar|pht|phps|shtml|cgi|pl|py|asp|aspx|jsp';

$forbidden = function (string $message): bool {
    http_response_code(403);
    header('Content-Type: text/plain; charset=utf-8');
    echo $message;

    return true;
};

if (preg_match('#^/uploads/consultation_images(/|$)#i', $checked)) {
    return $forbidden('Forbidden');
}

if (preg_match('#^/uploads/.*\.(' . $scriptExtensions . ')$#i', $checked)) {
    return $forbidden('Forbidden');
}

// A PHP file that is not the front controller is never executed from here.
if (preg_match('#\.(php[0-9]?|phtml|phar|pht|phps)$#i', $checked) && strcasecmp($checked, '/index.php') !== 0) {
    http_response_code(404);
    header('Content-Type: text/plain; charset=utf-8');
    echo 'Not Found';

    return true;
}

// This file allows us to emulate Apache's "mod_rewrite" functionality from the
// built-in PHP web server: real files are served as they are, everything else goes to Laravel.
if ($uri !== '/' && is_file($publicPath . $uri)) {
    return false;
}

require_once $publicPath . '/index.php';
