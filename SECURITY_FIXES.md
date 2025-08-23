# Security Fixes Required

## 1. SQL Injection Vulnerabilities (HIGH PRIORITY)

### Current Issues:

-   Raw SQL in Livewire tables using `whereRaw` with unescaped user input
-   Examples found in:
    -   `app/Livewire/AppointmentTable.php` line 148
    -   `app/Livewire/DoctorScheduleTable.php` line 53
    -   Multiple other Livewire tables

### Fix Required:

Replace all instances of:

```php
$q->whereRaw("TRIM(CONCAT(first_name,' ',last_name,' ')) like '%{$direction}%'");
```

With parameterized queries:

```php
$q->whereRaw("TRIM(CONCAT(first_name, ?, last_name, ?)) LIKE ?", [' ', ' ', "%{$direction}%"]);
```

Or better yet, use Eloquent methods:

```php
$q->where(DB::raw("CONCAT(first_name, ' ', last_name)"), 'LIKE', "%{$direction}%");
```

## 2. XSS Protection Issues

### Current Issue:

XSS middleware strips all HTML tags but may not handle all edge cases properly.

### Improvements Needed:

-   Use HTMLPurifier for better XSS protection
-   Implement proper output escaping in Blade templates
-   Add Content Security Policy headers

## 3. Missing Authorization Checks

### Issues Found:

-   Some controllers don't properly check user permissions
-   Direct object reference vulnerabilities possible

### Fixes Required:

-   Implement proper authorization policies
-   Add middleware checks for all sensitive routes
-   Use Laravel Gates and Policies consistently
