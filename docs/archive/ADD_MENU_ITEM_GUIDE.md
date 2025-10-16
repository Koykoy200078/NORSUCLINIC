# Adding Activity Logs to Navigation Menu

## Quick Guide: Add Activity Logs Menu Item

### Step 1: Locate the Admin Sidebar Menu File

The sidebar menu is typically located in one of these files:

-   `resources/views/layouts/sidebar.blade.php`
-   `resources/views/layouts/menu.blade.php`
-   `resources/views/layouts/app.blade.php`
-   `resources/views/partials/sidebar.blade.php`

### Step 2: Add Menu Item

Add this menu item code in the appropriate section (usually with other admin menu items):

```html
<!-- Activity Logs Menu Item -->
<li class="nav-item">
    <a
        href="{{ route('activity-logs.index') }}"
        class="nav-link {{ Request::is('admin/activity-logs*') ? 'active' : '' }}"
    >
        <i class="fas fa-clipboard-list nav-icon"></i>
        <p>Activity Logs</p>
    </a>
</li>
```

### Alternative Icon Options

Choose your preferred icon:

```html
<!-- Option 1: Clipboard List -->
<i class="fas fa-clipboard-list nav-icon"></i>

<!-- Option 2: History -->
<i class="fas fa-history nav-icon"></i>

<!-- Option 3: List -->
<i class="fas fa-list-alt nav-icon"></i>

<!-- Option 4: Tasks -->
<i class="fas fa-tasks nav-icon"></i>

<!-- Option 5: File -->
<i class="fas fa-file-alt nav-icon"></i>
```

### Example: Full Sidebar Section

```html
<!-- Settings & Management Section -->
<li class="nav-header">MANAGEMENT</li>

<!-- Patients -->
<li class="nav-item">
    <a href="{{ route('patients.index') }}" class="nav-link">
        <i class="fas fa-user-injured nav-icon"></i>
        <p>Patients</p>
    </a>
</li>

<!-- Request Documents -->
<li class="nav-item">
    <a href="{{ route('request-documents.index') }}" class="nav-link">
        <i class="fas fa-file-medical nav-icon"></i>
        <p>Request Documents</p>
    </a>
</li>

<!-- Activity Logs (NEW) -->
<li class="nav-item">
    <a
        href="{{ route('activity-logs.index') }}"
        class="nav-link {{ Request::is('admin/activity-logs*') ? 'active' : '' }}"
    >
        <i class="fas fa-clipboard-list nav-icon"></i>
        <p>Activity Logs</p>
    </a>
</li>

<!-- Settings -->
<li class="nav-item">
    <a href="{{ route('setting.index') }}" class="nav-link">
        <i class="fas fa-cog nav-icon"></i>
        <p>Settings</p>
    </a>
</li>
```

### If Using Submenu/Accordion

If your menu uses submenus (like AdminLTE), use this format:

```html
<li class="nav-item has-treeview">
    <a href="#" class="nav-link">
        <i class="nav-icon fas fa-database"></i>
        <p>
            Reports & Logs
            <i class="right fas fa-angle-left"></i>
        </p>
    </a>
    <ul class="nav nav-treeview">
        <li class="nav-item">
            <a href="{{ route('activity-logs.index') }}" class="nav-link">
                <i class="fas fa-clipboard-list nav-icon"></i>
                <p>Activity Logs</p>
            </a>
        </li>
        <li class="nav-item">
            <a href="/admin/logs" class="nav-link">
                <i class="fas fa-bug nav-icon"></i>
                <p>System Logs</p>
            </a>
        </li>
    </ul>
</li>
```

### Adding Badge (Shows Count)

Want to show the number of today's activities?

```html
<li class="nav-item">
    <a href="{{ route('activity-logs.index') }}" class="nav-link">
        <i class="fas fa-clipboard-list nav-icon"></i>
        <p>
            Activity Logs @php $todayLogs =
            \App\Models\ActivityLog::whereDate('created_at', today())->count();
            @endphp @if($todayLogs > 0)
            <span class="badge badge-info right">{{ $todayLogs }}</span>
            @endif
        </p>
    </a>
</li>
```

### For Role-Based Access

If you want to show menu only to clinic_admin:

```html
@if(auth()->user()->hasRole('clinic_admin'))
<li class="nav-item">
    <a href="{{ route('activity-logs.index') }}" class="nav-link">
        <i class="fas fa-clipboard-list nav-icon"></i>
        <p>Activity Logs</p>
    </a>
</li>
@endif
```

### Bootstrap-based Menu

If using Bootstrap navbar:

```html
<li class="nav-item">
    <a class="nav-link" href="{{ route('activity-logs.index') }}">
        <i class="fas fa-clipboard-list"></i> Activity Logs
    </a>
</li>
```

### Tailwind-based Menu

If using Tailwind CSS:

```html
<a
    href="{{ route('activity-logs.index') }}"
    class="flex items-center px-4 py-2 text-gray-700 hover:bg-gray-100"
>
    <svg class="w-5 h-5 mr-3" fill="currentColor" viewBox="0 0 20 20">
        <path d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9z" />
        <path
            fill-rule="evenodd"
            d="M4 5a2 2 0 012-2 3 3 0 003 3h2a3 3 0 003-3 2 2 0 012 2v11a2 2 0 01-2 2H6a2 2 0 01-2-2V5zm3 4a1 1 0 000 2h.01a1 1 0 100-2H7zm3 0a1 1 0 000 2h3a1 1 0 100-2h-3zm-3 4a1 1 0 100 2h.01a1 1 0 100-2H7zm3 0a1 1 0 100 2h3a1 1 0 100-2h-3z"
            clip-rule="evenodd"
        />
    </svg>
    Activity Logs
</a>
```

---

## Quick Find: Common Sidebar File Locations

### AdminLTE Theme

```
resources/views/layouts/sidebar.blade.php
resources/views/layouts/menu.blade.php
```

### Custom Laravel

```
resources/views/partials/sidebar.blade.php
resources/views/layouts/navigation.blade.php
```

### Livewire

```
resources/views/livewire/navigation.blade.php
```

---

## Testing the Menu

1. Save the sidebar file
2. Clear view cache: `php artisan view:clear`
3. Refresh your browser
4. Look for "Activity Logs" in the sidebar
5. Click it to navigate to `/admin/activity-logs`

---

## Styling Tips

### Active State

Make the menu item highlight when on activity logs page:

```html
class="nav-link {{ Request::is('admin/activity-logs*') ? 'active' : '' }}"
```

### Custom Color

If you want a specific color for this menu item:

```html
<a href="{{ route('activity-logs.index') }}" class="nav-link text-success">
    <i class="fas fa-clipboard-list nav-icon text-success"></i>
    <p>Activity Logs</p>
</a>
```

---

## Need Help Finding Your Sidebar?

Search your project for these terms:

```bash
# In VS Code, use Ctrl+Shift+F and search for:
"Request Documents"
"Patients"
"nav-item"
"sidebar"
```

The file containing these will be your sidebar menu file!

---

## Summary

✅ Choose your preferred icon
✅ Add menu item code to sidebar
✅ Clear cache
✅ Test navigation

**Recommended Placement**: Between "Request Documents" and "Settings" in the admin menu.
