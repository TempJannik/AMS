<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        Permission::create([
            'name' => 'edit overdue tasks', 
            'guard_name' => 'api'
        ]);
        $role = Role::create([
            'name' => 'Super-Admin',
            'guard_name' => 'api'
        ]);
        $role->givePermissionTo('edit overdue tasks');
        $admin = \App\Models\User::factory()->create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
        ]);
        $admin->assignRole('Super-Admin');
    }
}
