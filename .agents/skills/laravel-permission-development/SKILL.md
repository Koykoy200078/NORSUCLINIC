---
name: laravel-permission-development
description: Work with roles, permissions and access control in this project (spatie/laravel-permission 5.11 on Laravel 10) — role/permission middleware, the Manage User roles screen, RolePermissionsSeeder, Blade @can checks, role-aware routes and dashboards, and the Staff (Nurse) / Doctor / Clinic Admin model.
---

# Laravel Permission Development (spatie 5.11 — this project)

## When to use this skill

Use it for anything about who may see or do what: roles, permissions, route middleware, menu / button visibility,
Settings › Manage User roles, seeders that grant permissions, and access tests.

## The access model here

- **Roles:** `clinic_admin` (always every permission; locked on the Roles screen), `staff` (shown as **"Staff (Nurse)"**:
  the one role for staff and nurses), `doctor`, `patient` (patients do **not** sign in; read-only on the Roles screen).
  The older `nurse` role is **retired**: the `2026_10_09_170000_unify_staff_nurse_access` migration moves its accounts to
  `staff` and leaves the role in the table (default, hidden from the screen and the staff form, never assigned again).
- **14 permissions** (`manage_patients`, `manage_request_documents`, `manage_medicines`, `manage_doctors`,
  `manage_specialties`, `manage_settings`, `manage_roles`, `manage_staff`, `manage_front_cms`, `manage_countries`,
  `manage_states`, `manage_cities`, `manage_admin_dashboard`, `manage_staff_dashboard`).
- **Only five permissions apply to the Staff (Nurse) and Doctor panels:** `manage_patients` (patients + queue),
  `manage_request_documents` (consultations, certificates, prescriptions, lab requests), `manage_medicines` (inventory,
  dispensing, dispense history), `manage_doctors`, `manage_specialties` (`App\Support\ModuleAccess`). The Roles screen
  offers a role only the permissions that apply to it and the server drops the rest; `manage_admin_dashboard` /
  `manage_staff_dashboard` check nothing.
- **Users get permissions only through their role.** Direct user permissions (`model_has_permissions`) make a later
  revocation in the Roles screen silently ineffective — do not create them (the migration above cleared the existing ones
  of non-admins and `php artisan db:integrity` reports any new ones; the old `fix:dashboard-permissions` command creates
  them and is slated for removal).
- **Each role has its own route file** with the stack `auth`, `checkUserStatus`, `role:<role>` and per-module
  `permission:<name>`: `routes/web.php` (admin, prefix `/admin` + name `admin.` / unprefixed), `routes/staff.php`
  (`/staff`, `staff.`), `routes/doctor.php` (`/doctors`, `doctors.`).
- **Module access = permission, nothing else.** `canUseModule($module)` (`app/helpers.php`, map in
  `App\Support\ModuleAccess::PERMISSIONS`) is the one check for menus, dashboards and buttons and matches the `permission:`
  middleware of the routes. Dashboard, Report Generation and Notifications & Alerts are open to every signed-in staff,
  doctor and admin. The old designation / station layer (`staff.module` middleware, `canStaffAccessModule()`, the
  designation / station maps) was removed in Phase 3; the `staff_profiles` designation / station / shift columns remain
  in the database, hidden and unused.

## Laravel 10 specifics (differs from newer docs)

Middleware aliases are in **`app/Http/Kernel.php`** (not `bootstrap/app.php`), and spatie **5.x** uses the plural
namespace `Middlewares`:

```php
use Spatie\Permission\Middlewares\PermissionMiddleware;
use Spatie\Permission\Middlewares\RoleMiddleware;

protected $middlewareAliases = [
    'role' => RoleMiddleware::class,
    'permission' => PermissionMiddleware::class,
];
```

They are also registered as Livewire **persistent middleware** in `AppServiceProvider`, so Livewire actions re-check them.

## Granting and checking

```php
// Seeders: create with findOrCreate, then grant to ROLES only
Permission::findOrCreate('manage_medicines', 'web');
$role->givePermissionTo('manage_medicines');   // clears the cache via the role's saved event / package methods

// Checks: permission-based
$user->can('manage_medicines');
@can('manage_medicines') ... @endcan
Route::middleware('permission:manage_medicines')->group(...);
```

- **`RolePermissionsSeeder`** is the central authority for **default** role permissions. It applies each default pair
  **once** (remembered in the `default_role_permissions_applied` setting) so a re-seed never undoes what the
  administrator changed in the Roles screen. Add new defaults there; never `syncPermissions([])` a role in a seeder.
- `clinic_admin` always gets every permission (seeder + `RoleRepository::update()` refuses to change it).
- Role names are identifiers the code checks (`role:clinic_admin`, `hasRole('doctor')`); only `display_name` is editable.

## Cache

Permissions are cached 24 h under `spatie.permission.cache` in the **file** cache store, kept in a folder per database
(`config/cache.php`), so test-schema commands cannot poison the real app's cache. After raw DB changes call
`app(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions()`.

## Adding a permission-gated feature

1. Create the permission in a permission seeder with `findOrCreate`.
2. Add it to the roles' defaults in `RolePermissionsSeeder`.
3. Put the routes under `permission:<name>` in **each** role file that exposes them.
4. Gate the menu item, dashboard links and in-page buttons with the same permission (`@can` / `canUseModule()`).
5. Show it on the Roles page only for roles that have a route using it.
6. Test: revoke it in the Roles screen → on the next request the menu item is gone and the routes answer 403; re-grant
   restores both. Run with the real `file` cache store for this test.

## Role-blind link trap

A shared view that hard-codes one role's route name, or an `isRole(...) ? a : b` ternary whose last branch is an admin
URL, sends doctors / staff to a 403. Use `getRouteByRole('base.name', [params])` (second argument must be an array) and
draw buttons only when the user can use that module. Unprefixed route names (`patients.index`, `doctors.show`) are
**admin** routes.
