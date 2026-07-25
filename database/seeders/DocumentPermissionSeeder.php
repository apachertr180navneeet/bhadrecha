<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class DocumentPermissionSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            'view documents',
            'upload documents',
            'edit documents',
            'delete documents',
            'restore documents',
            'download documents',
            'view activity',
            'manage categories',
            'manage folders',
        ];

        foreach ($permissions as $perm) {
            Permission::firstOrCreate(['name' => $perm, 'guard_name' => 'web']);
        }

        // Assign to Super Admin & Company Admin if roles exist
        $superAdmin = Role::where('name', 'Super Admin')->first();
        if ($superAdmin) {
            $superAdmin->givePermissionTo($permissions);
        }

        $companyAdmin = Role::where('name', 'Company Admin')->first();
        if ($companyAdmin) {
            $companyAdmin->givePermissionTo($permissions);
        }
    }
}
