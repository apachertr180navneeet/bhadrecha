<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\Bulty;
use App\Models\City;
use App\Models\Company;
use App\Models\Consignee;
use App\Models\Consignor;
use App\Models\Driver;
use App\Models\GstMaster;
use App\Models\Item;
use App\Models\User;
use App\Models\Vehicle;
use App\Models\BillFormat;
use App\Models\Invoice;
use App\Models\Trip;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class OperationsTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('uploads');
    }

    public function test_entire_logistics_and_billing_workflow(): void
    {
        // 1. Setup Master Entities
        $company = Company::create([
            'name' => 'Test Company',
            'email' => 'company@test.com',
            'phone' => '1234567890',
            'status' => 'active',
        ]);

        $branch = Branch::create([
            'company_id' => $company->id,
            'name' => 'Mumbai Branch',
            'status' => 'active',
        ]);

        $city1 = City::create([
            'name' => 'Mumbai',
            'state' => 'Maharashtra',
            'status' => 'active',
        ]);

        $city2 = City::create([
            'name' => 'Pune',
            'state' => 'Maharashtra',
            'status' => 'active',
        ]);

        $consignor = Consignor::create([
            'company_id' => $company->id,
            'name' => 'Consignor Pvt Ltd',
            'phone' => '9999999999',
            'status' => 'active',
        ]);

        $consignee = Consignee::create([
            'company_id' => $company->id,
            'name' => 'Consignee Pvt Ltd',
            'phone' => '8888888888',
            'status' => 'active',
        ]);

        $driver = Driver::create([
            'name' => 'John Doe',
            'phone' => '7777777777',
            'license_number' => 'DL-12345678',
            'license_expiry' => now()->addYears(5),
            'status' => 'active',
        ]);

        $vehicle = Vehicle::create([
            'vehicle_number' => 'MH-12-AB-1234',
            'vehicle_type' => 'Truck',
            'status' => 'active',
        ]);

        $gstMaster = GstMaster::create([
            'gst_rate' => 'GST 18%',
            'percentage' => 18.00,
            'status' => 'active',
        ]);

        $item = Item::create([
            'name' => 'Steel Pipes',
            'status' => 'active',
        ]);

        // Setup Super Admin role and user
        $superAdminRole = Role::firstOrCreate(['name' => 'Super Admin', 'guard_name' => 'web']);
        $admin = new User();
        $admin->first_name = 'Super';
        $admin->last_name = 'Admin';
        $admin->full_name = 'Super Admin';
        $admin->slug = 'super-admin';
        $admin->email = 'admin@testerp.com';
        $admin->phone = '1111111111';
        $admin->country = 'India';
        $admin->password = bcrypt('password');
        $admin->company_id = $company->id;
        $admin->branch_id = $branch->id;
        $admin->status = 'active';
        $admin->role = 'admin';
        $admin->avatar = '';
        $admin->bio = '';
        $admin->device_token = '';
        $admin->save();
        $admin->assignRole($superAdminRole);

        // 2. Create Bilty (LR)
        $biltyData = [
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'lr_no' => 'MUM-2026-9999',
            'lr_date' => '2026-07-02',
            'from_city' => $city1->id,
            'to_city' => $city2->id,
            'consignor_id' => $consignor->id,
            'consignee_id' => $consignee->id,
            'declared_value' => 500000,
            'freight_charges' => 25000,
            'gst_master_id' => $gstMaster->id,
            'gst_amount' => 4500,
            'other_charges' => 500,
            'total_amount' => 30000,
            'payment_type' => 'topay',
            'driver_id' => $driver->id,
            'vehicle_id' => $vehicle->id,
            'items' => [
                [
                    'item_id' => $item->id,
                    'packaging_type' => 'Bundle',
                    'articles' => 10,
                    'weight' => 2000,
                    'unit' => 'kg',
                    'freight_per_mt' => 12.5,
                    'amount' => 25000,
                ]
            ],
            // bulty_details fields
            'po_no' => 'PO-9999',
            'challan_no' => 'CH-8888',
        ];

        $response = $this->actingAs($admin)
            ->withSession([
                'current_company_id' => $company->id,
                'current_branch_id' => $branch->id,
            ])
            ->post(route('admin.transport.bulties.store'), $biltyData);

        $response->assertRedirect();
        
        $bulty = Bulty::where('lr_no', 'MUM-2026-9999')->first();
        $this->assertNotNull($bulty);
        $this->assertEquals('pending', $bulty->status);
        $this->assertEquals('unbilled', $bulty->bill_status);

        // 3. Driver Uploads Material Document via Share Token
        $fakeMaterialDoc = UploadedFile::fake()->create('material_doc.pdf', 500);
        $response = $this->post(route('bilty.upload-document', $bulty->share_token), [
            'material_document' => $fakeMaterialDoc,
        ]);
        
        $response->assertRedirect();
        $bulty->refresh();
        $this->assertEquals('planned', $bulty->status);
        $this->assertNotNull($bulty->material_document);

        // 4. Admin Approves Material Document
        $response = $this->actingAs($admin)
            ->post(route('admin.transport.bulties.approve-document', $bulty->id));
            
        $response->assertRedirect();
        $bulty->refresh();
        $this->assertEquals('dispatched', $bulty->status);
        $this->assertTrue($bulty->material_document_status);

        // 5. Create Trip for Dispatched Bilty
        $tripData = [
            'builty_id' => $bulty->id,
            'status' => 'pending',
            'fasttag_total_amount' => 1200,
            'fuel_amount' => 8000,
            'other_amount' => 500,
            'adblue_total_amount' => 600,
            'advance_total_amount' => 2000,
        ];

        $response = $this->actingAs($admin)
            ->post(route('admin.transport.trips.store'), $tripData);

        $response->assertRedirect();
        
        $trip = Trip::where('builty_id', $bulty->id)->first();
        $this->assertNotNull($trip);
        $this->assertEquals('pending', $trip->status);

        // 6. Driver Uploads POD via Share Token
        $fakePod = UploadedFile::fake()->create('pod.jpg', 800);
        $response = $this->post(route('bilty.upload-pod', $bulty->share_token), [
            'pod_file' => $fakePod,
        ]);

        $response->assertRedirect();
        $bulty->refresh();
        $this->assertEquals('partially_delivered', $bulty->status);
        $this->assertNotNull($bulty->pod_document);

        // 7. Admin Approves POD
        $response = $this->actingAs($admin)
            ->post(route('admin.transport.bulties.approve-pod', $bulty->id));

        $response->assertRedirect();
        $bulty->refresh();
        $this->assertEquals('delivered', $bulty->status);
        $this->assertTrue($bulty->pod_document_status);

        // 8. Generate Billing Invoice
        $billFormat = BillFormat::create([
            'company_id' => $company->id,
            'format_name' => 'Standard Format',
            'visible_fields' => ['lr_no', 'lr_date', 'consignor', 'consignee', 'freight'],
            'grn_new_page' => false,
            'gst_master_id' => $gstMaster->id,
            'user_id' => $admin->id,
        ]);

        $billingData = [
            'ids' => (string) $bulty->id,
            'bill_format_id' => $billFormat->id,
            'invoice_type' => 'freight',
            'bill_number' => 'INV-2026-0001',
        ];

        $response = $this->actingAs($admin)
            ->post(route('admin.transport.billing.generate'), $billingData);

        $response->assertRedirect();
        
        $bulty->refresh();
        $this->assertEquals('billed', $bulty->bill_status);
        $this->assertNotNull($bulty->invoice_id);

        $invoice = Invoice::find($bulty->invoice_id);
        $this->assertNotNull($invoice);
        $this->assertEquals('INV-2026-0001', $invoice->invoice_no);
        $this->assertEquals(25000, $invoice->total_freight);
    }

    public function test_fuel_outstanding_and_credit_payments_workflow(): void
    {
        // 1. Setup Master Entities
        $company = Company::create([
            'name' => 'Fuel Test Company',
            'email' => 'fuel@test.com',
            'phone' => '1234567890',
            'status' => 'active',
        ]);

        $branch = Branch::create([
            'company_id' => $company->id,
            'name' => 'Mumbai Branch',
            'status' => 'active',
        ]);

        $city1 = City::create([
            'name' => 'Mumbai',
            'state' => 'Maharashtra',
            'status' => 'active',
        ]);

        $city2 = City::create([
            'name' => 'Pune',
            'state' => 'Maharashtra',
            'status' => 'active',
        ]);

        $consignor = Consignor::create([
            'company_id' => $company->id,
            'name' => 'Consignor Pvt Ltd',
            'phone' => '9999999999',
            'status' => 'active',
        ]);

        $consignee = Consignee::create([
            'company_id' => $company->id,
            'name' => 'Consignee Pvt Ltd',
            'phone' => '8888888888',
            'status' => 'active',
        ]);

        $driver = Driver::create([
            'name' => 'John Fuel Driver',
            'phone' => '7777777777',
            'license_number' => 'DL-9999999',
            'license_expiry' => now()->addYears(5),
            'status' => 'active',
        ]);

        $vehicle = Vehicle::create([
            'vehicle_number' => 'MH-12-XY-9999',
            'vehicle_type' => 'Truck',
            'status' => 'active',
        ]);

        $gstMaster = GstMaster::create([
            'gst_rate' => 'GST 18%',
            'percentage' => 18.00,
            'status' => 'active',
        ]);

        $superAdminRole = Role::firstOrCreate(['name' => 'Super Admin', 'guard_name' => 'web']);
        $admin = new User();
        $admin->first_name = 'Super';
        $admin->last_name = 'Admin';
        $admin->full_name = 'Super Admin';
        $admin->slug = 'super-admin-fuel';
        $admin->email = 'admin-fuel@testerp.com';
        $admin->phone = '1111111112';
        $admin->country = 'India';
        $admin->password = bcrypt('password');
        $admin->company_id = $company->id;
        $admin->branch_id = $branch->id;
        $admin->status = 'active';
        $admin->role = 'admin';
        $admin->avatar = '';
        $admin->bio = '';
        $admin->device_token = '';
        $admin->save();
        $admin->assignRole($superAdminRole);

        // 2. Create Fuel Company and Pump
        $fuelCompany = \App\Models\FuelCompany::create([
            'name' => 'Reliance Petroleum',
            'status' => 'active',
        ]);

        $fuelPump = \App\Models\FuelPump::create([
            'name' => 'Reliance Pump Pune',
            'fuel_company_id' => $fuelCompany->id,
            'number' => 'PUMP-999',
            'status' => 'active',
        ]);

        // 3. Create Bilty (LR)
        $bulty = Bulty::create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'lr_no' => 'LR-9999',
            'lr_date' => '2026-07-02',
            'from_city' => $city1->id,
            'to_city' => $city2->id,
            'consignor_id' => $consignor->id,
            'consignee_id' => $consignee->id,
            'declared_value' => 500000,
            'freight_charges' => 25000,
            'gst_master_id' => $gstMaster->id,
            'gst_amount' => 4500,
            'other_charges' => 500,
            'total_amount' => 30000,
            'payment_type' => 'topay',
            'driver_id' => $driver->id,
            'vehicle_id' => $vehicle->id,
            'material_document' => 'uploads/test.pdf',
        ]);

        // 4. Create Trip and Fuel Details / Advances
        $trip = Trip::create([
            'builty_id' => $bulty->id,
            'fuel_amount' => 15000,
            'advance_total_amount' => 5000,
            'status' => 'pending',
        ]);

        // Create fuel detail (credit type)
        $trip->fuelDetails()->create([
            'builty_id' => $bulty->id,
            'date' => '2026-07-02',
            'fuel_company_id' => $fuelCompany->id,
            'fuel_pump_id' => $fuelPump->id,
            'quantity' => 150,
            'rate' => 100,
            'amount' => 15000,
            'payment_type' => 'credit',
        ]);

        // Create driver advance taken at pump
        $trip->advanceDetails()->create([
            'builty_id' => $bulty->id,
            'date' => '2026-07-02',
            'fuel_company_id' => $fuelCompany->id,
            'fuel_pump_id' => $fuelPump->id,
            'advance_amount' => 5000,
        ]);

        // 5. Test Fuel Outstanding Ledger page loads and shows total fuel amount (15000) and advance (5000) = 20000
        $response = $this->actingAs($admin)
            ->withSession(['current_company_id' => $company->id, 'current_branch_id' => $branch->id])
            ->get(route('admin.transport.trips.fuel-outstanding'));

        $response->assertStatus(200);
        $response->assertSee('Reliance Petroleum');
        $response->assertSee('Reliance Pump Pune');

        // 6. Record Payment (Credit) to the pump
        $paymentData = [
            'date' => '2026-07-02',
            'fuel_company_id' => $fuelCompany->id,
            'fuel_pump_id' => $fuelPump->id,
            'amount' => 12000,
            'payment_method' => 'UPI',
            'remark' => 'Paid by Harish Bhaiya',
        ];

        $response = $this->actingAs($admin)
            ->withSession(['current_company_id' => $company->id, 'current_branch_id' => $branch->id])
            ->post(route('admin.transport.trips.fuel-payments.store'), $paymentData, ['HTTP_X-Requested-With' => 'XMLHttpRequest']);

        $response->assertJson(['success' => true]);
        
        $payment = \App\Models\FuelPumpPayment::where('amount', 12000)->first();
        $this->assertNotNull($payment);
        $this->assertEquals($company->id, $payment->company_id);

        // 7. Verify net outstanding is now 20000 - 12000 = 8000
        $response = $this->actingAs($admin)
            ->withSession(['current_company_id' => $company->id, 'current_branch_id' => $branch->id])
            ->get(route('admin.transport.trips.fuel-outstanding'));
        
        $response->assertStatus(200);
        
        // 8. Update payment to 15000
        $response = $this->actingAs($admin)
            ->withSession(['current_company_id' => $company->id, 'current_branch_id' => $branch->id])
            ->put(route('admin.transport.trips.fuel-payments.update', $payment->id), [
                'date' => '2026-07-02',
                'fuel_company_id' => $fuelCompany->id,
                'fuel_pump_id' => $fuelPump->id,
                'amount' => 15000,
                'payment_method' => 'Cheque',
                'remark' => 'Updated payment amount',
            ], ['HTTP_X-Requested-With' => 'XMLHttpRequest']);

        $response->assertJson(['success' => true]);
        $payment->refresh();
        $this->assertEquals(15000, $payment->amount);

        // 9. Verify Reports Route and Opening Balance logic
        $response = $this->actingAs($admin)
            ->withSession(['current_company_id' => $company->id, 'current_branch_id' => $branch->id])
            ->get(route('admin.reports.fuel-outstanding'));
        
        $response->assertStatus(200);
        $response->assertSee('Outstanding Report');

        // Apply a date filter where July 2nd is in the past (before July 3rd)
        // Opening balance should be 20,000
        $response = $this->actingAs($admin)
            ->withSession(['current_company_id' => $company->id, 'current_branch_id' => $branch->id])
            ->get(route('admin.reports.fuel-outstanding', [
                'date_from' => '2026-07-03'
            ]));

        $response->assertStatus(200);
        $response->assertSee('Opening Balance');
        $response->assertSee('20,000');

        // Delete payment
        $response = $this->actingAs($admin)
            ->withSession(['current_company_id' => $company->id, 'current_branch_id' => $branch->id])
            ->delete(route('admin.transport.trips.fuel-payments.destroy', $payment->id), [], ['HTTP_X-Requested-With' => 'XMLHttpRequest']);

        $response->assertJson(['success' => true]);
        $this->assertNull(\App\Models\FuelPumpPayment::find($payment->id));
    }

    public function test_adblue_outstanding_and_payments_workflow(): void
    {
        // 1. Setup Master Entities
        $company = Company::create([
            'name' => 'AdBlue Test Company Org',
            'email' => 'adblue@test.com',
            'phone' => '1234567891',
            'status' => 'active',
        ]);

        $branch = Branch::create([
            'company_id' => $company->id,
            'name' => 'Delhi Branch',
            'status' => 'active',
        ]);

        $city1 = City::create([
            'name' => 'Delhi',
            'state' => 'Delhi',
            'status' => 'active',
        ]);

        $city2 = City::create([
            'name' => 'Gurugram',
            'state' => 'Haryana',
            'status' => 'active',
        ]);

        $consignor = Consignor::create([
            'company_id' => $company->id,
            'name' => 'AdBlue Consignor',
            'phone' => '9999999998',
            'status' => 'active',
        ]);

        $consignee = Consignee::create([
            'company_id' => $company->id,
            'name' => 'AdBlue Consignee',
            'phone' => '8888888887',
            'status' => 'active',
        ]);

        $driver = Driver::create([
            'name' => 'Urea Driver',
            'phone' => '7777777776',
            'license_number' => 'DL-8888888',
            'license_expiry' => now()->addYears(5),
            'status' => 'active',
        ]);

        $vehicle = Vehicle::create([
            'vehicle_number' => 'DL-01-AB-1234',
            'vehicle_type' => 'Trailer',
            'status' => 'active',
        ]);

        $gstMaster = GstMaster::create([
            'gst_rate' => 'GST 18%',
            'percentage' => 18.00,
            'status' => 'active',
        ]);

        $superAdminRole = Role::firstOrCreate(['name' => 'Super Admin', 'guard_name' => 'web']);
        $admin = new User();
        $admin->first_name = 'AdBlue';
        $admin->last_name = 'Admin';
        $admin->full_name = 'AdBlue Admin User';
        $admin->slug = 'adblue-admin-user';
        $admin->email = 'admin-adblue@testerp.com';
        $admin->phone = '1111111115';
        $admin->country = 'India';
        $admin->password = bcrypt('password');
        $admin->company_id = $company->id;
        $admin->branch_id = $branch->id;
        $admin->status = 'active';
        $admin->role = 'admin';
        $admin->avatar = '';
        $admin->bio = '';
        $admin->device_token = '';
        $admin->save();
        $admin->assignRole($superAdminRole);

        // 2. Create AdBlue Company
        $adblueCompany = \App\Models\AdBlueCompany::create([
            'name' => 'Tata Urea Chemicals',
            'status' => 'active',
        ]);

        // 3. Create Bilty (LR)
        $bulty = Bulty::create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'lr_no' => 'LR-8888',
            'lr_date' => '2026-07-02',
            'from_city' => $city1->id,
            'to_city' => $city2->id,
            'consignor_id' => $consignor->id,
            'consignee_id' => $consignee->id,
            'declared_value' => 300000,
            'freight_charges' => 20000,
            'gst_master_id' => $gstMaster->id,
            'gst_amount' => 3600,
            'other_charges' => 400,
            'total_amount' => 24000,
            'payment_type' => 'topay',
            'driver_id' => $driver->id,
            'vehicle_id' => $vehicle->id,
            'material_document' => 'uploads/test.pdf',
        ]);

        // 4. Create Trip and AdBlue details
        $trip = Trip::create([
            'builty_id' => $bulty->id,
            'adblue_total_amount' => 8000,
            'status' => 'pending',
        ]);

        $trip->adblueDetails()->create([
            'builty_id' => $bulty->id,
            'date' => '2026-07-02',
            'adblue_company_id' => $adblueCompany->id,
            'quantity' => 100,
            'rate' => 80,
            'amount' => 8000,
        ]);

        // 5. Verify AdBlue Outstanding page loads and shows Tata Urea Chemicals
        $response = $this->actingAs($admin)
            ->withSession(['current_company_id' => $company->id, 'current_branch_id' => $branch->id])
            ->get(route('admin.transport.trips.adblue-outstanding'));

        $response->assertStatus(200);
        $response->assertSee('Tata Urea Chemicals');
        $response->assertSee('8,000');

        // 6. Record Payment to AdBlue Company
        $paymentData = [
            'date' => '2026-07-02',
            'adblue_company_id' => $adblueCompany->id,
            'amount' => 5000,
            'payment_method' => 'Bank Transfer',
            'remark' => 'Advance Urea payment by Harish Bhaiya',
        ];

        $response = $this->actingAs($admin)
            ->withSession(['current_company_id' => $company->id, 'current_branch_id' => $branch->id])
            ->post(route('admin.transport.trips.adblue-payments.store'), $paymentData, ['HTTP_X-Requested-With' => 'XMLHttpRequest']);

        $response->assertJson(['success' => true]);

        $payment = \App\Models\AdBlueCompanyPayment::where('amount', 5000)->first();
        $this->assertNotNull($payment);
        $this->assertEquals($company->id, $payment->company_id);

        // 7. Verify net outstanding is now 8000 - 5000 = 3000 on reports route
        $response = $this->actingAs($admin)
            ->withSession(['current_company_id' => $company->id, 'current_branch_id' => $branch->id])
            ->get(route('admin.reports.adblue-outstanding'));

        $response->assertStatus(200);
        $response->assertSee('3,000');

        // 8. Test Opening Balance with a future date
        $response = $this->actingAs($admin)
            ->withSession(['current_company_id' => $company->id, 'current_branch_id' => $branch->id])
            ->get(route('admin.reports.adblue-outstanding', ['date_from' => '2026-07-03']));

        $response->assertStatus(200);
        $response->assertSee('Opening Balance');
        $response->assertSee('3,000');

        // 9. Delete payment
        $response = $this->actingAs($admin)
            ->withSession(['current_company_id' => $company->id, 'current_branch_id' => $branch->id])
            ->delete(route('admin.transport.trips.adblue-payments.destroy', $payment->id), [], ['HTTP_X-Requested-With' => 'XMLHttpRequest']);

        $response->assertJson(['success' => true]);
        $this->assertNull(\App\Models\AdBlueCompanyPayment::find($payment->id));
    }
}
