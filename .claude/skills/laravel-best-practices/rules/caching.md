# Caching Best Practices

## Use `Cache::remember()` Instead of Manual Get/Put

Cleaner cache-aside pattern that removes boilerplate. use `Cache::lock()` for race conditions.

Incorrect:
```php
$val = Cache::get('stats');
if (! $val) {
    $val = $this->computeStats();
    Cache::put('stats', $val, 60);
}
```

Correct:
```php
$val = Cache::remember('stats', 60, fn () => $this->computeStats());
```

## Stale-While-Revalidate and Request Memoization (Laravel 10)

`Cache::flexible()` (Laravel 11) and `Cache::memo()` (Laravel 12) exist in the framework, but **this project does not use them**. Use
`Cache::remember()` with a short TTL, and keep a value read many times in one request in a local variable or a
property of the service (for example `SettingsService` already caches all settings in one array).

## Cache Tags Are Not Available Here

Tags only work with `redis`, `memcached` and `dynamodb`. This project uses the **`file`** cache store (one folder per
database, see `config/cache.php`), so `Cache::tags()` throws. Use explicit keys and forget each one, as the code already
does for the dashboard counters (`livewire_staff_dashboard_<date>` …).

## Use `Cache::add()` for Atomic Conditional Writes

`add()` only writes if the key does not exist — atomic, no race condition between checking and writing.

Incorrect: `if (! Cache::has('lock')) { Cache::put('lock', true, 10); }`

Correct: `Cache::add('lock', true, 10);`

## Per-Request Memoization (Laravel 10)

`once()` arrived in Laravel 11 and **does not exist here**. Memoize in a property:

```php
private ?Collection $roles = null;

public function roles(): Collection
{
    return $this->roles ??= $this->loadRoles();
}
```

## No Failover Cache Store

The `failover` cache driver exists in the framework. This project runs one `file` store on a single clinic PC; nothing to fail over to.

## Cache Only Arrays and Scalars (Laravel 13)

`config/cache.php` sets `serializable_classes` to `false`: the cache does not turn a stored object back into an object (a Collection, a model, a Carbon date comes back unusable). Put `->all()` / `->toArray()` into the cache and rebuild the Collection after reading (`collect(Cache::remember(...))`). `Laravel13ReadinessTest` fails if a list cache holds an object.
