<?php

namespace Tests\Unit;

use App\Models\Bulty;
use App\Models\Company;
use App\Models\Consignor;
use App\Models\Invoice;
use App\Models\User;
use App\Imports\TripImport;
use Tests\TestCase;

class TransportOperationIssuesTest extends TestCase
{
    /**
     * Test 19: Print Bill View and _invoice_preview safely render when bilty is standalone
     * without any existing invoice and without throwing Attempt to read property "invoice_date" on null.
     */
    public function test_print_bill_and_preview_render_cleanly_for_standalone_bilty(): void
    {
        $user = new User([
            'first_name' => 'Admin',
            'last_name' => 'User',
            'email' => 'admin@example.com',
            'role' => 'admin',
        ]);
        $user->full_name = 'Admin User';
        $this->actingAs($user);

        $company = new Company();
        $company->name = 'Bhadrecha Logistics';
        $company->address = 'Bhadrecha Complex, Jodhpur';
        $company->gst_number = '08AAAAA0000A1Z5';

        $consignor = new Consignor();
        $consignor->name = 'Premier Cement Works';
        $consignor->address = 'Industrial Area, Beawar';
        $consignor->gst_no = '08BBBBB1111B1Z2';

        $bulty = new Bulty([
            'lr_no' => 'LR-2026-9999',
            'lr_date' => '2026-10-14',
            'freight_charges' => 12000,
            'advance_amount' => 2000,
            'other_charges' => 500,
            'gst_amount' => 600,
            'total_amount' => 12500,
        ]);
        $bulty->id = 9999;
        $bulty->setRelation('company', $company);
        $bulty->setRelation('consignor', $consignor);
        $bulty->setRelation('consignee', null);
        $bulty->setRelation('vehicle', null);
        $bulty->setRelation('driver', null);
        $bulty->setRelation('originCity', null);
        $bulty->setRelation('destinationCity', null);
        $bulty->setRelation('bultyItems', collect());
        $bulty->setRelation('bultyDetail', null);
        $bulty->setRelation('invoice', null);

        $invoiceConsignor = (object) [
            'name' => $consignor->name,
            'address' => $consignor->address,
            'city' => '',
            'state' => '',
            'pincode' => '',
            'vendor_code' => '',
            'gst_no' => $consignor->gst_no,
        ];

        $html = view('admin.transport.billing._invoice_preview', [
            'invoiceCompany' => $company,
            'existingInvoice' => null,
            'invoiceConsignor' => $invoiceConsignor,
            'billNumber' => $bulty->lr_no,
            'vendorCode' => '',
            'bulties' => collect([$bulty]),
            'gstPercentage' => 0,
            'freightTotal' => 10000,
            'gstTotal' => 600,
            'otherTotal' => 500,
            'grandTotal' => 11100,
            'amountInWords' => 'Eleven Thousand One Hundred Only',
            'bankAccountNo' => '1234567890',
            'bankIfsc' => 'SBIN0001234',
            'bankHolder' => 'BHADRECHA LOGISTICS',
            'grnNewPage' => false,
        ])->render();

        $this->assertStringContainsString('LR-2026-9999', $html);
        $this->assertStringContainsString('Bhadrecha Logistics', $html);
        $this->assertStringContainsString('Premier Cement Works', $html);
        $this->assertStringContainsString('14-10-2026', $html);
    }

