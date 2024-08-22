<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission; // Add this line
use Spatie\Permission\Models\Role; 
use App\Models\User;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Permission::create(['name' => 'manage users']);
        Permission::create(['name' => 'manage roles']);
        Permission::create(['name' => 'manage food items']);
        Permission::create(['name' => 'view food items']);
        Permission::create(['name' => 'place orders']);

        // Create roles and assign existing permissions
        $adminRole = Role::create(['name' => 'admin']);
        $chefRole = Role::create(['name' => 'chef']);
        $userRole = Role::create(['name' => 'user']);

        $adminRole->givePermissionTo(['manage users', 'manage roles', 'manage food items', 'view food items', 'place orders']);
        $chefRole->givePermissionTo(['manage food items', 'view food items', 'place orders']);
        $userRole->givePermissionTo(['view food items', 'place orders']);

        // Create an admin user
        $admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => bcrypt('password'),
        ]);
        $admin->assignRole('admin');
    }
}
