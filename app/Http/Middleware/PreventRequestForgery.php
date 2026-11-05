<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\PreventRequestForgery as Middleware;

/**
 * Laravel 13's name for the forgery check (it was VerifyCsrfToken). A form or AJAX post must carry the session's token; a
 * browser that says `Sec-Fetch-Site: same-origin` is let through without one. Pages opened by an http:// LAN address carry no
 * such header, so there the token decides, exactly as before.
 */
class PreventRequestForgery extends Middleware
{
    /**
     * The URIs that should be excluded from the forgery check.
     *
     * @var array
     */
    protected $except = [
        //
    ];
}
