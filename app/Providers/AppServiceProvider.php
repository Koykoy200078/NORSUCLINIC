<?php

namespace App\Providers;

use Illuminate\Support\Facades\URL;
use Livewire\Livewire;
use Opcodes\LogViewer\Facades\LogViewer;
use Illuminate\Support\Facades\Schema;
use Illuminate\Pagination\Paginator;
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
        Schema::defaultStringLength(191);

        // Bind the Laravel JS Localization command into the app IOC.
        $this->app->singleton('localization.js', function ($app) {
            $app = $this->app;
            $laravelMajorVersion = (int) \Illuminate\Foundation\Application::VERSION;

            $files = $app['files'];

            if ($laravelMajorVersion === 4) {
                $langs = $app['path.base'] . '/app/lang';
            } elseif ($laravelMajorVersion >= 5 && $laravelMajorVersion < 9) {
                $langs = $app['path.base'] . '/resources/lang';
            } elseif ($laravelMajorVersion >= 9) {
                $langs = app()->langPath();
            }
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

        // The clinic runs on a private LAN over plain HTTP. The URL scheme is therefore decided by an
        // explicit switch (config('app.force_https') <- FORCE_HTTPS), never by APP_ENV: forcing
        // https in "production" made every form action, asset and redirect point at https://, which
        // the LAN server does not answer, so the app was unusable after deployment. The old code also
        // trusted the client-supplied Host header ("ngrok") to switch to https. P2-C1 / M-10.
        if (config('app.force_https')) {
            URL::forceScheme('https');
            request()->server->set('HTTPS', 'on');
        }

        // Livewire re-runs only "authentication" style middleware on /livewire/update. Add the
        // account-status and role/permission checks that guarded the page, so a user who was
        // disabled - or whose role changed - after the page loaded cannot keep acting through a
        // component action. (Staff-module checks are not persistent: they read the page's query
        // string, which a Livewire update request does not have; sensitive actions authorize
        // inside the component instead.) P2-H3.
        Livewire::addPersistentMiddleware([
            \App\Http\Middleware\CheckUserStatus::class,
            \Spatie\Permission\Middleware\RoleMiddleware::class,
            \Spatie\Permission\Middleware\PermissionMiddleware::class,
        ]);

        // The Log Viewer package serves application logs (patient names, queries, errors) and can
        // delete them. Without a callback it authorized EVERYONE, including anonymous LAN clients.
        // C-05. Only the clinic administrator may open it.
        LogViewer::auth(function ($request) {
            $user = $request->user();

            return $user !== null && $user->hasRole('clinic_admin');
        });
    }
}
