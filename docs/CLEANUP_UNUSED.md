# CLEANUP: WebSockets & Other Unused Code

**Phase:** 5 (independent — can run alongside Phase 2 or 3)  
**Status:** ⬜ Not started

---

## SCOPE

Remove WebSockets infrastructure and any remaining unused code not covered by other tracker files.  
Education models (Campus, College, Course, YearLevel, Office, Department, Guest) are **KEPT** per updated decision.

---

## PART A — WEBSOCKETS

### Composer Packages to Remove

```bash
composer remove beyondcode/laravel-websockets pusher/pusher-php-server
```

> Run AFTER removing all references from PHP files, config, and JS.

### Config Files to Delete / Clean

| File                      | Action                                                  | Status |
| ------------------------- | ------------------------------------------------------- | ------ |
| `config/websockets.php`   | ⬜ Delete entire file                                   |
| `config/broadcasting.php` | ⬜ Remove Pusher driver config; keep `null` driver only |

### ENV Variables to Remove

Remove from `.env` and `.env.example`:

```
PUSHER_APP_ID=
PUSHER_APP_KEY=
PUSHER_APP_SECRET=
PUSHER_APP_CLUSTER=
PUSHER_HOST=
PUSHER_PORT=
MIX_PUSHER_APP_KEY=
MIX_PUSHER_APP_CLUSTER=
```

### `webpack.mix.js` Changes

Remove Pusher / Echo setup (if present):

```js
// Remove lines like:
require("./echo");
// or
import Echo from "laravel-echo";
import Pusher from "pusher-js";
window.Pusher = Pusher;
```

### `resources/js/` Files to Check

| File                               | Action                             | Status |
| ---------------------------------- | ---------------------------------- | ------ |
| `resources/js/echo.js` (if exists) | ⬜ Delete                          |
| `resources/js/bootstrap.js`        | ⬜ Remove Pusher/Echo import block |

### `routes/channels.php`

- Check for any channel definitions referencing removed models (appointments, visits, etc.)
- Empty or delete if no remaining channels defined

### `app/Events/` Directory

Review all event classes — remove any that:

- Implement `ShouldBroadcast`
- Reference removed models (Appointment, Visit, Transaction)

### `app/Providers/BroadcastServiceProvider.php`

Check if it only registers channels. If unused after removal, it can be left (it's harmless) or removed from `config/app.php` providers list.

---

## PART B — LOG VIEWER (DUPLICATE / UNUSED)

A log viewer package was flagged as potentially duplicated:

| Package                          | Status                              |
| -------------------------------- | ----------------------------------- |
| `rap2hpoutre/laravel-log-viewer` | ⬜ Verify if used and remove if not |

Check `routes/web.php` or `routes/debug-profile.php` for:

```php
Route::get('log-viewer', [\Rap2hpoutre\LaravelLogViewer\LogViewerController::class, 'index']);
```

If present and not actively used, remove the route and run:

```bash
composer remove rap2hpoutre/laravel-log-viewer
```

---

## PART C — DEBUG & UPGRADE ROUTES

### `routes/debug-profile.php`

- Contains profiling/debug routes that should NOT be in production
- Review and remove or restrict to `APP_DEBUG=true` guard:
    ```php
    if (app()->environment('local') || config('app.debug')) {
        // debug routes
    }
    ```

### `routes/upgrade.php`

- Contains upgrade/migration helper routes
- Verify if still needed; if not, remove references from `routes/api.php` or `RouteServiceProvider`

---

## PART D — MISCELLANEOUS UNUSED CODE

### `debug/` Directory

The root `debug/` directory contains PHP debug scripts:

- `check-consultation-data.php`
- `deep-scan-consultation-data.php`
- `simulate-livewire-query.php`
- `test_patient_cascade_delete.php`
- `test-livewire-logic.php`
- `test-new-approach.php`

These are development scripts, not part of the application. They do not affect production but can be removed for cleanliness.

> 🗒️ These can be cleaned up anytime without risk.

### `app/Http/Controllers/UserController.php` — Debug Log Lines

Remove debug logging block in `editProfile()` method (lines 272–279):

```php
\Illuminate\Support\Facades\Log::debug('Edu fields debug', [
    'department_id' => ...,
    'office_id' => ...,
    'college_id' => ...,
]);
```

---

## COMPLETION CHECKLIST

### WebSockets

- [ ] Run `composer remove beyondcode/laravel-websockets pusher/pusher-php-server`
- [ ] Delete `config/websockets.php`
- [ ] Clean `config/broadcasting.php` (remove Pusher driver, keep null)
- [ ] Remove Pusher/Echo from `webpack.mix.js`
- [ ] Remove Pusher/Echo from `resources/js/bootstrap.js` or `echo.js`
- [ ] Clean `.env` and `.env.example` of Pusher variables
- [ ] Review `routes/channels.php` — remove unused channels
- [ ] Review `app/Events/` — remove ShouldBroadcast events for removed features

### Log Viewer

- [ ] Verify `rap2hpoutre/laravel-log-viewer` usage
- [ ] If unused: remove route + `composer remove rap2hpoutre/laravel-log-viewer`

### Debug Routes

- [ ] Review `routes/debug-profile.php` — restrict or remove
- [ ] Review `routes/upgrade.php` — remove if no longer needed

### Miscellaneous

- [ ] Remove debug log lines from `UserController::editProfile()` (lines 272–279)
- [ ] Optional: Remove `debug/` directory from project root

---

_Phase 5 — Independent. No prerequisites._
