<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Company;
use App\Models\Branch;
use App\Models\Route;
use App\Models\Bulty;

use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class TransportSeeder extends Seeder
{
    public function run(): void
    {
        // Get existing Super Admin (created in RolePermissionSeeder with email superadmin@mailinator.com)
        $superAdmin = User::where('email', 'superadmin@mailinator.com')->first();
        
        if (!$superAdmin) {
            $superAdmin = User::create([
                'first_name' => 'Super',
                'last_name' => 'Admin',
                'full_name' => 'Super Admin',
                'slug' => 'super-admin',
                'email' => 'superadmin@transporter.com',
                'phone' => '9876543210',
                'password' => Hash::make('password123'),
                'status' => 'active',
                'role' => 'admin',
                'country' => 'India',
                'state' => 'Rajasthan',
                'city' => 'Udaipur',
            ]);
            $superRole = Role::firstOrCreate(['name' => 'Super Admin']);
            $superAdmin->assignRole($superRole);
        }

        // Create Company 1
        $company1 = Company::create([
            'name' => 'ABC Logistics',
            'email' => 'info@abclogistics.com',
            'phone' => '9876543211',
            'address' => '123 Transport Nagar, Udaipur',
            'status' => 'active',
        ]);

        // Create Company 2
        $company2 = Company::create([
            'name' => 'XYZ Transport',
            'email' => 'contact@xyztransport.com',
            'phone' => '9876543212',
            'address' => '456 Highway Road, Ahmedabad',
            'status' => 'active',
        ]);

        // Branches for Company 1
        $branch1 = Branch::create([
            'company_id' => $company1->id,
            'name' => 'Udaipur Head Office',
            'email' => 'udaipur@abclogistics.com',
            'phone' => '9876543220',
            'address' => 'Head Office, Udaipur',
            'city' => 'Udaipur',
            'state' => 'Rajasthan',
            'status' => 'active',
        ]);

        $branch2 = Branch::create([
            'company_id' => $company1->id,
            'name' => 'Jaipur Branch',
            'email' => 'jaipur@abclogistics.com',
            'phone' => '9876543221',
            'address' => 'Branch Office, Jaipur',
            'city' => 'Jaipur',
            'state' => 'Rajasthan',
            'status' => 'active',
        ]);

        // Branch for Company 2
        $branch3 = Branch::create([
            'company_id' => $company2->id,
            'name' => 'Ahmedabad Head Office',
            'email' => 'ahmedabad@xyztransport.com',
            'phone' => '9876543222',
            'address' => 'Main Office, Ahmedabad',
            'city' => 'Ahmedabad',
            'state' => 'Gujarat',
            'status' => 'active',
        ]);

        // Company Admin Users
        $companyAdmin1 = User::create([
            'first_name' => 'Rajesh',
            'last_name' => 'Kumar',
            'full_name' => 'Rajesh Kumar',
            'slug' => 'rajesh-kumar',
            'email' => 'rajesh@abclogistics.com',
            'phone' => '9876543230',
            'password' => Hash::make('password123'),
            'status' => 'active',
            'role' => 'admin',
            'company_id' => $company1->id,
            'branch_id' => $branch1->id,
            'country' => 'India',
            'state' => 'Rajasthan',
            'city' => 'Udaipur',
        ]);
        $companyRole = Role::firstOrCreate(['name' => 'Company Admin']);
        $companyAdmin1->assignRole($companyRole);

        $companyAdmin2 = User::create([
            'first_name' => 'Suresh',
            'last_name' => 'Patel',
            'full_name' => 'Suresh Patel',
            'slug' => 'suresh-patel',
            'email' => 'suresh@xyztransport.com',
            'phone' => '9876543231',
            'password' => Hash::make('password123'),
            'status' => 'active',
            'role' => 'admin',
            'company_id' => $company2->id,
            'branch_id' => $branch3->id,
            'country' => 'India',
            'state' => 'Gujarat',
            'city' => 'Ahmedabad',
        ]);
        $companyAdmin2->assignRole($companyRole);

        // Branch Manager User
        $branchManager = User::create([
            'first_name' => 'Vikram',
            'last_name' => 'Singh',
            'full_name' => 'Vikram Singh',
            'slug' => 'vikram-singh',
            'email' => 'vikram@abclogistics.com',
            'phone' => '9876543240',
            'password' => Hash::make('password123'),
            'status' => 'active',
            'role' => 'user',
            'company_id' => $company1->id,
            'branch_id' => $branch2->id,
            'country' => 'India',
            'state' => 'Rajasthan',
            'city' => 'Jaipur',
        ]);
        $managerRole = Role::firstOrCreate(['name' => 'Branch Manager']);
        $branchManager->assignRole($managerRole);

        // Routes for Company 1
        $route1 = Route::create([
            'company_id' => $company1->id,
            'name' => 'Udaipur - Jaipur',
            'from_city' => 'Udaipur',
            'to_city' => 'Jaipur',
            'distance_km' => 395,
            'estimated_hours' => 6.5,
            'toll_estimate' => 450,
            'status' => 'active',
        ]);

        $route2 = Route::create([
            'company_id' => $company1->id,
            'name' => 'Udaipur - Ahmedabad',
            'from_city' => 'Udaipur',
            'to_city' => 'Ahmedabad',
            'distance_km' => 265,
            'estimated_hours' => 5.0,
            'toll_estimate' => 300,
            'status' => 'active',
        ]);

        $route3 = Route::create([
            'company_id' => $company1->id,
            'name' => 'Jaipur - Delhi',
            'from_city' => 'Jaipur',
            'to_city' => 'Delhi',
            'distance_km' => 280,
            'estimated_hours' => 4.5,
            'toll_estimate' => 350,
            'status' => 'active',
        ]);

        // Routes for Company 2
        $route4 = Route::create([
            'company_id' => $company2->id,
            'name' => 'Ahmedabad - Mumbai',
            'from_city' => 'Ahmedabad',
            'to_city' => 'Mumbai',
            'distance_km' => 530,
            'estimated_hours' => 8.0,
            'toll_estimate' => 650,
            'status' => 'active',
        ]);

        $route5 = Route::create([
            'company_id' => $company2->id,
            'name' => 'Ahmedabad - Surat',
            'from_city' => 'Ahmedabad',
            'to_city' => 'Surat',
            'distance_km' => 260,
            'estimated_hours' => 4.5,
            'toll_estimate' => 280,
            'status' => 'active',
        ]);

        // Bulties for Company 1
        $bulty1 = Bulty::create([
            'company_id' => $company1->id,
            'branch_id' => $branch1->id,
            'route_id' => $route1->id,
            'lr_no' => 'LR-' . date('Y') . '-000001',
            'lr_date' => '2026-05-01',
            'consignor_name' => 'Tata Steel Ltd',
            'consignor_phone' => '9876500001',
            'consignor_address' => 'Industrial Area, Udaipur',
            'consignor_gstin' => '08AABCT1234F1Z5',
            'consignee_name' => 'Rajasthan Infrastructure',
            'consignee_phone' => '9876500002',
            'consignee_address' => 'Site No 45, Jaipur',
            'consignee_gstin' => '08AABCR5678G1Z3',
            'pickup_location' => 'Udaipur',
            'delivery_location' => 'Jaipur',
            'goods_description' => 'Steel Rods',
            'quantity' => 50,
            'weight' => 5000,
            'declared_value' => 250000,
            'freight_charges' => 15000,
            'gst_amount' => 2700,
            'other_charges' => 500,
            'total_amount' => 18200,
            'payment_type' => 'topay',
            'gst_type' => 'intrastate',
            'status' => 'planned',
            'created_by' => $companyAdmin1->id,
        ]);

        $bulty2 = Bulty::create([
            'company_id' => $company1->id,
            'branch_id' => $branch1->id,
            'route_id' => $route2->id,
            'lr_no' => 'LR-' . date('Y') . '-000002',
            'lr_date' => '2026-05-02',
            'consignor_name' => 'Marble Industries',
            'consignor_phone' => '9876500003',
            'consignor_address' => 'Rishabhdeo Road, Udaipur',
            'consignor_gstin' => '08AABCM9012H1Z1',
            'consignee_name' => 'Gujarat Builders',
            'consignee_phone' => '9876500004',
            'consignee_address' => 'GIDC, Ahmedabad',
            'consignee_gstin' => '24AABCG3456I1Z8',
            'pickup_location' => 'Udaipur',
            'delivery_location' => 'Ahmedabad',
            'goods_description' => 'Marble Slabs',
            'quantity' => 30,
            'weight' => 3000,
            'declared_value' => 180000,
            'freight_charges' => 10000,
            'gst_amount' => 1800,
            'other_charges' => 300,
            'total_amount' => 12100,
            'payment_type' => 'paid',
            'gst_type' => 'interstate',
            'status' => 'pending',
            'created_by' => $companyAdmin1->id,
        ]);

        $bulty3 = Bulty::create([
            'company_id' => $company1->id,
            'branch_id' => $branch2->id,
            'route_id' => $route3->id,
            'lr_no' => 'LR-' . date('Y') . '-000003',
            'lr_date' => '2026-05-03',
            'consignor_name' => 'Jaipur Textiles',
            'consignor_phone' => '9876500005',
            'consignor_address' => 'Johari Bazaar, Jaipur',
            'consignor_gstin' => '08AABCT7890J1Z6',
            'consignee_name' => 'Delhi Fashion Hub',
            'consignee_phone' => '9876500006',
            'consignee_address' => 'Chandni Chowk, Delhi',
            'consignee_gstin' => '07AABCD1234K1Z9',
            'pickup_location' => 'Jaipur',
            'delivery_location' => 'Delhi',
            'goods_description' => 'Cotton Fabrics',
            'quantity' => 100,
            'weight' => 2000,
            'declared_value' => 150000,
            'freight_charges' => 8000,
            'gst_amount' => 1440,
            'other_charges' => 200,
            'total_amount' => 9640,
            'payment_type' => 'topay',
            'gst_type' => 'interstate',
            'status' => 'pending',
            'created_by' => $branchManager->id,
        ]);

        // Bulties for Company 2
        $bulty4 = Bulty::create([
            'company_id' => $company2->id,
            'branch_id' => $branch3->id,
            'route_id' => $route4->id,
            'lr_no' => 'LR-' . date('Y') . '-000004',
            'lr_date' => '2026-05-01',
            'consignor_name' => 'Gujarat Chemicals',
            'consignor_phone' => '9876500007',
            'consignor_address' => 'GIDC Phase 2, Ahmedabad',
            'consignor_gstin' => '24AABCG5678L1Z2',
            'consignee_name' => 'Mumbai Pharma',
            'consignee_phone' => '9876500008',
            'consignee_address' => 'Andheri East, Mumbai',
            'consignee_gstin' => '27AABCM9012M1Z4',
            'pickup_location' => 'Ahmedabad',
            'delivery_location' => 'Mumbai',
            'goods_description' => 'Chemical Raw Materials',
            'quantity' => 20,
            'weight' => 4000,
            'declared_value' => 300000,
            'freight_charges' => 18000,
            'gst_amount' => 3240,
            'other_charges' => 600,
            'total_amount' => 21840,
            'payment_type' => 'tobill',
            'gst_type' => 'intrastate',
            'status' => 'pending',
            'created_by' => $companyAdmin2->id,
        ]);

        $this->command->info('✅ Sample data seeded successfully!');
        $this->command->info('');
        $this->command->info('Login Credentials:');
        $this->command->info('Super Admin: superadmin@transporter.com / password123');
        $this->command->info('Company 1 Admin: rajesh@abclogistics.com / password123');
        $this->command->info('Company 2 Admin: suresh@xyztransport.com / password123');
        $this->command->info('Branch Manager: vikram@abclogistics.com / password123');
    }
}
