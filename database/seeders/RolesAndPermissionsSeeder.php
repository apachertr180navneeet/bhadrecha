<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // create permissions
        $permissions = [
            'view dashboard',
            'manage users',
            'manage roles',
            'manage permissions',
            'manage companies',
            'manage branches'
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // create roles and assign created permissions
        $roleSuperAdmin = Role::firstOrCreate(['name' => 'Super Admin']);
        $roleSuperAdmin->givePermissionTo(Permission::all());

        $roleCompanyAdmin = Role::firstOrCreate(['name' => 'Company Admin']);
        $roleCompanyAdmin->givePermissionTo(['view dashboard', 'manage users', 'manage branches']);

        $roleBranchAdmin = Role::firstOrCreate(['name' => 'Branch Admin']);
        $roleBranchAdmin->givePermissionTo(['view dashboard', 'manage users']);

        $roleUser = Role::firstOrCreate(['name' => 'User']);
        $roleUser->givePermissionTo(['view dashboard']);

        // Create Super Admin User
        $superAdmin = User::firstOrCreate([
            'email' => 'admin@admin.com',
        ], [
            'first_name' => 'Super',
            'last_name' => 'Admin',
            'full_name' => 'Super Admin',
            'slug' => 'super-admin',
            'phone' => '1234567890',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'status' => 'active',
            'country' => 'Australia',
        ]);

        $superAdmin->assignRole('Super Admin');
    }
}
