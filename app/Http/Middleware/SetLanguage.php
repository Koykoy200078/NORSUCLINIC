<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\App;

class SetLanguage
{
    public function handle(Request $request, Closure $next): Response
    {
        $localeLanguage = Session::get('languageName');

        if (! isset($localeLanguage)) {
            $language = getSettingValue('language') ?? config('app.locale');
            App::setLocale($language);
        } else {
            App::setLocale($localeLanguage);
        }

        return $next($request);
    }
}
