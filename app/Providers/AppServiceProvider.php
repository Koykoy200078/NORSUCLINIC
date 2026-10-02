<?php

namespace App\Providers;

use App\Models\IllnessSystem;
use App\Models\ServiceType;
use App\Support\AuditLog;
use Illuminate\Auth\Events\Failed;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;
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

    private function recordAttempt(string $action, string $description, ?string $email, ?object $user): void
    {
        try {
            AuditLog::recordAttempt($action, $description, $email, $user instanceof \App\Models\User ? $user : null);
        } catch (\Throwable $e) {
            report($e);
        }
    }

    private function recordSession(string $action, string $description, object $event): void
    {
        if (! $event->user) {
            return;
        }

        try {
            AuditLog::record($action, $description, ['subject_type' => get_class($event->user), 'subject_id' => $event->user->getAuthIdentifier()]);
        } catch (\Throwable $e) {
            report($e);
        }
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Paginator::useBootstrap();

        // Who signed in and out, and from where. (Failed attempts are rate limited and not stored.) R3-M5.
        Event::listen(Login::class, fn (Login $event) => $this->recordSession('login', 'Signed in', $event));
        Event::listen(Logout::class, fn (Logout $event) => $this->recordSession('logout', 'Signed out', $event));
        Event::listen(Failed::class, function (Failed $event) {
            $this->recordAttempt('login_failed', 'Failed sign-in attempt', $event->credentials['email'] ?? null, $event->user);
        });
        Event::listen(Lockout::class, function (Lockout $event) {
            $this->recordAttempt('login_locked_out', 'Sign-in blocked: too many attempts', (string) $event->request->input('email'), null);
        });

        // The clinic runs on a private LAN over plain HTTP. The URL scheme is therefore decided by an
        // explicit switch (config('app.force_https') <- FORCE_HTTPS), never by APP_ENV: forcing
        // https in "production" made every form action, asset and redirect point at https://, which
        // the LAN server does not answer, so the app was unusable after deployment. The old code also
        // trusted the client-supplied Host header ("ngrok") to switch to https. P2-C1 / M-10.
        if (config('app.force_https')) {
            URL::forceScheme('https');
            request()->server->set('HTTPS', 'on');
        }

        // The illness / services lists of the ACCOMPLISHMENT REPORT, shown on every consultation form.
        View::composer('document_issuances.components.classification_picker', function ($view) {
            $view->with('illnessSystems', IllnessSystem::query()->ordered()->with('illnesses')->get());

            $view->with('serviceGroups', ServiceType::query()->ordered()->get()
                ->groupBy('category')
                ->mapWithKeys(fn ($services, $category) => [ServiceType::CATEGORY_LABELS[$category] ?? $category => $services]));
        });

        // Livewire re-runs only "authentication" style middleware on /livewire/update. Add the
        // account-status and role/permission checks that guarded the page, so a user who was
        // disabled - or whose role changed - after the page loaded cannot keep acting through a
        // component action. (Staff-module checks are not persistent: they read the page's query
        // string, which a Livewire update request does not have; sensitive actions authorize
        // inside the component instead.) P2-H3.
        Livewire::addPersistentMiddleware([
            \App\Http\Middleware\CheckUserStatus::class,
            // The installed spatie/laravel-permission (5.x) keeps these in "Middlewares" (plural); the
            // singular namespace does not exist, so they were never matched and never re-applied. R3-H6.
            \Spatie\Permission\Middlewares\RoleMiddleware::class,
            \Spatie\Permission\Middlewares\PermissionMiddleware::class,
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
