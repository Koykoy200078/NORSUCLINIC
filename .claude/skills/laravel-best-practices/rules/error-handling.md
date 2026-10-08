# Error Handling Best Practices

## Exception Reporting and Rendering

There are two valid approaches — choose one and apply it consistently across the project.

**Co-location on the exception class** — keeps behavior alongside the exception definition, easier to find:

```php
class InvalidOrderException extends Exception
{
    public function report(): void { /* custom reporting */ }

    public function render(Request $request): Response
    {
        return response()->view('errors.invalid-order', status: 422);
    }
}
```

**Centralized in `app/Exceptions/Handler.php`** (Laravel 10; `bootstrap/app.php` `withExceptions()` is Laravel 11+):

```php
public function register(): void
{
    $this->reportable(function (InvalidOrderException $e) { /* ... */ });
    $this->renderable(function (InvalidOrderException $e, Request $request) {
        return response()->view('errors.invalid-order', [], 422);
    });
}
```

Check the existing codebase and follow whichever pattern is already established (this project already maps status codes
in `Handler.php`).

## Exceptions That Should Never Log

`ShouldntReport` and `dontReportDuplicates()` are Laravel 11+. In Laravel 10 list the class in `$dontReport` in
`app/Exceptions/Handler.php`.

## Throttle High-Volume Exceptions

A single failing integration can flood the logs. In Laravel 10 use `$this->throttle()` in `Handler` only if needed;
this LAN app has no external integrations, so it rarely is.

## Force JSON Error Rendering for API Routes

Laravel auto-detects `Accept: application/json` but API clients may not set it. Explicitly declare JSON rendering for API routes.

```php
$exceptions->shouldRenderJsonWhen(function (Request $request, Throwable $e) {
    return $request->is('api/*') || $request->expectsJson();
});
```

## Add Context to Exception Classes

Attach structured data to exceptions at the source via a `context()` method — Laravel includes it automatically in the log entry.

```php
class InvalidOrderException extends Exception
{
    public function context(): array
    {
        return ['order_id' => $this->orderId];
    }
}
```
