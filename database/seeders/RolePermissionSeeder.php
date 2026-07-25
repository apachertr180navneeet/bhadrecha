<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

$permissions = [
            'companies' => ['view companies', 'create companies', 'edit companies', 'delete companies'],
            'branches' => ['view branches', 'create branches', 'edit branches', 'delete branches'],
            'users' => ['view users', 'create users', 'edit users', 'delete users'],
            'roles' => ['view roles', 'create roles', 'edit roles', 'delete roles'],
            'permissions' => ['view permissions', 'create permissions', 'edit permissions', 'delete permissions'],
            'bulties' => ['create bulties', 'edit bulties', 'delete bulties', 'view bulties'],
            'trips' => ['create trips', 'edit trips', 'delete trips', 'view trips'],
            'vehicles' => ['create vehicles', 'edit vehicles', 'delete vehicles', 'view vehicles'],
            'drivers' => ['create drivers', 'edit drivers', 'delete drivers', 'view drivers'],
            'gst' => ['create gst', 'edit gst', 'delete gst', 'view gst'],
            'cities' => ['create cities', 'edit cities', 'delete cities', 'view cities'],
            'reports' => ['view reports', 'export reports'],
            'settings' => ['manage settings'],
            'activity_logs' => ['view activity logs'],
            'consignors' => ['create consignors', 'edit consignors', 'delete consignors', 'view consignors'],
            'consignees' => ['create consignees', 'edit consignees', 'delete consignees', 'view consignees'],
            'packagings' => ['create packagings', 'edit packagings', 'delete packagings', 'view packagings'],
            'units' => ['create units', 'edit units', 'delete units', 'view units'],
            'fuel_pumps' => ['create fuel pumps', 'edit fuel pumps', 'delete fuel pumps', 'view fuel pumps'],
            'items' => ['create items', 'edit items', 'delete items', 'view items'],
            'banks' => ['create banks', 'edit banks', 'delete banks', 'view banks'],
            'billing' => ['view billing'],
            'bill_formats' => ['view bill formats', 'create bill formats', 'edit bill formats', 'delete bill formats'],
            'bank_branches' => ['create bank branches', 'edit bank branches', 'delete bank branches', 'view bank branches'],
            'fuel_companies' => ['view fuel companies', 'create fuel companies', 'edit fuel companies', 'delete fuel companies'],
            'adblue_companies' => ['view adblue companies', 'create adblue companies', 'edit adblue companies', 'delete adblue companies'],
            'suppliers' => ['view suppliers', 'create suppliers', 'edit suppliers', 'delete suppliers'],
            'vendors' => ['view vendors', 'create vendors', 'edit vendors', 'delete vendors'],
            'driver_salary' => [
                'view driver salary', 'create driver salary', 'edit driver salary', 'delete driver salary',
                'view driver advances', 'create driver advances', 'edit driver advances', 'delete driver advances',
                'generate driver salary slips', 'view driver salary slips', 'delete driver salary slips'
            ],
            'employee_salary' => [
                'view employee salary', 'create employee salary', 'edit employee salary', 'delete employee salary',
                'view attendance', 'mark attendance',
                'view leaves', 'create leaves', 'approve leaves', 'reject leaves',
                'view employee advances', 'create employee advances', 'approve employee advances', 'reject employee advances', 'mark employee advances paid'
            ],
            'loans' => [
                'view company loans', 'create company loans', 'edit company loans', 'delete company loans', 'record company loan payments',
                'view vehicle loans'
            ],
            'maintenance' => [
                'view service schedules', 'create service schedules', 'edit service schedules', 'delete service schedules', 'mark service schedules completed',
                'view spare parts', 'create spare parts', 'edit spare parts', 'delete spare parts',
                'view maintenance history', 'create maintenance history', 'edit maintenance history', 'delete maintenance history',
                'view breakdowns', 'create breakdowns', 'edit breakdowns', 'delete breakdowns', 'mark breakdowns resolved',
                'view tyre management', 'create tyre management', 'edit tyre management', 'delete tyre management'
            ],
        ];

        foreach ($permissions as $group => $perms) {
            foreach ($perms as $perm) {
                Permission::firstOrCreate(['name' => $perm]);
            }
        }

        $superAdmin = Role::firstOrCreate(['name' => 'Super Admin']);
        $superAdmin->syncPermissions(Permission::all());

        $companyAdmin = Role::firstOrCreate(['name' => 'Company Admin']);
        $companyAdminPermissions = Permission::whereNotIn('name', ['manage settings', 'view activity logs'])->get();
        $companyAdmin->syncPermissions($companyAdminPermissions);

        $branchManager = Role::firstOrCreate(['name' => 'Branch Manager']);
        $branchManagerPermissions = Permission::whereIn('name', [
            'view branches', 'view users', 'create users', 'edit users',
            'view bulties', 'create bulties', 'edit bulties',
            'view trips', 'create trips', 'edit trips',
            'view vehicles', 'view drivers',
            'view reports',
            'view billing',
            'view driver salary', 'view driver salary slips',
            'view employee salary', 'view attendance', 'mark attendance',
            'view leaves', 'create leaves',
            'view employee advances', 'create employee advances',
            'view company loans', 'view vehicle loans',
            'view service schedules', 'view spare parts', 'view maintenance history',
            'view breakdowns', 'create breakdowns', 'mark breakdowns resolved',
            'view tyre management'
        ])->get();
        $branchManager->syncPermissions($branchManagerPermissions);

        $accountant = Role::firstOrCreate(['name' => 'Accountant']);
        $accountantPermissions = Permission::whereIn('name', [
            'view bulties', 'view trips', 'view vehicles', 'view drivers',
            'view reports', 'export reports',
            'view billing',
            'view driver salary', 'create driver salary', 'edit driver salary', 'delete driver salary',
            'view driver advances', 'create driver advances', 'edit driver advances', 'delete driver advances',
            'generate driver salary slips', 'view driver salary slips', 'delete driver salary slips',
            'view employee salary', 'create employee salary', 'edit employee salary', 'delete employee salary',
            'view employee advances', 'create employee advances', 'approve employee advances', 'reject employee advances', 'mark employee advances paid',
            'view company loans', 'create company loans', 'edit company loans', 'delete company loans', 'record company loan payments',
            'view vehicle loans',
            'view service schedules', 'view spare parts', 'view maintenance history', 'view breakdowns',
            'view tyre management'
        ])->get();
        $accountant->syncPermissions($accountantPermissions);

        $dispatcher = Role::firstOrCreate(['name' => 'Dispatcher']);
        $dispatcherPermissions = Permission::whereIn('name', [
            'view bulties', 'create bulties', 'edit bulties',
            'view trips', 'create trips', 'edit trips',
            'view vehicles', 'view drivers',
        ])->get();
        $dispatcher->syncPermissions($dispatcherPermissions);

        $driver = Role::firstOrCreate(['name' => 'Driver']);
        $driverPermissions = Permission::whereIn('name', [
            'view trips', 'view vehicles',
        ])->get();
        $driver->syncPermissions($driverPermissions);

        $operator = Role::firstOrCreate(['name' => 'Operator']);
        $operatorPermissions = Permission::whereIn('name', [
            'view bulties', 'create bulties', 'edit bulties',
            'view trips', 'create trips', 'edit trips',
        ])->get();
        $operator->syncPermissions($operatorPermissions);

        $superAdminUser = User::firstOrCreate(
            ['email' => 'superadmin@mailinator.com'],
            [
                'first_name' => 'Super',
                'last_name' => 'Admin',
                'full_name' => 'Super Admin',
                'slug' => 'super-admin',
                'phone' => '9876543210',
                'password' => Hash::make('password'),
                'role' => 'admin',
                'country' => 'Australia',
                'country_code' => 61,
                'status' => 'active',
            ]
        );
        $superAdminUser->assignRole('Super Admin');

        $this->command->info('Roles and permissions seeded successfully!');
        $this->command->info('Super Admin: superadmin@mailinator.com / password');
    }
}
