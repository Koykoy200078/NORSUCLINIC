---
name: livewire-development
description: "Use for any task or question involving Livewire in this project (Livewire 3.8 on Laravel 10, with the project's own list base App\\Livewire\\Tables\\TableComponent). Activate if the user mentions Livewire, wire: directives (wire:model, wire:click, wire:poll), Livewire tables, lazy components, or a screen built from app/Livewire classes. Covers building and debugging class-based components, tables, tabbed screens, authorization inside actions, loading states and tests. Do not use for the jQuery / AJAX pages (e.g. patient queue) or standard Laravel forms without Livewire."
license: MIT
metadata:
  author: laravel (adapted for Medical University Clinic / NORSUCLINIC, Livewire 3)
---

# Livewire Development (Livewire 3 — this project)

This project runs **Livewire 3.8**, not Livewire 4. There are no single-file / multi-file components, no `⚡` filenames,
no islands, no `Route::livewire()`, no `wire:sort`. Look up the **Livewire 3.x** docs (Context7 library
`/livewire/livewire`, v3) when unsure.

## Project conventions

- **Class-based components only**: class in `app/Livewire/` (flat files; the list base is in `app/Livewire/Tables/`), view in
  `resources/views/livewire/<kebab-name>.blade.php`. Create with `php artisan make:livewire CreatePost` and follow sibling files.
- **List screens** (searchable, sortable, paged tables) extend **`App\Livewire\Tables\TableComponent`**, the project's own base on
  plain Livewire 3 (the old rappasoft table package was removed in the Laravel upgrade: never add a table package). Implement
  `configure()`, `columns()` (`App\Support\Tables\Column`) and `builder()` (or name a `$model` to show all its rows). The base:
  - searches word by word (`App\Support\SearchTerm`: every word must be found in some searchable column), pages 10 / 25 / 50,
    and always ends the sort with the row's own key, so a page never repeats or loses a row;
  - selects only the mapped columns plus `setAdditionalSelects()`, so a label-only column that reads other fields needs them added;
  - left-joins related columns (`'city.name'`) under their relation names; `->sortable(fn ($query, $direction))` and
    `->searchable(fn ($query, $word))` take a callback; `->hideIf()` hides a column but keeps it in the query;
  - is **not** lazy by itself: put `#[Lazy]` on the list class (Livewire reads the class's own attributes, not its parent's);
  - has its configuration setters `protected` (a browser can call public methods): keep new helpers protected too.
  Every list has a recorded screen in `tests/Fixtures/tables/*.json` (`TableGoldenMasterTest`): a new list gets its own, and a change
  to the base must keep all of them passing.
- **Tabbed screens** (e.g. `MedicineScreen`, `MedicineDispensingScreen`): mount child tables with `:lazy="false"` and a
  `key`, otherwise lazy children are destroyed on tab switch. Report Generation keeps `$tab` as state with `setTab()`.
- Layout `resources/views/layouts/app.blade.php`: `@livewireStyles` / `@livewireScripts` (assets not auto-injected:
  `inject_assets => false`); full-page layout `components.layouts.app`. Page JavaScript must be in `@push('scripts')` or
  `@section('page_js')` — `@section('scripts')` is never output.
- **jQuery ↔ Livewire bridge:** older pages read hidden `html()->hidden(...)` inputs from compiled `resources/assets/js/*`
  files. Before removing a hidden input, search `resources/assets/js/<module>` for its id.

## Security inside components

- Livewire re-applies only **persistent middleware** on `/livewire/update`. `AppServiceProvider` registers
  `CheckUserStatus` and Spatie's `RoleMiddleware` / `PermissionMiddleware` (namespace `Spatie\Permission\Middleware`,
  singular, in spatie 6.x) as persistent; `ConsultationAccessTest` checks the list against the `role` / `permission` aliases.
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
- Events: `$this->dispatch('name')` (v3) and `#[On('name')]` listeners, never the `$listeners` property (a class-level `#[On('refresh')]` is the `$refresh` action and is inherited; a list that resets its page on `resetPage` uses the `ResetsPageOnEvent` trait; every list's events are pinned by `LivewireListenersTest`).
- `#[Url]` for state kept in the query string (validate it in `mount()`; see `MedicineDispensingScreen`).
- Real-time (Phase 7, done): no `wire:poll` and no `setInterval` reloads. A committed write to a clinic table is announced
  (`App\Support\Realtime`, table -> module) and the open screens redraw: tables and components listen for `refresh`
  (a class-level `#[On('refresh')]` on a non-table component), plain pages listen for the window event
  `clinic:live-refresh`. A new table needs a row in `Realtime::TABLES`; a new page a row in `Realtime::PAGES`. Never make a page
  write on read (it would refresh itself for ever: `test_opening_a_screen_announces_nothing`). Do not add `wire:poll` timers.

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