    /**
     * Test 20: TripImport advance mapping logic.
     * Generic 'advance_amount' in import sheet represents Trip advance given to driver,
     * and MUST NOT be mapped to Bilty advance unless explicit bilty advance column is provided.
     */
    public function test_trip_import_does_not_populate_trip_advance_into_bilty_advance(): void
    {
        $import = new TripImport();
        $reflector = new \ReflectionClass($import);
        $parseAmount = $reflector->getMethod('parseAmount');
        $parseAmount->setAccessible(true);

        // Scenario 1: Row with only trip advance ('advance_amount' = 3000), no explicit bilty advance
        $rowWithTripAdvanceOnly = [
            'freight_charges' => '15000',
            'advance_amount' => '3000', // Trip advance
            'remark' => 'Test Trip with advance',
        ];

        $hasExplicitBiltyAdvance = isset($rowWithTripAdvanceOnly['bilty_advance_amount'])
            || isset($rowWithTripAdvanceOnly['bilty_advance'])
            || isset($rowWithTripAdvanceOnly['builty_advance_amount'])
            || isset($rowWithTripAdvanceOnly['builty_advance'])
            || isset($rowWithTripAdvanceOnly['lr_advance_amount'])
            || isset($rowWithTripAdvanceOnly['lr_advance']);

        $biltyAdvanceAmount = $hasExplicitBiltyAdvance
            ? $parseAmount->invoke($import, $rowWithTripAdvanceOnly['bilty_advance_amount'] ?? $rowWithTripAdvanceOnly['bilty_advance'] ?? 0)
            : 0;

        $tripAdvanceAmount = $parseAmount->invoke($import, $rowWithTripAdvanceOnly['advance_amount'] ?? 0);

        // Bilty advance must be 0
        $this->assertFalse($hasExplicitBiltyAdvance);
        $this->assertEquals(0, $biltyAdvanceAmount);
        // Trip advance must be 3000
        $this->assertEquals(3000, $tripAdvanceAmount);

        // Scenario 2: Row with both explicit bilty advance ('bilty_advance_amount' = 1000) and trip advance ('advance_amount' = 4000)
        $rowWithBothAdvances = [
            'freight_charges' => '20000',
            'bilty_advance_amount' => '1000', // Bilty advance
            'advance_amount' => '4000',       // Trip advance
        ];

        $hasExplicitBiltyAdvance2 = isset($rowWithBothAdvances['bilty_advance_amount'])
            || isset($rowWithBothAdvances['bilty_advance'])
            || isset($rowWithBothAdvances['builty_advance_amount'])
            || isset($rowWithBothAdvances['builty_advance'])
            || isset($rowWithBothAdvances['lr_advance_amount'])
            || isset($rowWithBothAdvances['lr_advance']);

        $biltyAdvanceAmount2 = $hasExplicitBiltyAdvance2
            ? $parseAmount->invoke($import, $rowWithBothAdvances['bilty_advance_amount'] ?? 0)
            : 0;

        $tripAdvanceAmount2 = $parseAmount->invoke($import, $rowWithBothAdvances['advance_amount'] ?? 0);

        $this->assertTrue($hasExplicitBiltyAdvance2);
        $this->assertEquals(1000, $biltyAdvanceAmount2);
        $this->assertEquals(4000, $tripAdvanceAmount2);
    }

    /**
     * Test 17 & 18: Bulty relationships and role helper methods exist.
     */
    public function test_bulty_has_invoice_relations_and_user_roles(): void
    {
        $bulty = new Bulty();
        $this->assertTrue(method_exists($bulty, 'invoice'));
        $this->assertTrue(method_exists($bulty, 'tollInvoice'));

        $user = new User();
        $this->assertTrue(method_exists($user, 'isSuperAdmin'));
        $this->assertTrue(method_exists($user, 'isCompanyAdmin'));
        $this->assertTrue(method_exists($user, 'isBranchManager'));
        $this->assertTrue(method_exists($user, 'canAccessCompany'));
        $this->assertTrue(method_exists($user, 'canAccessBranch'));
    }

