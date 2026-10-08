---
name: livewire-development
description: "Use for any task or question involving Livewire in this project (Livewire 3.8 on Laravel 10, with rappasoft/laravel-livewire-tables 3.2). Activate if the user mentions Livewire, wire: directives (wire:model, wire:click, wire:poll), Livewire tables, lazy components, or a screen built from app/Livewire classes. Covers building and debugging class-based components, tables, tabbed screens, authorization inside actions, loading states and tests. Do not use for the jQuery / AJAX pages (e.g. patient queue) or standard Laravel forms without Livewire."
license: MIT
metadata:
  author: laravel (adapted for Medical University Clinic / NORSUCLINIC, Livewire 3)
---

# Livewire Development (Livewire 3 — this project)

This project runs **Livewire 3.8**, not Livewire 4. There are no single-file / multi-file components, no `⚡` filenames,
no islands, no `Route::livewire()`, no `wire:sort`. Look up the **Livewire 3.x** docs (Context7 library
`/livewire/livewire`, v3) when unsure.

## Project conventions

- **Class-based components only**: class in `app/Livewire/` (42 flat files, no sub-folders), view in
  `resources/views/livewire/<kebab-name>.blade.php`. Create with `php artisan make:livewire CreatePost` and follow sibling files.
- **Data tables** use rappasoft/laravel-livewire-tables **3.2** (held back: 3.8 breaks the customised table views).
  Every table extends **`App\Livewire\LivewireTableComponent`**, never `DataTableComponent` directly. That base:
  - is `#[Lazy]`;
  - sets search debounce 500 ms and per-page 10 / 25 / 50;
  - uses `Concerns\SearchesByWords` (word search through `App\Support\SearchTerm`).
  Implement `configure()`, `columns()` and `builder()`. Rappasoft 3 only selects mapped columns, so label-only columns need `setAdditionalSelects()`.
- **Tabbed screens** (e.g. `MedicineScreen`, `MedicineDispensingScreen`): mount child tables with `:lazy="false"` and a
  `key`, otherwise lazy children are destroyed on tab switch. Report Generation keeps `$tab` as state with `setTab()`.
- Layout `resources/views/layouts/app.blade.php`: `@livewireStyles` / `@livewireScripts` (assets not auto-injected:
  `inject_assets => false`); full-page layout `components.layouts.app`. Page JavaScript must be in `@push('scripts')` or
  `@section('page_js')` — `@section('scripts')` is never output.
- **jQuery ↔ Livewire bridge:** older pages read hidden `Form::hidden(...)` inputs from compiled `resources/assets/js/*`
  files. Before removing a hidden input, search `resources/assets/js/<module>` for its id.

## Security inside components

- Livewire re-applies only **persistent middleware** on `/livewire/update`. `AppServiceProvider` registers
  `CheckUserStatus` and Spatie's `RoleMiddleware` / `PermissionMiddleware` (namespace `Spatie\Permission\Middlewares`,
  plural, in spatie 5.x) as persistent.
- Query-string based checks are **not** re-run on updates. **Authorize inside every action** (`abort_unless(...)`) and
  scope every `builder()` to what the user may see.
- Public properties are user-editable: validate them, never trust ids sent back from the browser.

## Basic usage

```php
namespace App\Livewire;

use Livewire\Component;

class Counter extends Component
{
    public int $count = 0;

    public function increment(): void
    {
        $this->count++;
    }

    public function render()
    {
        return view('livewire.counter');
    }
}
```

## Best practices

- `wire:key` on every element rendered in a loop.
- `wire:loading` / `wire:target` for loading states; `wire:model.live` only where instant updates are needed (default is deferred in v3).
- Events: `$this->dispatch('name')` (v3) and `#[On('name')]` listeners; tables listen for `refresh`.
- `#[Url]` for state kept in the query string (validate it in `mount()`; see `MedicineDispensingScreen`).
- Real-time: today Report Generation uses `wire:poll.30s`; the plan (`docs/audit-plan-2026-10-08.md` Phase 7) replaces
  polling with Laravel Reverb broadcasts. Do not add new `wire:poll` timers.

## Testing (PHPUnit)

```php
use Livewire\Livewire;

public function test_counter_increments(): void
{
    Livewire::test(Counter::class)
        ->assertSet('count', 0)
        ->call('increment')
        ->assertSet('count', 1);
}
```

Lazy tables in feature tests: the link crawler (`tests/Feature/Audit/LinkCrawlTest.php`) shows how to load `#[Lazy]`
tables by replaying `POST /livewire/update` with `__lazyLoad`.

## Verification

1. Browser console: no JavaScript errors.
2. Network tab: Livewire requests return 200 (a 403 means a persistent middleware or an action check refused it).
3. `wire:key` on every `@foreach`.

## Common pitfalls

- Missing `wire:key` in loops → wrong rows re-rendered.
- A child table inside a tab without `:lazy="false"` → blank table after switching tabs.
- Checking access only in the page route → anyone can still call the component's actions.
- Including Alpine separately — it is bundled with Livewire 3.
- Following Livewire 4 examples (SFC, `⚡`, `Route::livewire`, islands) — not available here.

For JavaScript hooks see [reference/javascript-hooks.md](reference/javascript-hooks.md) (written for Livewire 4; in v3
use `Livewire.hook('commit', …)` / `Livewire.hook('request', …)` as documented for v3).
