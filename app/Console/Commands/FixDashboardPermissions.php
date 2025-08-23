<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class FixDashboardPermissions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fix:dashboard-permissions 
                            {--dry-run : Show what would be changed without making changes}
                            {--role= : Fix permissions for specific role only}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fix dashboard permission conflicts between admin and staff users';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $isDryRun = $this->option('dry-run');
        $specificRole = $this->option('role');

        $this->info('🔍 Analyzing dashboard permissions...');
        $this->newLine();

        // Get all users with problematic permissions
        $problematicUsers = $this->findProblematicUsers($specificRole);

        if ($problematicUsers->isEmpty()) {
            $this->info('✅ No permission conflicts found!');
            return;
        }

        $this->warn("Found {$problematicUsers->count()} users with potential dashboard permission conflicts:");
        $this->newLine();

        // Display problematic users
        $headers = ['ID', 'Name', 'Email', 'Roles', 'Dashboard Permissions', 'Issue'];
        $rows = [];

        foreach ($problematicUsers as $user) {
            $roles = $user->roles->pluck('name')->implode(', ');
            $dashboardPerms = $user->permissions()
                ->where('name', 'like', '%dashboard%')
                ->pluck('name')
                ->implode(', ');

            $issue = $this->identifyIssue($user);

            $rows[] = [
                $user->id,
                $user->first_name . ' ' . $user->last_name,
                $user->email,
                $roles,
                $dashboardPerms ?: 'None',
                $issue
            ];
        }

        $this->table($headers, $rows);
        $this->newLine();

        if ($isDryRun) {
            $this->info('🔍 Dry run mode - showing what would be fixed:');
            $this->showProposedFixes($problematicUsers);
        } else {
            if ($this->confirm('Do you want to fix these permission conflicts?')) {
                $this->fixPermissions($problematicUsers);
            }
        }
    }

    /**
     * Find users with problematic dashboard permissions
     */
    private function findProblematicUsers($specificRole = null)
    {
        $query = User::with(['roles', 'permissions']);

        if ($specificRole) {
            $query->whereHas('roles', function ($q) use ($specificRole) {
                $q->where('name', $specificRole);
            });
        }

        return $query->get()->filter(function ($user) {
            // Check for users with conflicting dashboard permissions
            $hasAdminDashboard = $user->can('manage_admin_dashboard');
            $hasStaffDashboard = $user->can('manage_staff_dashboard');
            $isAdmin = $user->hasRole('clinic_admin');
            $isStaff = $user->hasRole('staff');
            $isDoctor = $user->hasRole('doctor');
            $isPatient = $user->hasRole('patient');

            // Problematic cases:
            // 1. Staff user with admin dashboard permission
            // 2. Admin user without admin dashboard permission
            // 3. Doctor/Patient with any dashboard permissions (they should only access their own)
            // 4. User with multiple roles (except patient+doctor combination)
            // 5. Users with conflicting role combinations

            $hasMultipleRoles = $user->roles->count() > 1;
            $hasInappropriatePermissions = false;

            // Check if doctor/patient has admin/staff dashboard permissions
            if (($isDoctor || $isPatient) && ($hasAdminDashboard || $hasStaffDashboard)) {
                $hasInappropriatePermissions = true;
            }

            return ($isStaff && $hasAdminDashboard) ||
                ($isAdmin && !$hasAdminDashboard) ||
                ($hasMultipleRoles && !($isDoctor && $isPatient && $user->roles->count() == 2)) ||
                $hasInappropriatePermissions;
        });
    }

    /**
     * Identify the specific issue with a user's permissions
     */
    private function identifyIssue($user)
    {
        $hasAdminDashboard = $user->can('manage_admin_dashboard');
        $hasStaffDashboard = $user->can('manage_staff_dashboard');
        $isAdmin = $user->hasRole('clinic_admin');
        $isStaff = $user->hasRole('staff');
        $isDoctor = $user->hasRole('doctor');
        $isPatient = $user->hasRole('patient');

        if ($user->roles->count() > 2) {
            return 'Too many roles assigned';
        }

        if ($user->roles->count() > 1 && !($isDoctor && $isPatient)) {
            return 'Conflicting role combination';
        }

        if (($isDoctor || $isPatient) && ($hasAdminDashboard || $hasStaffDashboard)) {
            return 'Doctor/Patient with admin/staff dashboard access';
        }

        if ($isStaff && $hasAdminDashboard) {
            return 'Staff user with admin dashboard access';
        }

        if ($isAdmin && !$hasAdminDashboard) {
            return 'Admin user without admin dashboard access';
        }

        return 'Unknown issue';
    }

    /**
     * Show what would be fixed in dry run mode
     */
    private function showProposedFixes($users)
    {
        foreach ($users as $user) {
            $this->info("User: {$user->first_name} {$user->last_name} ({$user->email})");

            $isAdmin = $user->hasRole('clinic_admin');
            $isStaff = $user->hasRole('staff');
            $isDoctor = $user->hasRole('doctor');
            $isPatient = $user->hasRole('patient');

            if ($user->roles->count() > 2) {
                $this->line("  → Would keep primary role, remove excess roles");
            } elseif ($user->roles->count() > 1 && !($isDoctor && $isPatient)) {
                $this->line("  → Would resolve conflicting role combination");
            } elseif (($isDoctor || $isPatient) && ($user->can('manage_admin_dashboard') || $user->can('manage_staff_dashboard'))) {
                $this->line("  → Would remove admin/staff dashboard permissions");
            } elseif ($isStaff && $user->can('manage_admin_dashboard')) {
                $this->line("  → Would remove 'manage_admin_dashboard' permission");
            } elseif ($isAdmin && !$user->can('manage_admin_dashboard')) {
                $this->line("  → Would add 'manage_admin_dashboard' permission");
            }

            $this->newLine();
        }
    }

    /**
     * Fix the permission conflicts
     */
    private function fixPermissions($users)
    {
        $fixed = 0;

        foreach ($users as $user) {
            $isAdmin = $user->hasRole('clinic_admin');
            $isStaff = $user->hasRole('staff');
            $isDoctor = $user->hasRole('doctor');
            $isPatient = $user->hasRole('patient');

            $this->info("Fixing permissions for: {$user->first_name} {$user->last_name}");

            // Handle too many roles - keep the most privileged
            if ($user->roles->count() > 2) {
                $rolePriority = ['clinic_admin', 'staff', 'doctor', 'patient'];
                $userRoles = $user->roles->pluck('name')->toArray();

                foreach ($rolePriority as $role) {
                    if (in_array($role, $userRoles)) {
                        // Keep this role, remove others
                        $rolesToRemove = array_diff($userRoles, [$role]);
                        foreach ($rolesToRemove as $roleToRemove) {
                            $user->removeRole($roleToRemove);
                            $this->line("  ✓ Removed '{$roleToRemove}' role");
                        }
                        break;
                    }
                }
            }

            // Handle conflicting role combinations (except doctor+patient)
            if ($user->roles->count() > 1 && !($isDoctor && $isPatient && $user->roles->count() == 2)) {
                if ($isAdmin && $isStaff) {
                    $user->removeRole('staff');
                    $this->line("  ✓ Removed 'staff' role (prioritizing admin)");
                } elseif ($isAdmin && ($isDoctor || $isPatient)) {
                    // Keep admin role
                    if ($isDoctor) $user->removeRole('doctor');
                    if ($isPatient) $user->removeRole('patient');
                    $this->line("  ✓ Removed doctor/patient role (prioritizing admin)");
                } elseif ($isStaff && ($isDoctor || $isPatient)) {
                    // Keep staff role
                    if ($isDoctor) $user->removeRole('doctor');
                    if ($isPatient) $user->removeRole('patient');
                    $this->line("  ✓ Removed doctor/patient role (prioritizing staff)");
                }
            }

            // Remove inappropriate dashboard permissions from doctors/patients
            if (($isDoctor || $isPatient) && $user->can('manage_admin_dashboard')) {
                $user->revokePermissionTo('manage_admin_dashboard');
                $this->line("  ✓ Removed 'manage_admin_dashboard' permission from doctor/patient");
            }

            if (($isDoctor || $isPatient) && $user->can('manage_staff_dashboard')) {
                $user->revokePermissionTo('manage_staff_dashboard');
                $this->line("  ✓ Removed 'manage_staff_dashboard' permission from doctor/patient");
            }

            // If staff user has admin dashboard permission, remove it
            if ($isStaff && $user->can('manage_admin_dashboard')) {
                $user->revokePermissionTo('manage_admin_dashboard');
                $this->line("  ✓ Removed 'manage_admin_dashboard' permission");
            }

            // If admin user doesn't have admin dashboard permission, add it
            if ($isAdmin && !$user->can('manage_admin_dashboard')) {
                $user->givePermissionTo('manage_admin_dashboard');
                $this->line("  ✓ Added 'manage_admin_dashboard' permission");
            }

            $fixed++;
        }

        $this->newLine();
        $this->info("✅ Fixed permissions for {$fixed} users");

        // Suggest clearing cache
        $this->warn('💡 Consider running the following commands to clear caches:');
        $this->line('   php artisan cache:clear');
        $this->line('   php artisan permission:cache-reset');
    }
}
