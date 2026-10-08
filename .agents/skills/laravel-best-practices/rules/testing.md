# Testing Best Practices

**This project uses PHPUnit 9 classes (not Pest)** in `tests/Feature` and `tests/Unit`. Shared fixtures live in
`tests/Concerns/BuildsClinicData.php`; regression tests for fixed bugs go in `tests/Feature/Regression/`. The suite runs
on its own throwaway MySQL schema (`norsu_clinic_test`) and must never run against `norsu_clinic`
(`tests/CreatesApplication.php` aborts otherwise). Never run two phpunit processes at the same time.

## Use `LazilyRefreshDatabase` Over `RefreshDatabase`

`RefreshDatabase` migrates once per process and wraps each test in a rolled-back transaction. `LazilyRefreshDatabase` skips even that first migration if the schema is already up to date. Follow what the neighbouring test classes use.

## Use Model Assertions Over Raw Database Assertions

Incorrect: `$this->assertDatabaseHas('users', ['id' => $user->id]);`

Correct: `$this->assertModelExists($user);`

More expressive, type-safe, and fails with clearer messages.

## Use Factory States and Sequences

Named states make tests self-documenting. Sequences eliminate repetitive setup.

Incorrect: `User::factory()->create(['email_verified_at' => null]);`

Correct: `User::factory()->unverified()->create();`

## Asserting Exception Reporting (Laravel 10)

`Exceptions::fake()` is Laravel 11+. In Laravel 10 use `$this->withoutExceptionHandling()` and `expectException()`, or
assert the response status / flash message the user sees.

## Call `Event::fake()` After Factory Setup

Model factories rely on model events (e.g., `creating` to generate UUIDs). Calling `Event::fake()` before factory calls silences those events, producing broken models.

Incorrect: `Event::fake(); $user = User::factory()->create();`

Correct: `$user = User::factory()->create(); Event::fake();`

## Use `recycle()` to Share Relationship Instances Across Factories

Without `recycle()`, nested factories create separate instances of the same conceptual entity.

```php
Ticket::factory()
    ->recycle(Airline::factory()->create())
    ->create();
```
