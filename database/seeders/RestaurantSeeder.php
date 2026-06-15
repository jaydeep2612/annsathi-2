<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Restaurant;
use App\Models\Branch;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class RestaurantSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Create Super Admin User (Not tied to a restaurant)
        $superAdmin = User::firstOrCreate([
            'email' => 'superadmin@annsathi.com',
        ], [
            'name' => 'Super Admin',
            'password' => Hash::make('password'),
            'is_super_admin' => true,
            'is_active' => true,
        ]);
        $superAdmin->assignRole('super_admin');

        // 2. Create Demo Restaurant
        $restaurant = Restaurant::firstOrCreate([
            'slug' => 'demo-restaurant',
        ], [
            'name' => 'The Grand Feast',
            'logo_path' => null,
            'address' => '123 Gourmet Street, Foodville',
            'phone_no' => '+1234567890',
            'gst_no' => '27AAACT1234A1Z1',
            'upi_id' => 'grandfeast@upi',
            'subscription_plan' => 'pro',
            'features' => [
                'branches' => true,
                'rooms' => true,
                'parcel' => true,
                'waiter_app' => true,
                'inventory' => 'detailed',
                'purchase_management' => true,
                'item_variants' => true,
                'kitchen_stations' => true,
                'table_operations' => true,
                'shift_management' => true,
                'waste_tracking' => true,
                'recipe_versioning' => true,
                'food_cost_engine' => true,
                'central_menu' => false,
                'gst_billing' => true,
                'credit_notes' => true,
                'advanced_analytics' => true,
            ],
            'settings' => [
                'auto_confirm' => false,
                'invoice_prefix' => 'GF',
                'gst_rate' => 5.0,
                'currency' => 'INR',
                'tax_inclusive' => false,
                'extra_charge_label' => 'Service Charge',
                'extra_charge_amount' => 5.0,
                'service_charge_pct' => 5.0,
                'operating_mode' => 'Mode C',
                'deduction_trigger' => 'preparing',
                'archive_after_months' => 6,
                'require_waiter_assignment' => true,
            ],
            'user_limits' => 20,
            'table_limits' => 30,
            'rooms_limit' => 10,
            'max_branches' => 3,
            'is_active' => true,
        ]);

        // 3. Create Main Branch
        $branch = Branch::firstOrCreate([
            'restaurant_id' => $restaurant->id,
            'name' => 'Colaba Branch',
        ], [
            'address' => 'Colaba Causeway, Mumbai',
            'phone_no' => '+919876543210',
            'upi_id' => 'gfcolaba@upi',
            'is_active' => true,
        ]);

        // 4. Create Restaurant Admin User
        $restAdmin = User::firstOrCreate([
            'email' => 'admin@annsathi.com',
        ], [
            'restaurant_id' => $restaurant->id,
            'branch_id' => $branch->id,
            'name' => 'Restaurant Admin',
            'password' => Hash::make('password'),
            'is_super_admin' => false,
            'is_active' => true,
        ]);
        $restAdmin->assignRole('restaurant_admin');
        $restAdmin->branches()->syncWithoutDetaching([$branch->id]);

        // Update restaurant creator
        $restaurant->update(['created_by' => $restAdmin->id]);

        // 5. Create Manager User
        $manager = User::firstOrCreate([
            'email' => 'manager@annsathi.com',
        ], [
            'restaurant_id' => $restaurant->id,
            'branch_id' => $branch->id,
            'name' => 'Branch Manager',
            'password' => Hash::make('password'),
            'is_active' => true,
        ]);
        $manager->assignRole('manager');
        $manager->branches()->syncWithoutDetaching([$branch->id]);

        // 6. Create Chef User
        $chef = User::firstOrCreate([
            'email' => 'chef@annsathi.com',
        ], [
            'restaurant_id' => $restaurant->id,
            'branch_id' => $branch->id,
            'name' => 'Head Chef',
            'password' => Hash::make('password'),
            'is_active' => true,
        ]);
        $chef->assignRole('chef');
        $chef->branches()->syncWithoutDetaching([$branch->id]);

        // 7. Create Waiter User
        $waiter = User::firstOrCreate([
            'email' => 'waiter@annsathi.com',
        ], [
            'restaurant_id' => $restaurant->id,
            'branch_id' => $branch->id,
            'name' => 'John Waiter',
            'password' => Hash::make('password'),
            'is_active' => true,
        ]);
        $waiter->assignRole('waiter');
        $waiter->branches()->syncWithoutDetaching([$branch->id]);
    }
}