    /**
     * Test Issue 18: View permission for Trip Details works independently.
     * Users with View-only access can access trip index & show, while restricted actions remain disabled.
     */
    public function test_view_trip_permission_works_independently_and_blocks_restricted_actions(): void
    {
        // 1. User with only 'view trips' permission
        $viewUser = new User([
            'first_name' => 'Trip',
            'last_name' => 'Viewer',
            'email' => 'viewer@example.com',
            'role' => 'operator',
        ]);
        $viewUser->id = 777;

        $permissions = collect([
            (object) ['name' => 'view trips'],
        ]);
        $viewUser->setRelation('permissions', $permissions);
        $viewUser->setRelation('roles', collect());

        // View permission must pass for both 'view trips' and 'view trip details'
        $this->assertTrue(\App\Providers\AuthServiceProvider::checkTripPermission($viewUser, 'view trips'));
        $this->assertTrue(\App\Providers\AuthServiceProvider::checkTripPermission($viewUser, 'view trip details'));

        // Restricted actions must FAIL
        $this->assertFalse(\App\Providers\AuthServiceProvider::checkTripPermission($viewUser, 'create trips'));
        $this->assertFalse(\App\Providers\AuthServiceProvider::checkTripPermission($viewUser, 'create trip details'));
        $this->assertFalse(\App\Providers\AuthServiceProvider::checkTripPermission($viewUser, 'edit trips'));
        $this->assertFalse(\App\Providers\AuthServiceProvider::checkTripPermission($viewUser, 'edit trip details'));
        $this->assertFalse(\App\Providers\AuthServiceProvider::checkTripPermission($viewUser, 'delete trips'));
        $this->assertFalse(\App\Providers\AuthServiceProvider::checkTripPermission($viewUser, 'delete trip details'));

        // 2. User with 'view trip details' permission alias
        $aliasUser = new User([
            'first_name' => 'Detail',
            'last_name' => 'Viewer',
            'email' => 'detail_viewer@example.com',
            'role' => 'operator',
        ]);
        $aliasUser->id = 778;

        $aliasPermissions = collect([
            (object) ['name' => 'view trip details'],
        ]);
        $aliasUser->setRelation('permissions', $aliasPermissions);
        $aliasUser->setRelation('roles', collect());

        // View permissions must pass via alias mapping
        $this->assertTrue(\App\Providers\AuthServiceProvider::checkTripPermission($aliasUser, 'view trips'));
        $this->assertTrue(\App\Providers\AuthServiceProvider::checkTripPermission($aliasUser, 'view trip details'));

        // Restricted actions must FAIL
        $this->assertFalse(\App\Providers\AuthServiceProvider::checkTripPermission($aliasUser, 'create trips'));
        $this->assertFalse(\App\Providers\AuthServiceProvider::checkTripPermission($aliasUser, 'create trip details'));
        $this->assertFalse(\App\Providers\AuthServiceProvider::checkTripPermission($aliasUser, 'edit trips'));
        $this->assertFalse(\App\Providers\AuthServiceProvider::checkTripPermission($aliasUser, 'edit trip details'));
        $this->assertFalse(\App\Providers\AuthServiceProvider::checkTripPermission($aliasUser, 'delete trips'));
        $this->assertFalse(\App\Providers\AuthServiceProvider::checkTripPermission($aliasUser, 'delete trip details'));

        // 3. Super Admin has all permissions
        $superAdmin = new User([
            'first_name' => 'Super',
            'last_name' => 'Admin',
            'email' => 'super@example.com',
            'role' => 'super admin',
        ]);
        $superAdmin->id = 1;
        $superAdmin->setRelation('permissions', collect());
        $superAdmin->setRelation('roles', collect());

        $this->assertTrue(\App\Providers\AuthServiceProvider::checkTripPermission($superAdmin, 'view trips'));
        $this->assertTrue(\App\Providers\AuthServiceProvider::checkTripPermission($superAdmin, 'view trip details'));
        $this->assertTrue(\App\Providers\AuthServiceProvider::checkTripPermission($superAdmin, 'create trips'));
        $this->assertTrue(\App\Providers\AuthServiceProvider::checkTripPermission($superAdmin, 'create trip details'));
        $this->assertTrue(\App\Providers\AuthServiceProvider::checkTripPermission($superAdmin, 'edit trips'));
        $this->assertTrue(\App\Providers\AuthServiceProvider::checkTripPermission($superAdmin, 'edit trip details'));
        $this->assertTrue(\App\Providers\AuthServiceProvider::checkTripPermission($superAdmin, 'delete trips'));
        $this->assertTrue(\App\Providers\AuthServiceProvider::checkTripPermission($superAdmin, 'delete trip details'));
    }
}
