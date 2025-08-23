# ROLE-BASED ROUTING REFACTOR

## Current Issues Identified

### 1. Route Conflicts

-   Staff users share admin routes but have different permissions
-   No dedicated staff route prefix exists
-   Admin dashboard requires `manage_admin_dashboard` permission that staff might not have
-   403 errors occur when staff try to access admin-only routes

### 2. Permission Mismatches

-   Staff role excludes some admin permissions but shares routes
-   Inconsistent role checking (Doctor vs doctor)
-   No clear separation between admin, staff, and doctor access levels

### 3. Dashboard Routing Issues

-   Staff users redirect to admin dashboard but lack admin permissions
-   No dedicated staff dashboard routes

## Solution Overview

### Phase 1: Create Dedicated Route Files

1. Create `routes/staff.php` for staff-specific routes
2. Separate admin, staff, and doctor route prefixes
3. Add proper middleware and permission checks

### Phase 2: Update Permissions Structure

1. Create staff-specific permissions
2. Update role assignments
3. Fix permission inheritance issues

### Phase 3: Refactor Helper Functions

1. Update dashboard URL routing logic
2. Fix role checking inconsistencies
3. Add staff-specific URL helpers

### Phase 4: Update Controllers and Views

1. Add role-based controller logic
2. Update view conditionals
3. Fix route redirections

## Implementation Details

### New Route Structure:

-   `/admin/*` - Clinic Admin only (requires clinic_admin role)
-   `/staff/*` - Staff only (requires staff role)
-   `/doctors/*` - Doctors only (requires doctor role)
-   `/patients/*` - Patients only (requires patient role)

### Permission Mapping:

-   **Admin**: All permissions
-   **Staff**: Limited permissions excluding system-critical ones
-   **Doctor**: Medical/patient related permissions only
-   **Patient**: Read-only access to their own data
