# BLADE LAYOUT & ROUTE CONFLICT ANALYSIS

## 🔍 Issues Identified

### 1. **Menu Route Conflicts**

-   Staff users see admin-prefixed routes (`admin/staffs*`, `admin/doctors*`, etc.)
-   No staff dashboard menu item exists
-   All management routes hardcoded to `/admin/*` prefix
-   Permission conflicts when staff users access admin routes

### 2. **Layout Structure Issues**

-   **menu.blade.php**: Hardcoded admin routes for shared permissions
-   **sub_menu.blade.php**: No staff route alternatives
-   **sidebar.blade.php**: Missing staff role handling
-   Templates don't use role-based route helpers

### 3. **Permission-Route Mismatches**

-   `manage_patients` permission routes to `admin/patients*`
-   `manage_appointments` permission routes to `admin/appointments*`
-   `manage_services` permission routes to `admin/services*`
-   Staff users have permissions but wrong route prefixes

### 4. **Missing Components**

-   No staff-specific Livewire components
-   No staff dashboard breadcrumbs
-   No staff menu highlighting logic

## 🛠️ Refactoring Strategy

### Phase 1: Create Role-Based Menu Components

1. Create separate menu partials for each role
2. Add staff dashboard menu items
3. Implement dynamic route resolution

### Phase 2: Update Layout Logic

1. Add role-based menu inclusion
2. Update navigation highlighting
3. Fix breadcrumb generation

### Phase 3: Template Route Updates

1. Replace hardcoded admin routes with helpers
2. Add role-based route resolution
3. Update all action buttons and links

### Phase 4: Permission Structure Alignment

1. Ensure permissions match available routes
2. Add missing staff permissions
3. Validate route accessibility

## 📋 Files Requiring Updates

### Layout Files

-   ✅ `layouts/menu.blade.php` - Add staff routes
-   ✅ `layouts/sub_menu.blade.php` - Add staff navigation
-   ✅ `layouts/sidebar.blade.php` - Role-based menu inclusion
-   ✅ `layouts/app.blade.php` - Add staff variables

### Component Templates

-   ✅ All action button components
-   ✅ Navigation breadcrumbs
-   ✅ Link generation helpers

### Livewire Components

-   ✅ Create staff dashboard components
-   ✅ Update existing components for role support

### Route Resolution

-   ✅ Update all hardcoded admin routes
-   ✅ Add role-based route helpers
-   ✅ Fix permission-route mappings
