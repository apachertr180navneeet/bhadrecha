<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Company;
use App\Models\Vehicle;
use App\Models\Driver;
use App\Models\Trip;
use App\Models\Bulty;
use App\Models\FuelCompany;
use App\Models\FuelPump;
use App\Models\AdBlueCompany;
use App\Models\TripFuelDetail;
use App\Models\TripFastTagDetail;
use App\Models\TripAdBlueDetail;
use App\Models\TripOtherAmountDetail;
use App\Models\TripAdvanceDetail;
use App\Models\MaintenanceHistory;
use App\Http\Controllers\Admin\ReportController;
use Illuminate\Http\Request;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Spatie\Permission\Models\Role;

class ReportsCalculationAuditTest extends TestCase
{
    use DatabaseTransactions;

    protected $adminUser;
    protected $company;
    protected $v1;
    protected $v2;
    protected $trip1;
    protected $trip2;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'database.default' => 'mysql',
            'database.connections.mysql.database' => 'gsgwevi6_bhadrecha',
        ]);
        \Illuminate\Support\Facades\DB::purge('mysql');

        Role::firstOrCreate(['name' => 'Super Admin', 'guard_name' => 'web']);

        $this->company = Company::create(['name' => 'Audit Co ' . uniqid(), 'status' => 'active']);
        $company = $this->company;

        $this->adminUser = User::first();
        if (!$this->adminUser) {
            $this->adminUser = new User();
            $this->adminUser->first_name = 'Audit';
            $this->adminUser->last_name = 'Tester';
            $this->adminUser->full_name = 'Audit Tester';
            $this->adminUser->slug = 'audit-tester';
            $this->adminUser->email = 'audit_' . uniqid() . '@example.com';
            $this->adminUser->phone = '9' . rand(100000000, 999999999);
            $this->adminUser->password = bcrypt('secret123');
            $this->adminUser->company_id = $company->id;
            $this->adminUser->status = 'active';
            $this->adminUser->save();
        } else {
            $this->adminUser->company_id = $company->id;
            $this->adminUser->save();
        }
        if (!$this->adminUser->hasRole('Super Admin')) {
            $this->adminUser->assignRole('Super Admin');
        }
        auth()->login($this->adminUser);
        session(['current_company_id' => $company->id]);

        $this->v1 = Vehicle::create([
            'company_id' => $company->id,
            'vehicle_number' => 'MH04AB' . rand(1000, 9999),
            'status' => 'active',
            'ownership_type' => 'owned',
        ]);
        $this->v2 = Vehicle::create([
            'company_id' => $company->id,
            'vehicle_number' => 'MH04CD' . rand(1000, 9999),
            'status' => 'active',
            'ownership_type' => 'owned',
        ]);

        $driver = Driver::create([
            'company_id' => $company->id,
            'name' => 'Audit Driver',
            'phone' => '9' . rand(100000000, 999999999),
            'license_number' => 'DL-' . rand(10000, 99999),
            'status' => 'active',
        ]);

        $fuelCo = FuelCompany::firstOrCreate(['name' => 'HPCL Audit'], ['status' => 'active']);
        $fuelPump = FuelPump::firstOrCreate(['name' => 'Pump Audit'], ['fuel_company_id' => $fuelCo->id, 'status' => 'active']);
        $adblueCo = AdBlueCompany::firstOrCreate(['name' => 'Tata AdBlue Audit'], ['company_id' => $company->id, 'status' => 'active']);

        // Trip 1 (Vehicle 1, June 5)
        $b1 = Bulty::create([
            'company_id' => $company->id,
            'vehicle_id' => $this->v1->id,
            'driver_id' => $driver->id,
            'lr_no' => 'JUNE-001-' . uniqid(),
            'lr_date' => '2026-06-05',
            'freight_charges' => 45000,
            'gst_amount' => 2500,
            'other_charges' => 2500,
            'total_amount' => 50000,
            'advance_amount' => 10000,
            'bilty_commission' => 500,
            'status' => 'delivered',
            'pickup_location' => 'City A',
            'delivery_location' => 'City B',
            'payment_type' => 'topay',
        ]);
        $this->trip1 = Trip::create([
            'builty_id' => $b1->id,
            'status' => 'complete',
            'fuel_amount' => 10000,
            'fasttag_total_amount' => 0,
            'adblue_total_amount' => 1500,
            'other_amount' => 500,
            'advance_total_amount' => 2000,
        ]);
        TripFuelDetail::create([
            'trip_id' => $this->trip1->id,
            'builty_id' => $b1->id,
            'date' => '2026-06-05',
            'fuel_company_id' => $fuelCo->id,
            'fuel_pump_id' => $fuelPump->id,
            'quantity' => 100,
            'rate' => 100,
            'amount' => 10000,
            'km' => 1000,
            'payment_type' => 'credit',
        ]);
        TripFastTagDetail::create([
            'trip_id' => $this->trip1->id,
            'builty_id' => $b1->id,
            'transaction_time' => '2026-06-05 10:00:00',
            'amount' => 0,
            'one_way' => 400,
            'return' => 350,
            'location' => 'Toll Plaza A',
        ]);
        TripAdBlueDetail::create([
            'trip_id' => $this->trip1->id,
            'builty_id' => $b1->id,
            'date' => '2026-06-05',
            'adblue_company_id' => $adblueCo->id,
            'quantity' => 25,
            'rate' => 60,
            'amount' => 1500,
            'payment_type' => 'credit',
        ]);
        TripOtherAmountDetail::create([
            'trip_id' => $this->trip1->id,
            'builty_id' => $b1->id,
            'title' => 'Loading charges',
            'amount' => 500,
            'date' => '2026-06-05',
        ]);
        TripAdvanceDetail::create([
            'trip_id' => $this->trip1->id,
            'builty_id' => $b1->id,
            'date' => '2026-06-05',
            'advance_amount' => 2000,
            'payment_type' => 'cash',
        ]);

        // Trip 2 (Vehicle 2, June 12)
        $b2 = Bulty::create([
            'company_id' => $company->id,
            'vehicle_id' => $this->v2->id,
            'driver_id' => $driver->id,
            'lr_no' => 'JUNE-002-' . uniqid(),
            'lr_date' => '2026-06-12',
            'freight_charges' => 36000,
            'gst_amount' => 2000,
            'other_charges' => 2000,
            'total_amount' => 40000,
            'advance_amount' => 5000,
            'bilty_commission' => 400,
            'status' => 'delivered',
            'pickup_location' => 'City A',
            'delivery_location' => 'City B',
            'payment_type' => 'paid',
        ]);
        $this->trip2 = Trip::create([
            'builty_id' => $b2->id,
            'status' => 'complete',
            'fuel_amount' => 8000,
            'fasttag_total_amount' => 600,
            'adblue_total_amount' => 1200,
            'other_amount' => 0,
            'advance_total_amount' => 1500,
        ]);
        TripFastTagDetail::create([
            'trip_id' => $this->trip2->id,
            'builty_id' => $b2->id,
            'transaction_time' => '2026-06-12 11:00:00',
            'amount' => 600,
            'one_way' => 600,
            'return' => 0,
            'location' => 'Toll Plaza B',
        ]);
        TripAdBlueDetail::create([
            'trip_id' => $this->trip2->id,
            'builty_id' => $b2->id,
            'date' => '2026-06-12',
            'adblue_company_id' => $adblueCo->id,
            'quantity' => 20,
            'rate' => 60,
            'amount' => 1200,
            'payment_type' => 'debit',
        ]);
        TripAdvanceDetail::create([
            'trip_id' => $this->trip2->id,
            'builty_id' => $b2->id,
            'date' => '2026-06-12',
            'advance_amount' => 1500,
            'payment_type' => 'cash',
        ]);

        MaintenanceHistory::create([
            'company_id' => $company->id,
            'vehicle_id' => $this->v1->id,
            'service_date' => '2026-06-20',
            'cost' => 3000,
            'service_type' => 'Oil Change',
        ]);
    }

    public function test_trip_model_fasttag_and_expense_accessors()
    {
        $this->trip1->refresh();
        $this->trip2->refresh();

        // FASTag with one_way + return split (400 + 350 = 750)
        $this->assertEquals(750, (float)$this->trip1->total_fasttag_amount);
        $this->assertEquals(14750, (float)$this->trip1->total_expenses);
        $this->assertEquals(35250, (float)$this->trip1->net_profit);

        // FASTag with direct amount = 600
        $this->assertEquals(600, (float)$this->trip2->total_fasttag_amount);
        $this->assertEquals(8000, (float)$this->trip2->total_fuel_amount);
        $this->assertEquals(11300, (float)$this->trip2->total_expenses);
        $this->assertEquals(28700, (float)$this->trip2->net_profit);
    }

    public function test_all_reports_reconcile_for_june_dataset()
    {
        $controller = app(ReportController::class);
        $req = new Request([
            'from_date' => '2026-06-01',
            'to_date' => '2026-06-30',
            'date_from' => '2026-06-01',
            'date_to' => '2026-06-30',
            'company_id' => $this->company->id,
        ]);
        $req->setUserResolver(fn() => $this->adminUser);

        // 1. MIS Report
        $misView = $controller->misReport($req);
        $misData = $misView->getData();

        // 2. Expense Management Report
        $expView = $controller->expenseReport($req);
        $expData = $expView->getData();

        // 3. Profit & Loss Report
        $plView = $controller->profitLossReport($req);
        $plData = $plView->getData();

        // 4. Vehicle Utilization Report
        $vuView = $controller->vehicleUtilization($req);
        $vuData = $vuView->getData();

        // Verify MIS
        $this->assertEquals(90000, (float)$misData['totalRevenue']);
        $this->assertEquals(18000, (float)$misData['totalFuelAmt']);
        $this->assertEquals(1350, (float)$misData['totalFastTag']);
        $this->assertEquals(2700, (float)$misData['totalAdBlue']);
        $this->assertEquals(500, (float)$misData['totalOtherExp']);
        $this->assertEquals(3500, (float)$misData['totalTripAdvance']);

        // Verify Expense Management Cards
        $this->assertEquals(18000, (float)$expData['totalFuelAmt']);
        $this->assertEquals(1350, (float)$expData['totalFastTag']);
        $this->assertEquals(2700, (float)$expData['totalAdBlue']);
        $this->assertEquals(500, (float)$expData['totalOtherExp']);
        $this->assertEquals(3500, (float)$expData['totalTripAdvance']);
        $this->assertEquals(26050, (float)$expData['totalTripExpenses']);
        $this->assertEquals(3000, (float)$expData['totalMaintenanceExpenses']);
        $this->assertEquals(29050, (float)$expData['grandTotal']);

        // Verify Expense Vehicle Breakdown Table sum matches cards exactly
        $vehicles = $expData['vehicles'];
        $v1Row = $vehicles->firstWhere('id', $this->v1->id);
        $v2Row = $vehicles->firstWhere('id', $this->v2->id);

        $this->assertNotNull($v1Row);
        $this->assertNotNull($v2Row);

        $this->assertEquals(10000, (float)$v1Row->fuel_expense);
        $this->assertEquals(750, (float)$v1Row->fasttag_expense);
        $this->assertEquals(1500, (float)$v1Row->adblue_expense);
        $this->assertEquals(500, (float)$v1Row->other_expense);
        $this->assertEquals(2000, (float)$v1Row->advance_expense);
        $this->assertEquals(3000, (float)$v1Row->maintenance_cost);
        $this->assertEquals(17750, (float)$v1Row->total_expense);

        $this->assertEquals(8000, (float)$v2Row->fuel_expense);
        $this->assertEquals(600, (float)$v2Row->fasttag_expense);
        $this->assertEquals(1200, (float)$v2Row->adblue_expense);
        $this->assertEquals(0, (float)$v2Row->other_expense);
        $this->assertEquals(1500, (float)$v2Row->advance_expense);
        $this->assertEquals(0, (float)$v2Row->maintenance_cost);
        $this->assertEquals(11300, (float)$v2Row->total_expense);

        // Verify P&L
        $plSummary = $plData['summary'];
        $this->assertEquals(90000, (float)$plSummary['total_income']);
        $this->assertEquals(18000, (float)$plSummary['fuel_expense']);
        $this->assertEquals(1350, (float)$plSummary['fasttag_expense']);
        $this->assertEquals(2700, (float)$plSummary['adblue_expense']);
        $this->assertEquals(500, (float)$plSummary['other_trip_expense']);
        $this->assertEquals(3500, (float)$plSummary['total_trip_advance']);
        $this->assertEquals(26050, (float)$plSummary['total_trip_expenses']);
        $this->assertEquals(900, (float)$plSummary['total_commission']);
        $this->assertEquals(26950, (float)$plSummary['total_expenses']);
        $this->assertEquals(63050, (float)$plSummary['net_profit']);

        // Verify Vehicle Utilization
        $vuVehicles = $vuData['vehicles'];
        $vu1 = $vuVehicles->firstWhere('id', $this->v1->id);
        $vu2 = $vuVehicles->firstWhere('id', $this->v2->id);

        $this->assertNotNull($vu1);
        $this->assertNotNull($vu2);

        $this->assertEquals(50000, (float)$vu1->total_revenue);
        $this->assertEquals(10000, (float)$vu1->total_fuel_amount);
        $this->assertEquals(750, (float)$vu1->total_fasttag);
        $this->assertEquals(1500, (float)$vu1->total_adblue);
        $this->assertEquals(500, (float)$vu1->total_other_expense);
        $this->assertEquals(2000, (float)$vu1->total_advance);
        $totExp1 = $vu1->total_fuel_amount + $vu1->total_fasttag + $vu1->total_adblue + $vu1->total_other_expense + $vu1->total_advance;
        $this->assertEquals(14750, (float)$totExp1);
        $this->assertEquals(35250, (float)($vu1->total_revenue - $totExp1));

        $this->assertEquals(40000, (float)$vu2->total_revenue);
        $this->assertEquals(8000, (float)$vu2->total_fuel_amount);
        $this->assertEquals(600, (float)$vu2->total_fasttag);
        $this->assertEquals(1200, (float)$vu2->total_adblue);
        $this->assertEquals(0, (float)$vu2->total_other_expense);
        $this->assertEquals(1500, (float)$vu2->total_advance);
        $totExp2 = $vu2->total_fuel_amount + $vu2->total_fasttag + $vu2->total_adblue + $vu2->total_other_expense + $vu2->total_advance;
        $this->assertEquals(11300, (float)$totExp2);
        $this->assertEquals(28700, (float)($vu2->total_revenue - $totExp2));
    }
}
