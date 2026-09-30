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

        // Ignore a stored value the app has no translation for (older sessions may hold one).
        if (! is_string($localeLanguage) || ! array_key_exists($localeLanguage, \App\Models\User::LANGUAGES)) {
            $localeLanguage = null;
        }

        if (! isset($localeLanguage)) {
            $language = getSettingValue('language') ?? config('app.locale');
            App::setLocale($language);
        } else {
            App::setLocale($localeLanguage);
        }

        return $next($request);
    }
}
