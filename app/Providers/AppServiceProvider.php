<?php

namespace App\Providers;

use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;
use Mariuzzo\LaravelJsLocalization\Commands\LangJsCommand;
use Mariuzzo\LaravelJsLocalization\Generators\LangJsGenerator;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        if (env('APP_ENV') === 'production') {
            URL::forceScheme('https');
        } else {
            URL::forceScheme('http');
        }


        // Bind the Laravel JS Localization command into the app IOC.
        $this->app->singleton('localization.js', function ($app) {
            $laravelMajorVersion = (int) $app->version();
            $files = $app['files'];

            $langs = match (true) {
                $laravelMajorVersion === 4 => $app['path.base'] . '/app/lang',
                $laravelMajorVersion >= 5 && $laravelMajorVersion < 9 => $app['path.base'] . '/resources/lang',
                $laravelMajorVersion >= 9 => app()->langPath(),
                default => throw new \RuntimeException('Unsupported Laravel version'),
            };

            $messages = $app['config']->get('localization-js.messages');
            $generator = new LangJsGenerator($files, $langs, $messages);

            return new LangJsCommand($generator);
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Paginator::useBootstrap();
    }
}
