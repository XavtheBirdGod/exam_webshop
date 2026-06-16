<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Enums\Role;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Platform Admin (Central admin)
        User::updateOrCreate(
            ['email' => 'admin@rituals.com'],
            [
                'name' => 'Platform Admin',
                'password' => Hash::make('password'),
                'role' => Role::PLATFORM_ADMIN,
            ]
        );

        // 2. Amsterdam Shop Admin
        $amsterdamAdmin = User::updateOrCreate(
            ['email' => 'amsterdam@rituals.com'],
            [
                'name' => 'Amsterdam Admin',
                'password' => Hash::make('password'),
                'role' => Role::CUSTOMER,
            ]
        );
        // Sync tenant role (Role::SHOP_ADMIN in shop-amsterdam)
        $amsterdamAdmin->tenants()->syncWithPivotValues(['shop-amsterdam'], ['role' => Role::SHOP_ADMIN->value]);

        // 3. Paris Shop Admin
        $parisAdmin = User::updateOrCreate(
            ['email' => 'paris@rituals.com'],
            [
                'name' => 'Paris Admin',
                'password' => Hash::make('password'),
                'role' => Role::CUSTOMER,
            ]
        );
        // Sync tenant role (Role::SHOP_ADMIN in shop-paris)
        $parisAdmin->tenants()->syncWithPivotValues(['shop-paris'], ['role' => Role::SHOP_ADMIN->value]);

        // 4. London Shop Admin
        $londonAdmin = User::updateOrCreate(
            ['email' => 'london@rituals.com'],
            [
                'name' => 'London Admin',
                'password' => Hash::make('password'),
                'role' => Role::CUSTOMER,
            ]
        );
        // Sync tenant role (Role::SHOP_ADMIN in shop-london)
        $londonAdmin->tenants()->syncWithPivotValues(['shop-london'], ['role' => Role::SHOP_ADMIN->value]);

        // 4. Amsterdam Shop Staff
        $amsterdamStaff = User::updateOrCreate(
            ['email' => 'staff@rituals.com'],
            [
                'name' => 'Amsterdam Staff',
                'password' => Hash::make('password'),
                'role' => Role::CUSTOMER,
            ]
        );
        // Sync tenant role (Role::SHOP_STAFF in shop-amsterdam)
        $amsterdamStaff->tenants()->syncWithPivotValues(['shop-amsterdam'], ['role' => Role::SHOP_STAFF->value]);

        // 5. Standard Customer
        User::updateOrCreate(
            ['email' => 'customer@example.com'],
            [
                'name' => 'Jane Customer',
                'password' => Hash::make('password'),
                'role' => Role::CUSTOMER,
            ]
        );
    }
}
