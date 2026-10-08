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

// The server decodes %XX only ("+" stays "+"), so rawurldecode - not urldecode - is what it will serve.
// REQUEST_URI is a request path. parse_url() would mistake a leading // for a host.
$uri = rawurldecode(explode('?', $_SERVER['REQUEST_URI'], 2)[0]);

// Judge the path the way the server and Windows will RESOLVE it, not the way it was typed: forward slashes only,
// no empty or "." segments, ".." removes the folder before it, and every name loses trailing dots and spaces
// ("consultation_images." is consultation_images, "shell.php. " is shell.php on NTFS) and any alternate data
// stream suffix ("shell.php::$DATA"). Without this, "/uploads/../uploads/consultation_images/x.jpg" or
// "/uploads/./consultation_images/x.jpg" looked different from the protected folder but was served from it.
$segments = [];
foreach (explode('/', str_replace('\\', '/', $uri)) as $segment) {
    $segment = preg_replace('/::.*$/', '', $segment);

    if ($segment !== '.' && $segment !== '..') {
        $segment = rtrim($segment, ". \t");
    }

    if ($segment === '' || $segment === '.') {
        continue;
    }

    if ($segment === '..') {
        array_pop($segments);   // never climbs above the document root
        continue;
    }

    $segments[] = $segment;
}
$checked = '/' . implode('/', $segments);

$scriptExtensions = 'php[0-9]?|phtml|phar|pht|phps|shtml|cgi|pl|py|asp|aspx|jsp';

$forbidden = function (string $message): bool {
    http_response_code(403);
    header('Content-Type: text/plain; charset=utf-8');
    echo $message;

    return true;
};

$refusePath = function (string $path) use ($scriptExtensions, $forbidden): bool {
    if (preg_match('#^/uploads/consultation_images(/|$)#i', $path)) {
        return $forbidden('Forbidden');
    }

    if (preg_match('#^/uploads/.*\.(' . $scriptExtensions . ')$#i', $path)) {
        return $forbidden('Forbidden');
    }

    // A PHP file that is not the front controller is never executed from here.
    if (preg_match('#\.(php[0-9]?|phtml|phar|pht|phps)$#i', $path) && strcasecmp($path, '/index.php') !== 0) {
        http_response_code(404);
        header('Content-Type: text/plain; charset=utf-8');
        echo 'Not Found';

        return true;
    }

    return false;
};

if ($refusePath($checked)) {
    return true;
}

// This file allows us to emulate Apache's "mod_rewrite" functionality from the
// built-in PHP web server: real files are served as they are, everything else goes to Laravel.
if ($checked !== '/' && is_file($publicPath . $checked)) {
    // NTFS accepts directory streams such as consultation_images:$I30:$INDEX_ALLOCATION.
    // Their names survive realpath(), so refuse stream access before serving an existing file.
    if (str_contains($uri, ':')) {
        return $forbidden('Forbidden');
    }

    // realpath() expands Windows short names (CONSUL~1) and follows filesystem aliases.
    // Apply the same rules to the actual file, and never serve an alias outside public/.
    $resolvedPublicPath = realpath($publicPath);
    $resolvedPath = realpath($publicPath . $checked);
    if ($resolvedPublicPath === false || $resolvedPath === false) {
        return $forbidden('Forbidden');
    }

    $publicPrefix = rtrim(str_replace('\\', '/', $resolvedPublicPath), '/') . '/';
    $resolvedPath = str_replace('\\', '/', $resolvedPath);
    $withinPublic = PHP_OS_FAMILY === 'Windows'
        ? strncasecmp($resolvedPath, $publicPrefix, strlen($publicPrefix)) === 0
        : strncmp($resolvedPath, $publicPrefix, strlen($publicPrefix)) === 0;

    if (! $withinPublic) {
        return $forbidden('Forbidden');
    }

    if ($refusePath('/' . substr($resolvedPath, strlen($publicPrefix)))) {
        return true;
    }

    return false;
}

require_once $publicPath . '/index.php';
