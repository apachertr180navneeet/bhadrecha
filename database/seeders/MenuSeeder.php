<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Menu;

class MenuSeeder extends Seeder
{
    public function run(): void
    {
        $parentMenus = [
            ['title' => 'Dashboard', 'icon' => 'bx bx-home-circle', 'route_name' => 'admin.dashboard', 'order' => 1],
            ['title' => 'Transport Operations', 'menu_header' => 'Transport Operations', 'order' => 2],
            ['title' => 'Bulties (LR)', 'icon' => 'bx bx-receipt', 'route_name' => 'admin.transport.bulties.index', 'order' => 3, 'permission' => 'view bulties'],
            ['title' => 'Trips', 'icon' => 'bx bx-carousel', 'route_name' => 'admin.transport.trips.index', 'order' => 4, 'permission' => 'view trips'],
            ['title' => 'Generate Bill', 'icon' => 'bx bx-receipt', 'route_name' => 'admin.transport.billing', 'order' => 5, 'permission' => 'view billing'],
            ['title' => 'Driver Salary', 'menu_header' => 'Driver Salary', 'order' => 6],
        ];
        foreach ($parentMenus as $menu) {
            Menu::create($menu);
        }

        $driverSalaryParent = Menu::create(['title' => 'Driver Salary', 'icon' => 'bx bx-user-circle', 'order' => 9]);
        
        $driverSalaryChildren = [
            ['title' => 'Salary Management', 'icon' => 'bx bx-money', 'route_name' => 'admin.driver-management.salary', 'order' => 1],
            ['title' => 'Advance Management', 'icon' => 'bx bx-coin', 'route_name' => 'admin.driver-management.advance', 'order' => 2],
            ['title' => 'Generate Slip', 'icon' => 'bx bx-receipt', 'route_name' => 'admin.driver-management.salary-slip', 'order' => 3],
            ['title' => 'All Salary Slips', 'icon' => 'bx bx-list-ul', 'route_name' => 'admin.driver-management.salary-slip.list', 'order' => 4],
        ];
        foreach ($driverSalaryChildren as $child) {
            $child['parent_id'] = $driverSalaryParent->id;
            Menu::create($child);
        }

        $employeeSalaryParent = Menu::create(['title' => 'Employee Salary', 'icon' => 'bx bx-wallet', 'order' => 9]);
        
        $employeeSalaryChildren = [
            ['title' => 'Employee List', 'icon' => 'bx bx-group', 'order' => 1],
            ['title' => 'Attendance', 'icon' => 'bx bx-calendar', 'route_name' => 'admin.attendance.index', 'order' => 2],
            ['title' => 'Leaves', 'icon' => 'bx bx-exit', 'route_name' => 'admin.leaves.index', 'order' => 3],
            ['title' => 'Advance', 'icon' => 'bx bx-money', 'route_name' => 'admin.advances.index', 'order' => 4],
        ];
        foreach ($employeeSalaryChildren as $child) {
            $child['parent_id'] = $employeeSalaryParent->id;
            Menu::create($child);
        }

        $loanParent = Menu::create(['title' => 'Loan', 'icon' => 'bx bx-credit-card', 'order' => 10]);
        
        $loanChildren = [
            ['title' => 'Company Loan', 'icon' => 'bx bx-buildings', 'route_name' => 'admin.loan.company-loan.index', 'order' => 1],
            ['title' => 'Vehicle Loan', 'icon' => 'bx bx-car', 'route_name' => 'admin.loan.vehicle', 'order' => 2],
        ];
        foreach ($loanChildren as $child) {
            $child['parent_id'] = $loanParent->id;
            Menu::create($child);
        }

        $maintenanceParent = Menu::create(['title' => 'Maintenance', 'icon' => 'bx bx-wrench', 'order' => 11]);
        
        $maintenanceChildren = [
            ['title' => 'Service Schedule', 'icon' => 'bx bx-calendar', 'route_name' => 'admin.maintenance.service-schedule.index', 'order' => 1],
            ['title' => 'Spare Parts', 'icon' => 'bx bx-cog', 'route_name' => 'admin.maintenance.spare-part.index', 'order' => 2],
            ['title' => 'Maintenance History', 'icon' => 'bx bx-history', 'route_name' => 'admin.maintenance.maintenance-history.index', 'order' => 3],
            ['title' => 'Breakdown Management', 'icon' => 'bx bx-alarm-exclamation', 'route_name' => 'admin.maintenance.breakdowns.index', 'order' => 4],
            ['title' => 'Tyre Management', 'icon' => 'bx bx-circle', 'route_name' => 'admin.maintenance.tyre-management.index', 'order' => 5, 'permission' => 'view tyre management'],
        ];
        foreach ($maintenanceChildren as $child) {
            $child['parent_id'] = $maintenanceParent->id;
            Menu::create($child);
        }

        $reportsParent = Menu::create(['title' => 'Reports', 'icon' => 'bx bx-bar-chart-alt-2', 'order' => 12, 'permission' => 'view reports']);
        
        $reportsChildren = [
            ['title' => 'Vehicle Performance', 'icon' => 'bx bx-car', 'route_name' => 'admin.reports.vehicle', 'order' => 1],
            ['title' => 'Driver Trip Report', 'icon' => 'bx bx-user', 'route_name' => 'admin.reports.driver-trip', 'order' => 2],
            ['title' => 'Customer Ledger', 'icon' => 'bx bx-book', 'route_name' => 'admin.reports.customer-ledger', 'order' => 3],
            ['title' => 'Trip Reports', 'icon' => 'bx bx-detail', 'route_name' => 'admin.reports.trip-reports', 'order' => 4],
            ['title' => 'Fuel Report', 'icon' => 'bx bx-gas-pump', 'route_name' => 'admin.reports.fuel', 'order' => 5],
            ['title' => 'Vehicle Utilization', 'icon' => 'bx bx-line-chart', 'route_name' => 'admin.reports.vehicle-utilization', 'order' => 6],
            ['title' => 'MIS Report', 'icon' => 'bx bx-bar-chart-square', 'route_name' => 'admin.reports.mis', 'order' => 7],
            ['title' => 'Expense Management', 'icon' => 'bx bx-money', 'route_name' => 'admin.reports.expense-management', 'order' => 8],
            ['title' => 'Vehicle Documents', 'icon' => 'bx bx-file-blank', 'route_name' => 'admin.reports.vehicle-documents', 'order' => 9],
            ['title' => 'GST & Tax', 'icon' => 'bx bx-receipt', 'route_name' => 'admin.reports.gst-tax', 'order' => 10],
            ['title' => 'Profit & Loss', 'icon' => 'bx bx-trending-up', 'route_name' => 'admin.reports.profit-loss', 'order' => 11],
        ];
        foreach ($reportsChildren as $child) {
            $child['parent_id'] = $reportsParent->id;
            Menu::create($child);
        }

        $mastersParent = Menu::create(['title' => 'Masters', 'icon' => 'bx bx-data', 'order' => 13]);
        
        $mastersChildren = [
            ['title' => 'Consignors', 'icon' => 'bx bx-user-plus', 'route_name' => 'admin.masters.consignors.index', 'order' => 1, 'permission' => 'view consignors'],
            ['title' => 'Consignees', 'icon' => 'bx bx-user-check', 'route_name' => 'admin.masters.consignees.index', 'order' => 2, 'permission' => 'view consignees'],
            ['title' => 'Vehicles', 'icon' => 'bx bx-car', 'route_name' => 'admin.masters.vehicles.index', 'order' => 3, 'permission' => 'view vehicles'],
            ['title' => 'Drivers', 'icon' => 'bx bx-id-card', 'route_name' => 'admin.masters.drivers.index', 'order' => 4, 'permission' => 'view drivers'],
            ['title' => 'Companies', 'icon' => 'bx bx-buildings', 'route_name' => 'admin.companies.index', 'order' => 5, 'permission' => 'view companies'],
            ['title' => 'Branches', 'icon' => 'bx bx-store-alt', 'route_name' => 'admin.branches.index', 'order' => 6, 'permission' => 'view branches'],
            ['title' => 'GST Master', 'icon' => 'bx bx-dollar-circle', 'route_name' => 'admin.masters.gst.index', 'order' => 7, 'permission' => 'view gst'],
            ['title' => 'Cities', 'icon' => 'bx bx-building', 'route_name' => 'admin.masters.city.index', 'order' => 8, 'permission' => 'view cities'],
            ['title' => 'Packagings', 'icon' => 'bx bx-package', 'route_name' => 'admin.masters.packagings.index', 'order' => 9, 'permission' => 'view packagings'],
            ['title' => 'Units', 'icon' => 'bx bx-ruler', 'route_name' => 'admin.masters.units.index', 'order' => 10, 'permission' => 'view units'],
            ['title' => 'Fuel Pumps', 'icon' => 'bx bx-gas-pump', 'route_name' => 'admin.masters.fuel-pumps.index', 'order' => 11, 'permission' => 'view fuel pumps'],
            ['title' => 'Fuel Companies', 'icon' => 'bx bx-tag', 'route_name' => 'admin.masters.fuel-companies.index', 'order' => 12, 'permission' => 'view fuel companies'],
            ['title' => 'AdBlue Companies', 'icon' => 'bx bx-droplet', 'route_name' => 'admin.masters.adblue-companies.index', 'order' => 13, 'permission' => 'view adblue companies'],
            ['title' => 'Items', 'icon' => 'bx bx-cube', 'route_name' => 'admin.masters.items.index', 'order' => 14, 'permission' => 'view items'],
            ['title' => 'Suppliers', 'icon' => 'bx bx-package', 'route_name' => 'admin.masters.suppliers.index', 'order' => 15, 'permission' => 'view suppliers'],
            ['title' => 'Vendors', 'icon' => 'bx bx-store', 'route_name' => 'admin.masters.vendors.index', 'order' => 16, 'permission' => 'view vendors'],
            ['title' => 'Banks', 'icon' => 'bx bx-building-house', 'route_name' => 'admin.masters.banks.index', 'order' => 17, 'permission' => 'view banks'],
            ['title' => 'Bank Branches', 'icon' => 'bx bx-git-branch', 'route_name' => 'admin.masters.bank-branches.index', 'order' => 18, 'permission' => 'view bank branches'],
            ['title' => 'Bill Formats', 'icon' => 'bx bx-file', 'route_name' => 'admin.masters.bill-formats.index', 'order' => 19, 'permission' => 'view bill formats'],
        ];
        foreach ($mastersChildren as $child) {
            $child['parent_id'] = $mastersParent->id;
            Menu::create($child);
        }

        $accessControlMenus = [
            ['title' => 'Access Control', 'menu_header' => 'Access Control', 'order' => 14],
            ['title' => 'Users', 'icon' => 'bx bx-user', 'route_name' => 'admin.users.index', 'order' => 15, 'permission' => 'view users'],
            ['title' => 'Roles', 'icon' => 'bx bx-shield-quarter', 'route_name' => 'admin.roles.index', 'order' => 16, 'permission' => 'view roles'],
            ['title' => 'Permissions', 'icon' => 'bx bx-key', 'route_name' => 'admin.permissions.index', 'order' => 17, 'permission' => 'view permissions'],
        ];
        foreach ($accessControlMenus as $menu) {
            Menu::create($menu);
        }

        $systemMenus = [
            ['title' => 'System', 'menu_header' => 'System', 'order' => 18],
            ['title' => 'Activity Logs', 'icon' => 'bx bx-history', 'route_name' => 'admin.activity-logs', 'order' => 19, 'permission' => 'view activity logs'],
            ['title' => 'Settings', 'icon' => 'bx bx-cog', 'route_name' => 'admin.settings', 'order' => 20, 'permission' => 'manage settings'],
        ];
        foreach ($systemMenus as $menu) {
            Menu::create($menu);
        }

        $this->command->info('Menus seeded successfully!');
    }
}