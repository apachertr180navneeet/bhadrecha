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
        ];
        foreach ($parentMenus as $menu) {
            Menu::create($menu);
        }

        $driverSalaryParent = Menu::create(['title' => 'Driver Salary', 'icon' => 'bx bx-user-circle', 'order' => 6, 'permission' => 'view driver salary']);
        
        $driverSalaryChildren = [
            ['title' => 'Salary Management', 'icon' => 'bx bx-money', 'route_name' => 'admin.driver-management.salary', 'order' => 1, 'permission' => 'view driver salary'],
            ['title' => 'Advance Management', 'icon' => 'bx bx-coin', 'route_name' => 'admin.driver-management.advance', 'order' => 2, 'permission' => 'view driver advances'],
            ['title' => 'Generate Slip', 'icon' => 'bx bx-receipt', 'route_name' => 'admin.driver-management.salary-slip', 'order' => 3, 'permission' => 'generate driver salary slips'],
            ['title' => 'All Salary Slips', 'icon' => 'bx bx-list-ul', 'route_name' => 'admin.driver-management.salary-slip.list', 'order' => 4, 'permission' => 'view driver salary slips'],
        ];
        foreach ($driverSalaryChildren as $child) {
            $child['parent_id'] = $driverSalaryParent->id;
            Menu::create($child);
        }

        $employeeSalaryParent = Menu::create(['title' => 'Employee Salary', 'icon' => 'bx bx-wallet', 'order' => 7]);
        
        $employeeSalaryChildren = [
            ['title' => 'Employee List', 'icon' => 'bx bx-group', 'route_name' => 'admin.employee-salary.employees-list', 'order' => 1, 'permission' => 'view employee salary'],
            ['title' => 'Attendance', 'icon' => 'bx bx-calendar', 'route_name' => 'admin.attendance.index', 'order' => 2, 'permission' => 'view attendance'],
            ['title' => 'Leaves', 'icon' => 'bx bx-exit', 'route_name' => 'admin.leaves.index', 'order' => 3, 'permission' => 'view leaves'],
            ['title' => 'Advance', 'icon' => 'bx bx-money', 'route_name' => 'admin.advances.index', 'order' => 4, 'permission' => 'view employee advances'],
        ];
        foreach ($employeeSalaryChildren as $child) {
            $child['parent_id'] = $employeeSalaryParent->id;
            Menu::create($child);
        }

        $loanParent = Menu::create(['title' => 'Loan', 'icon' => 'bx bx-credit-card', 'order' => 8]);
        
        $loanChildren = [
            ['title' => 'Company Loan', 'icon' => 'bx bx-buildings', 'route_name' => 'admin.loan.company-loan.index', 'order' => 1, 'permission' => 'view company loans'],
            ['title' => 'Vehicle Loan', 'icon' => 'bx bx-car', 'route_name' => 'admin.loan.vehicle', 'order' => 2, 'permission' => 'view vehicle loans'],
        ];
        foreach ($loanChildren as $child) {
            $child['parent_id'] = $loanParent->id;
            Menu::create($child);
        }

        $maintenanceParent = Menu::create(['title' => 'Maintenance', 'icon' => 'bx bx-wrench', 'order' => 9]);
        
        $maintenanceChildren = [
            ['title' => 'Service Schedule', 'icon' => 'bx bx-calendar', 'route_name' => 'admin.maintenance.service-schedule.index', 'order' => 1, 'permission' => 'view service schedules'],
            ['title' => 'Spare Parts', 'icon' => 'bx bx-cog', 'route_name' => 'admin.maintenance.spare-part.index', 'order' => 2, 'permission' => 'view spare parts'],
            ['title' => 'Maintenance History', 'icon' => 'bx bx-history', 'route_name' => 'admin.maintenance.maintenance-history.index', 'order' => 3, 'permission' => 'view maintenance history'],
            ['title' => 'Breakdown Management', 'icon' => 'bx bx-alarm-exclamation', 'route_name' => 'admin.maintenance.breakdowns.index', 'order' => 4, 'permission' => 'view breakdowns'],
            ['title' => 'Tyre Management', 'icon' => 'bx bx-circle', 'route_name' => 'admin.maintenance.tyre-management.index', 'order' => 5, 'permission' => 'view tyre management'],
            ['title' => 'Graphic Tyre Layout', 'icon' => 'bx bx-grid-alt', 'route_name' => 'admin.maintenance.tyre-management.layout', 'order' => 6, 'permission' => 'view tyre management'],
        ];
        foreach ($maintenanceChildren as $child) {
            $child['parent_id'] = $maintenanceParent->id;
            Menu::create($child);
        }

        $reportsParent = Menu::create(['title' => 'Reports', 'icon' => 'bx bx-bar-chart-alt-2', 'order' => 10, 'permission' => 'view reports']);
        
        $reportsChildren = [
            ['title' => 'Vehicle Performance', 'icon' => 'bx bx-car', 'route_name' => 'admin.reports.vehicle', 'order' => 1, 'permission' => 'view vehicle report'],
            ['title' => 'Driver Trip Report', 'icon' => 'bx bx-user', 'route_name' => 'admin.reports.driver-trip', 'order' => 2, 'permission' => 'view driver trip report'],
            ['title' => 'Customer Ledger', 'icon' => 'bx bx-book', 'route_name' => 'admin.reports.customer-ledger', 'order' => 3, 'permission' => 'view customer ledger'],
            ['title' => 'Sales Ledger', 'icon' => 'bx bx-rupee', 'route_name' => 'admin.reports.sales-ledger', 'order' => 4, 'permission' => 'view sales ledger'],
            ['title' => 'TDS Report', 'icon' => 'bx bx-receipt', 'route_name' => 'admin.reports.tds-report', 'order' => 5, 'permission' => 'view tds report'],
            ['title' => 'Trip Reports', 'icon' => 'bx bx-detail', 'route_name' => 'admin.reports.trip-reports', 'order' => 6, 'permission' => 'view trip reports'],
            ['title' => 'Bilty Advance Details', 'icon' => 'bx bx-money', 'route_name' => 'admin.reports.bilty-advance-details.index', 'order' => 7, 'permission' => 'view bilty advance details'],
            ['title' => 'Fuel Report', 'icon' => 'bx bx-gas-pump', 'route_name' => 'admin.reports.fuel', 'order' => 8, 'permission' => 'view fuel report'],
            ['title' => 'AdBlue Report', 'icon' => 'bx bx-droplet', 'route_name' => 'admin.reports.adblue', 'order' => 9, 'permission' => 'view adblue report'],
            ['title' => 'Fuel Outstanding', 'icon' => 'bx bx-wallet', 'route_name' => 'admin.reports.fuel-outstanding', 'order' => 10, 'permission' => 'view fuel outstanding'],
            ['title' => 'AdBlue Outstanding', 'icon' => 'bx bx-water', 'route_name' => 'admin.reports.adblue-outstanding', 'order' => 11, 'permission' => 'view adblue outstanding'],
            ['title' => 'Vehicle Utilization', 'icon' => 'bx bx-line-chart', 'route_name' => 'admin.reports.vehicle-utilization', 'order' => 11, 'permission' => 'view vehicle utilization'],
            ['title' => 'MIS Report', 'icon' => 'bx bx-bar-chart-square', 'route_name' => 'admin.reports.mis', 'order' => 12, 'permission' => 'view mis report'],
            ['title' => 'Expense Management', 'icon' => 'bx bx-money', 'route_name' => 'admin.reports.expense-management', 'order' => 13, 'permission' => 'view expense management'],
            ['title' => 'Vehicle Documents', 'icon' => 'bx bx-file-blank', 'route_name' => 'admin.reports.vehicle-documents', 'order' => 14, 'permission' => 'view vehicle document report'],
            ['title' => 'GST & Tax', 'icon' => 'bx bx-receipt', 'route_name' => 'admin.reports.gst-tax', 'order' => 15, 'permission' => 'view gst tax report'],
            ['title' => 'Profit & Loss', 'icon' => 'bx bx-trending-up', 'route_name' => 'admin.reports.profit-loss', 'order' => 16, 'permission' => 'view profit loss report'],
        ];
        foreach ($reportsChildren as $child) {
            $child['parent_id'] = $reportsParent->id;
            Menu::create($child);
        }

        $mastersParent = Menu::create(['title' => 'Masters', 'icon' => 'bx bx-data', 'order' => 11]);
        
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
            ['title' => 'Tyre Brands', 'icon' => 'bx bx-disc', 'route_name' => 'admin.masters.tyre-brands.index', 'order' => 14, 'permission' => 'view tyre brands'],
            ['title' => 'Tyre Models', 'icon' => 'bx bx-layer', 'route_name' => 'admin.masters.tyre-models.index', 'order' => 15, 'permission' => 'view tyre models'],
            ['title' => 'Tyre Sizes', 'icon' => 'bx bx-ruler', 'route_name' => 'admin.masters.tyre-sizes.index', 'order' => 16, 'permission' => 'view tyre sizes'],
            ['title' => 'Items', 'icon' => 'bx bx-cube', 'route_name' => 'admin.masters.items.index', 'order' => 17, 'permission' => 'view items'],
            ['title' => 'Suppliers', 'icon' => 'bx bx-package', 'route_name' => 'admin.masters.suppliers.index', 'order' => 18, 'permission' => 'view suppliers'],
            ['title' => 'Vendors', 'icon' => 'bx bx-store', 'route_name' => 'admin.masters.vendors.index', 'order' => 19, 'permission' => 'view vendors'],
            ['title' => 'Banks', 'icon' => 'bx bx-building-house', 'route_name' => 'admin.masters.banks.index', 'order' => 20, 'permission' => 'view banks'],
            ['title' => 'Bank Branches', 'icon' => 'bx bx-git-branch', 'route_name' => 'admin.masters.bank-branches.index', 'order' => 21, 'permission' => 'view bank branches'],
            ['title' => 'Bill Formats', 'icon' => 'bx bx-file', 'route_name' => 'admin.masters.bill-formats.index', 'order' => 22, 'permission' => 'view bill formats'],
        ];
        foreach ($mastersChildren as $child) {
            $child['parent_id'] = $mastersParent->id;
            Menu::create($child);
        }

        $accessControlMenus = [
            ['title' => 'Access Control', 'menu_header' => 'Access Control', 'order' => 12],
            ['title' => 'Users', 'icon' => 'bx bx-user', 'route_name' => 'admin.users.index', 'order' => 13, 'permission' => 'view users'],
            ['title' => 'Roles', 'icon' => 'bx bx-shield-quarter', 'route_name' => 'admin.roles.index', 'order' => 14, 'permission' => 'view roles'],
            ['title' => 'Permissions', 'icon' => 'bx bx-key', 'route_name' => 'admin.permissions.index', 'order' => 15, 'permission' => 'view permissions'],
        ];
        foreach ($accessControlMenus as $menu) {
            Menu::create($menu);
        }

        $documentManagementParent = Menu::create(['title' => 'Document Management', 'menu_header' => 'Document Management', 'order' => 16]);
        
        $documentManagementChildren = [
            ['title' => 'Doc Dashboard', 'icon' => 'bx bx-grid-alt', 'route_name' => 'admin.documents.dashboard', 'order' => 1, 'permission' => 'view documents'],
            ['title' => 'Document Explorer', 'icon' => 'bx bx-folder-open', 'route_name' => 'admin.documents.index', 'order' => 2, 'permission' => 'view documents'],
            ['title' => 'Doc Categories', 'icon' => 'bx bx-category', 'route_name' => 'admin.documents.categories.index', 'order' => 3, 'permission' => 'manage categories'],
            ['title' => 'Doc Folders', 'icon' => 'bx bx-folder', 'route_name' => 'admin.documents.folders.index', 'order' => 4, 'permission' => 'manage folders'],
            ['title' => 'Expiry Alerts', 'icon' => 'bx bx-time-five', 'route_name' => 'admin.documents.reports.expiry', 'order' => 5, 'permission' => 'view document reports'],
            ['title' => 'Storage Usage', 'icon' => 'bx bx-pie-chart-alt-2', 'route_name' => 'admin.documents.reports.storage', 'order' => 6, 'permission' => 'view document reports'],
            ['title' => 'Doc Audit Trail', 'icon' => 'bx bx-list-check', 'route_name' => 'admin.documents.activity-logs', 'order' => 7, 'permission' => 'view activity'],
            ['title' => 'Doc Trash Bin', 'icon' => 'bx bx-trash', 'route_name' => 'admin.documents.trash', 'order' => 8, 'permission' => 'manage document trash'],
        ];
        foreach ($documentManagementChildren as $child) {
            $child['parent_id'] = $documentManagementParent->id;
            Menu::create($child);
        }

        $systemMenus = [
            ['title' => 'System', 'menu_header' => 'System', 'order' => 17],
            ['title' => 'Activity Logs', 'icon' => 'bx bx-history', 'route_name' => 'admin.activity-logs', 'order' => 18, 'permission' => 'view activity logs'],
            ['title' => 'Settings', 'icon' => 'bx bx-cog', 'route_name' => 'admin.settings', 'order' => 19, 'permission' => 'manage settings'],
        ];
        foreach ($systemMenus as $menu) {
            Menu::create($menu);
        }

        $this->command->info('Menus seeded successfully!');
    }
}