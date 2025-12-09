<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Clear cache
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $this->command->info('🚀 Starting Role & Permission Seeder...');
        $this->command->newLine();

        // Step 1: Create/Update Roles
        $this->createRoles();

        // Step 2: Create/Update Permissions
        $this->createPermissions();

        // Step 3: Assign Permissions to Roles
        $this->assignPermissionsToRoles();

        // Step 4: Assign specific users to roles
        $this->assignSpecificUsers();

        // Step 5: Sync remaining users
        $this->syncExistingUsers();

        $this->command->newLine();
        $this->command->info('✅ Role & Permission Seeder completed successfully!');
    }

    /**
     * Create or update roles
     */
    private function createRoles(): void
    {
        $this->command->info('📋 Creating/Updating Roles...');

        $roles = ['staff', 'manager', 'admin', 'director'];

        foreach ($roles as $roleName) {
            $role = Role::firstOrCreate(['name' => $roleName]);

            if ($role->wasRecentlyCreated) {
                $this->command->line("  + Created: {$roleName}");
            } else {
                $this->command->line("  ✓ Role exists: {$roleName}");
            }
        }
    }

    /**
     * Create permissions (only if not exists)
     */
    private function createPermissions(): void
    {
        $this->command->info('📋 Creating Permissions (skipping existing)...');

        $allPermissions = [
            // Global
            'dashboard.view',
            'attendance.check-in',

            // Attendance
            'attendance.view-own',
            'attendance.view-team',
            'attendance.view-all',

            // Leave Requests
            'leave-requests.view-own',
            'leave-requests.view-pending',

            // Management
            'users.view',
            'schedule.view',
            'office-locations.view',
        ];

        $newCount = 0;
        $existingCount = 0;

        foreach ($allPermissions as $permissionName) {
            $permission = Permission::firstOrCreate(['name' => $permissionName]);

            if ($permission->wasRecentlyCreated) {
                $this->command->line("  + Created: {$permissionName}");
                $newCount++;
            } else {
                $existingCount++;
            }
        }

        $this->command->info('  ✓ Total: ' . count($allPermissions) . ' permissions');
        $this->command->line("    → New: {$newCount}");
        $this->command->line("    → Existing: {$existingCount}");
    }

    /**
     * Assign permissions to roles
     */
    private function assignPermissionsToRoles(): void
    {
        $this->command->info('📋 Assigning Permissions to Roles...');

        // STAFF
        $staff = Role::findByName('staff');
        $staff->syncPermissions([
            'dashboard.view',
            'attendance.check-in',
            'attendance.view-own',
            'leave-requests.view-own',
        ]);
        $this->command->line('  ✓ Staff: ' . $staff->permissions->count() . ' permissions');

        // MANAGER
        $manager = Role::findByName('manager');
        $manager->syncPermissions([
            'dashboard.view',
            'attendance.check-in',
            'attendance.view-team',
            'leave-requests.view-pending',
            'users.view',
            'schedule.view',
            'office-locations.view',
        ]);
        $this->command->line('  ✓ Manager: ' . $manager->permissions->count() . ' permissions');

        // ADMIN
        $admin = Role::findByName('admin');
        $admin->syncPermissions([
            'dashboard.view',
            'attendance.check-in',
            'attendance.view-all',
            'leave-requests.view-pending',
            'users.view',
            'schedule.view',
            'office-locations.view',
        ]);
        $this->command->line('  ✓ Admin: ' . $admin->permissions->count() . ' permissions');

        // DIRECTOR - Full Access
        $director = Role::findByName('director');
        $director->syncPermissions(Permission::all());
        $this->command->line("  ✓ Director: {$director->permissions->count()} permissions (Full Access)");
    }

    /**
     * Assign specific users to roles based on email
     */
    private function assignSpecificUsers(): void
    {
        $this->command->info('📋 Assigning Specific Users...');

        $specificUsers = [
            'admin@gmail.com' => 'admin',
            'director@gmail.com' => 'director',
            'manager@gmail.com' => 'manager',
            'staff@gmail.com' => 'staff',
        ];

        $assignedCount = 0;

        foreach ($specificUsers as $email => $roleName) {
            $user = User::where('email', $email)->first();

            if ($user) {
                $user->syncRoles([$roleName]);
                $this->command->line("  + {$email} → {$roleName}");
                $assignedCount++;
            }
        }

        if ($assignedCount > 0) {
            $this->command->info("  ✓ Assigned {$assignedCount} specific user(s)");
        } else {
            $this->command->warn('  ⚠ No specific users found');
        }
    }

    /**
     * Sync remaining users without roles
     */
    private function syncExistingUsers(): void
    {
        $this->command->info('📋 Syncing Remaining Users...');

        $totalUsers = User::count();

        if ($totalUsers === 0) {
            $this->command->warn('  ⚠ No users in database yet!');
            return;
        }

        $this->command->line("  → Total users in database: {$totalUsers}");

        // Get users without roles
        $usersWithoutRoles = User::doesntHave('roles')->get();

        if ($usersWithoutRoles->isEmpty()) {
            $this->command->line('  ✓ All users have roles assigned');
            $this->showRoleDistribution();
            return;
        }

        $this->command->warn("  ⚠ Found {$usersWithoutRoles->count()} user(s) without roles");
        $this->command->line('  → Assigning default "staff" role...');

        $assignedCount = 0;
        foreach ($usersWithoutRoles as $user) {
            $user->assignRole('staff');
            $this->command->line("    + {$user->email}");
            $assignedCount++;
        }

        $this->command->info("  ✓ Assigned 'staff' role to {$assignedCount} user(s)");
        $this->showRoleDistribution();
    }

    /**
     * Show current role distribution
     */
    private function showRoleDistribution(): void
    {
        $this->command->newLine();
        $this->command->info('  📊 Current Role Distribution:');

        $roles = Role::all();
        foreach ($roles as $role) {
            $userCount = $role->users()->count();
            $this->command->line("    → {$role->name}: {$userCount} user(s)");
        }
    }
}