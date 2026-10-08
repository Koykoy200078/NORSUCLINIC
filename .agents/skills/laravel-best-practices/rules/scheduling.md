# Task Scheduling Best Practices

## Use `withoutOverlapping()` on Variable-Duration Tasks

Without it, a long-running task spawns a second instance on the next tick, causing double-processing or resource exhaustion.

## Use `onOneServer()` on Multi-Server Deployments

Without it, every server runs the same task simultaneously. Requires a shared cache driver (Redis, database, Memcached).

## Use `runInBackground()` for Concurrent Long Tasks

By default, tasks at the same tick run sequentially. A slow first task delays all subsequent ones. `runInBackground()` runs them as separate processes.

## Use `environments()` to Restrict Tasks

Prevent accidental execution of production-only tasks on a development copy.

In Laravel 10 tasks live in `app/Console/Kernel.php` (`Schedule::` in `routes/console.php` is Laravel 11+):

```php
protected function schedule(Schedule $schedule): void
{
    $schedule->command('db:backup')->hourly()->withoutOverlapping();
}
```

## Use `takeUntilTimeout()` for Time-Bounded Processing

A task that processes an unbounded cursor can overlap with the next run. Bound execution time.

## No Schedule Groups

`Schedule::...->group()` is Laravel 11+. Repeat the few modifiers per task in `Kernel::schedule()`.

**This project:** the scheduler only runs when Windows Task Scheduler calls `php artisan schedule:run` every minute (see the
startup script); there is one server, so `onOneServer()` is unnecessary.
