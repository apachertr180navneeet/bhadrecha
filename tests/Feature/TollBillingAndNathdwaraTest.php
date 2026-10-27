<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Company;
use App\Models\Branch;
use App\Models\Consignor;
use App\Models\Consignee;
use App\Models\City;
use App\Models\Vehicle;
use App\Models\Driver;
use App\Models\Trip;
use App\Models\Bulty;
use App\Models\Invoice;
use App\Models\TripFastTagDetail;
use App\Models\GstMaster;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Request;
use Tests\TestCase;
use Spatie\Permission\Models\Role;

class TollBillingAndNathdwaraTest extends TestCase
{
    use DatabaseTransactions;

    protected $adminUser;
    protected $company;
    protected $branch;
    protected $consignor;
    protected $consignee;
    protected $cityOrigin;
    protected $cityDest;
    protected $vehicle;
    protected $driver;
    protected $gstMaster;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'database.default' => 'mysql',
            'database.connections.mysql.database' => 'gsgwevi6_bhadrecha',
        ]);
        \Illuminate\Support\Facades\DB::purge('mysql');

        $role = Role::firstOrCreate(['name' => 'Super Admin', 'guard_name' => 'web']);
        $this->adminUser = User::first();
        if (!$this->adminUser) {
            $this->adminUser = new User();
            $this->adminUser->first_name = 'Toll';
            $this->adminUser->last_name = 'Admin';
            $this->adminUser->full_name = 'Toll Admin';
            $this->adminUser->slug = 'toll-admin';
            $this->adminUser->email = 'admin_toll_' . uniqid() . '@example.com';
            $this->adminUser->phone = '9' . rand(100000000, 999999999);
            $this->adminUser->password = bcrypt('secret123');
            $this->adminUser->status = 'active';
            $this->adminUser->save();
        }
        if (!$this->adminUser->hasRole('Super Admin')) {
            $this->adminUser->assignRole($role);
        }

        $this->company = Company::firstOrCreate(
            ['name' => 'Test Transport Logistics'],
            [
                'state' => 'RAJASTHAN',
                'gst_number' => '08ABCDE1234F1Z5',
                'status' => 'active',
                'hsn_code' => '996511',
            ]
        );

        $this->branch = Branch::firstOrCreate(
            ['company_id' => $this->company->id, 'name' => 'Jaipur Main Branch'],
            ['code' => 'JPR01', 'status' => 'active']
        );

        $this->consignor = Consignor::firstOrCreate(
            ['name' => 'UltraTech Nathdwara Cement Unit'],
            [
                'company_id' => $this->company->id,
                'phone' => '9829012345',
                'state' => 'RAJASTHAN',
                'city' => 'Nathdwara',
                'address' => 'Nathdwara Works, Rajasthan',
                'status' => 'active',
            ]
        );

        $this->consignee = Consignee::firstOrCreate(
            ['name' => 'Birla White Depot'],
            [
                'company_id' => $this->company->id,
                'phone' => '9829054321',
                'state' => 'RAJASTHAN',
                'city' => 'Jodhpur',
                'address' => 'Kharia Khangar, Jodhpur',
                'status' => 'active',
            ]
        );

        $this->cityOrigin = City::firstOrCreate(['name' => 'Nathdwara'], ['state' => 'RAJASTHAN']);
        $this->cityDest = City::firstOrCreate(['name' => 'Jodhpur'], ['state' => 'RAJASTHAN']);

        $this->vehicle = Vehicle::firstOrCreate(
            ['vehicle_number' => 'RJ14GB9999'],
            ['status' => 'active']
        );

        $this->driver = Driver::firstOrCreate(
            ['name' => 'Ramesh Toll Driver'],
            ['phone' => '9898989898', 'license_number' => 'DL-9999999', 'status' => 'active']
        );

        $this->gstMaster = GstMaster::first() ?: GstMaster::create([
            'gst_rate' => '18%',
            'percentage' => 18.00,
            'status' => 'active',
        ]);
    }

    /**
     * Requirement 1: Verify No. of LR maps specifically to NUMBER OF DI for Nathdwara bill format.
     */
    public function test_nathdwara_template_displays_entered_no_of_lrs_in_no_of_di_field()
    {
        $this->actingAs($this->adminUser);

        $lrNo = 'LR-NATH-' . uniqid();
        $invNo = 'INV-NATH-' . uniqid();
        $billNo = 'BILL-NATH-' . uniqid();

        $bulty = Bulty::create([
            'lr_no' => $lrNo,
            'lr_date' => now()->toDateString(),
            'company_id' => $this->company->id,
            'branch_id' => $this->branch->id,
            'consignor_id' => $this->consignor->id,
            'consignee_id' => $this->consignee->id,
            'from_city_id' => $this->cityOrigin->id,
            'to_city_id' => $this->cityDest->id,
            'vehicle_id' => $this->vehicle->id,
            'driver_id' => $this->driver->id,
            'freight_charges' => 20000,
            'total_amount' => 20000,
            'bill_status' => 'billed',
        ]);

        $trip = Trip::create([
            'builty_id' => $bulty->id,
            'status' => 'complete',
        ]);
        $bulty->update(['trip_id' => $trip->id]);

        $invoice = Invoice::create([
            'invoice_no' => $invNo,
            'bill_number' => $billNo,
            'company_id' => $this->company->id,
            'branch_id' => $this->branch->id,
            'consignor_id' => $this->consignor->id,
            'invoice_type' => 'freight',
            'template_type' => 'nathdwara',
            'invoice_date' => now(),
            'total_freight' => 20000,
            'total_amount' => 20000,
            'status' => 'pending',
            'no_of_lrs' => 45, // user entered 45 LRs (while bulties count is 1)
        ]);

        $bulty->update(['invoice_id' => $invoice->id]);

        // 1. Check Nathdwara Show View
        $response = $this->get(route('admin.transport.invoices.show', $invoice->id));
        $response->assertStatus(200);
        $content = $response->getContent();
        $this->assertStringContainsString('NUMBER OF DI', $content);
        $this->assertStringContainsString('<td style="border-right: 2px solid #000; padding: 8px;">45</td>', $content);

        // 2. Check Bill Generate View displays total LRs
        $billGenResponse = $this->get(route('admin.transport.invoices.bill-generate', $invoice->id));
        $billGenResponse->assertStatus(200);
        $this->assertStringContainsString('Total LRs: 45', $billGenResponse->getContent());
    }

    /**
     * Requirement 3: Toll Bill defaults to Pending status even if source freight bill was Paid.
     */
    public function test_toll_bill_creation_defaults_to_pending_status_even_if_freight_bill_was_paid()
    {
        $this->actingAs($this->adminUser);

        $lrNo = 'LR-TOLL-' . uniqid();
        $frtInvNo = 'INV-FRT-' . uniqid();
        $tollBillNo = 'TOLL-BILL-' . uniqid();

        $bulty = Bulty::create([
            'lr_no' => $lrNo,
            'lr_date' => now()->toDateString(),
            'company_id' => $this->company->id,
            'branch_id' => $this->branch->id,
            'consignor_id' => $this->consignor->id,
            'consignee_id' => $this->consignee->id,
            'from_city_id' => $this->cityOrigin->id,
            'to_city_id' => $this->cityDest->id,
            'vehicle_id' => $this->vehicle->id,
            'driver_id' => $this->driver->id,
            'freight_charges' => 15000,
            'total_amount' => 15000,
            'bill_status' => 'billed',
        ]);

        $trip = Trip::create([
            'builty_id' => $bulty->id,
            'status' => 'complete',
        ]);
        $bulty->update(['trip_id' => $trip->id]);

        TripFastTagDetail::create([
            'trip_id' => $trip->id,
            'builty_id' => $bulty->id,
            'location' => 'KISHANGARH TOLL',
            'one_way' => 350.00,
            'return' => 250.00,
            'amount' => 0.00,
        ]);

        // Freight invoice is marked as PAID
        $freightInvoice = Invoice::create([
            'invoice_no' => $frtInvNo,
            'company_id' => $this->company->id,
            'branch_id' => $this->branch->id,
            'consignor_id' => $this->consignor->id,
            'invoice_type' => 'freight',
            'invoice_date' => now(),
            'total_freight' => 15000,
            'total_amount' => 15000,
            'status' => 'paid',
        ]);
        $bulty->update(['invoice_id' => $freightInvoice->id]);

        // Opening toll-print preview from freight invoice
        $printResponse = $this->get(route('admin.transport.invoices.toll-print', $freightInvoice->id));
        $printResponse->assertStatus(200);
        // The default payment status should be pending in the selector
        $this->assertStringContainsString('<option value="pending" selected>Pending</option>', $printResponse->getContent());

        // Saving toll invoice without explicit status (or with default)
        $saveResponse = $this->post(route('admin.transport.invoices.save-toll', $freightInvoice->id), [
            'bill_number' => $tollBillNo,
            'gst_type' => 'CGST_SGST',
            'status' => 'pending',
        ]);
        $saveResponse->assertRedirect(route('admin.transport.toll-bills.index'));

        $savedToll = Invoice::where('bill_number', $tollBillNo)->first();
        $this->assertNotNull($savedToll);
        $this->assertEquals('toll', $savedToll->invoice_type);
        $this->assertEquals('pending', $savedToll->status, 'Toll bill must default to pending status');
    }

    /**
     * Requirement 4: GST Type selection (CGST+SGST vs IGST) persists correctly across save and edit.
     */
    public function test_toll_bill_gst_type_selection_persists_cgst_sgst_and_igst_accurately()
    {
        $this->actingAs($this->adminUser);

        $lrNo = 'LR-GST-' . uniqid();
        $frtInvNo = 'INV-GST-' . uniqid();
        $tollBillNo = 'TOLL-GST-' . uniqid();

        $bulty = Bulty::create([
            'lr_no' => $lrNo,
            'lr_date' => now()->toDateString(),
            'company_id' => $this->company->id,
            'branch_id' => $this->branch->id,
            'consignor_id' => $this->consignor->id,
            'consignee_id' => $this->consignee->id,
            'from_city_id' => $this->cityOrigin->id,
            'to_city_id' => $this->cityDest->id,
            'vehicle_id' => $this->vehicle->id,
            'driver_id' => $this->driver->id,
            'freight_charges' => 10000,
            'total_amount' => 10000,
            'bill_status' => 'billed',
        ]);

        $trip = Trip::create([
            'builty_id' => $bulty->id,
            'status' => 'complete',
        ]);
        $bulty->update(['trip_id' => $trip->id]);

        TripFastTagDetail::create([
            'trip_id' => $trip->id,
            'builty_id' => $bulty->id,
            'location' => 'BEAWAR TOLL',
            'one_way' => 400.00,
            'return' => 400.00,
            'amount' => 0.00,
        ]);

        $freightInvoice = Invoice::create([
            'invoice_no' => $frtInvNo,
            'company_id' => $this->company->id,
            'branch_id' => $this->branch->id,
            'consignor_id' => $this->consignor->id,
            'invoice_type' => 'freight',
            'gst_type' => 'IGST', // source freight bill had IGST
            'invoice_date' => now(),
            'total_freight' => 10000,
            'total_amount' => 10000,
            'status' => 'pending',
            'gst_master_id' => $this->gstMaster->id,
        ]);
        $bulty->update(['invoice_id' => $freightInvoice->id]);

        // 1. User selects CGST_SGST explicitly when saving toll bill
        $this->post(route('admin.transport.invoices.save-toll', $freightInvoice->id), [
            'bill_number' => $tollBillNo,
            'gst_type' => 'CGST_SGST',
            'status' => 'pending',
        ]);

        $tollInvoice = Invoice::where('bill_number', $tollBillNo)->first();
        $this->assertNotNull($tollInvoice);
        $this->assertEquals('CGST_SGST', $tollInvoice->gst_type, 'User selected CGST_SGST must be persisted');
        $this->assertEquals(800.00, (float)$tollInvoice->total_freight); // 400 + 400
        $this->assertEquals(144.00, (float)$tollInvoice->total_gst); // 18% of 800
        $this->assertEquals(72.00, (float)$tollInvoice->cgst_amount);
        $this->assertEquals(72.00, (float)$tollInvoice->sgst_amount);
        $this->assertEquals(0.00, (float)$tollInvoice->igst_amount);
        $this->assertEquals(944.00, (float)$tollInvoice->total_amount);

        // 2. Reopening toll invoice: should keep CGST_SGST
        $reopenResponse = $this->get(route('admin.transport.invoices.toll-print', $tollInvoice->id));
        $reopenResponse->assertStatus(200);
        $this->assertStringContainsString('<option value="CGST_SGST" selected>CGST + SGST</option>', $reopenResponse->getContent());

        // 3. User edits toll bill and switches to IGST
        $this->post(route('admin.transport.invoices.save-toll', $tollInvoice->id), [
            'bill_number' => $tollBillNo,
            'gst_type' => 'IGST',
            'status' => 'paid',
        ]);

        $tollInvoice->refresh();
        $this->assertEquals('IGST', $tollInvoice->gst_type, 'Updated GST type IGST must be persisted');
        $this->assertEquals('paid', $tollInvoice->status, 'Updated status paid must be persisted');
        $this->assertEquals(0.00, (float)$tollInvoice->cgst_amount);
        $this->assertEquals(0.00, (float)$tollInvoice->sgst_amount);
        $this->assertEquals(144.00, (float)$tollInvoice->igst_amount);
        $this->assertEquals(944.00, (float)$tollInvoice->total_amount);

        // 4. Reopening again: should now show IGST selected and paid status selected
        $reopenResponse2 = $this->get(route('admin.transport.invoices.toll-print', $tollInvoice->id));
        $reopenResponse2->assertStatus(200);
        $this->assertStringContainsString('<option value="IGST" selected>IGST</option>', $reopenResponse2->getContent());
        $this->assertStringContainsString('<option value="paid" selected>Paid</option>', $reopenResponse2->getContent());
    }

    /**
     * Requirement 2 & 5: Toll Bill Dashboard & Invoice History calculation accuracy.
     */
    public function test_toll_bill_dashboard_and_history_calculations_reflect_saved_records()
    {
        $this->actingAs($this->adminUser);

        $lrNo = 'LR-DASH-' . uniqid();
        $tollBillNo = 'TOLL-DIR-' . uniqid();

        $bulty = Bulty::create([
            'lr_no' => $lrNo,
            'lr_date' => now()->toDateString(),
            'company_id' => $this->company->id,
            'branch_id' => $this->branch->id,
            'consignor_id' => $this->consignor->id,
            'consignee_id' => $this->consignee->id,
            'from_city_id' => $this->cityOrigin->id,
            'to_city_id' => $this->cityDest->id,
            'vehicle_id' => $this->vehicle->id,
            'driver_id' => $this->driver->id,
            'freight_charges' => 12000,
            'total_amount' => 12000,
            'bill_status' => 'unbilled',
        ]);

        $trip = Trip::create([
            'builty_id' => $bulty->id,
            'status' => 'complete',
        ]);
        $bulty->update(['trip_id' => $trip->id]);

        // Trip with FastTag amount (single amount column)
        TripFastTagDetail::create([
            'trip_id' => $trip->id,
            'builty_id' => $bulty->id,
            'location' => 'JAIPUR TOLL',
            'one_way' => 0.00,
            'return' => 0.00,
            'amount' => 1000.00,
        ]);

        // Generate toll bill directly through generate route
        $response = $this->post(route('admin.transport.billing.generate'), [
            'ids' => (string)$bulty->id,
            'lr_nos' => $bulty->lr_no,
            'invoice_type' => 'toll',
            'bill_number' => $tollBillNo,
            'gst_type' => 'IGST',
            'action' => 'save',
        ]);
        $response->assertRedirect(route('admin.transport.toll-bills.index'));

        $generatedToll = Invoice::where('bill_number', $tollBillNo)->first();
        $this->assertNotNull($generatedToll);
        $this->assertEquals(1000.00, (float)$generatedToll->total_freight);
        $this->assertEquals(180.00, (float)$generatedToll->total_gst);
        $this->assertEquals(1180.00, (float)$generatedToll->total_amount);
        $this->assertEquals('pending', $generatedToll->status);

        // Check Dashboard Controller & View
        $dashResponse = $this->get(route('admin.transport.toll-bills.index', ['search' => $tollBillNo]));
        $dashResponse->assertStatus(200);
        $dashContent = $dashResponse->getContent();
        $this->assertStringContainsString('1,000.00', $dashContent);
        $this->assertStringContainsString('180.00', $dashContent);
        $this->assertStringContainsString('1,180.00', $dashContent);

        // Check Invoice History View
        $historyResponse = $this->get(route('admin.transport.invoices.index', ['search' => $tollBillNo]));
        $historyResponse->assertStatus(200);
        $historyContent = $historyResponse->getContent();
        $this->assertStringContainsString($tollBillNo, $historyContent);
        $this->assertStringContainsString('1,000.00', $historyContent);
        $this->assertStringContainsString('180.00', $historyContent);
        $this->assertStringContainsString('1,180.00', $historyContent);
    }
}
