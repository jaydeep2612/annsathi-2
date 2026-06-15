<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // Define system permissions
        $permissions = [
            'manage_settings',
            'manage_branches',
            'manage_users',
            'manage_menu',
            'manage_variants',
            'manage_kitchen_stations',
            'confirm_orders',
            'cancel_orders',
            'merge_split_orders',
            'transfer_tables',
            'merge_tables',
            'record_payments',
            'issue_credit_notes',
            'approve_refunds',
            'manage_shifts',
            'manage_inventory',
            'manage_suppliers',
            'view_reports',
            'waive_payments',
            'update_kitchen_status',
            'record_waste',
            'place_manual_orders',
            'mark_served',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        // Create roles and assign permissions
        $superAdmin = Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => 'web']);

        $restaurantAdmin = Role::firstOrCreate(['name' => 'restaurant_admin', 'guard_name' => 'web']);
        $restaurantAdmin->syncPermissions($permissions);

        $branchAdmin = Role::firstOrCreate(['name' => 'branch_admin', 'guard_name' => 'web']);
        $branchAdmin->syncPermissions(array_diff($permissions, ['manage_settings']));

        $manager = Role::firstOrCreate(['name' => 'manager', 'guard_name' => 'web']);
        $manager->syncPermissions(array_diff($permissions, ['manage_settings', 'manage_branches', 'manage_users']));

        $chef = Role::firstOrCreate(['name' => 'chef', 'guard_name' => 'web']);
        $chef->syncPermissions(['update_kitchen_status', 'record_waste']);

        $waiter = Role::firstOrCreate(['name' => 'waiter', 'guard_name' => 'web']);
        $waiter->syncPermissions(['place_manual_orders', 'mark_served']);
    }
}
