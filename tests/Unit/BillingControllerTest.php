<?php

namespace Tests\Unit;

use App\Http\Controllers\Admin\Transport\BillingController;
use App\Models\Invoice;
use Illuminate\Http\Request;
use PHPUnit\Framework\TestCase;

class BillingControllerTest extends TestCase
{
    public function test_toll_invoice_metadata_falls_back_to_the_source_invoice_when_request_values_are_blank(): void
    {
        $controller = new BillingController();

        $sourceInvoice = new Invoice();
        $sourceInvoice->fill([
            'custom_hsn_code' => '996511',
            'billing_address' => 'Source billing address',
            'custom_place_of_supply' => 'Source Place',
            'custom_district' => 'Source District',
            'custom_state' => 'Source State',
            'custom_state_code' => '22',
            'custom_gstn' => '22AAAA0000A1Z5',
            'custom_pan_no' => 'AAAA0000A',
            'state_vendor_code' => 'STATE-001',
            'vendor_code' => 'VENDOR-001',
            'vendor_name' => 'Source Vendor',
            'company_name' => 'Source Company',
            'epod_status' => 'Y',
            'mn_number' => 'MN-001',
            'no_of_lrs' => 5,
        ]);

        $request = new Request([
            'custom_hsn_code' => '',
            'billing_address' => '',
            'custom_place_of_supply' => '',
            'custom_district' => '',
            'custom_state' => '',
            'custom_state_code' => '',
            'custom_gstn' => '',
            'custom_pan_no' => '',
            'state_vendor_code' => '',
            'vendor_code' => '',
            'vendor_name' => '',
            'company_name' => '',
            'epod_status' => '',
            'mn_number' => '',
            'no_of_lrs' => '',
        ]);

        $method = new \ReflectionMethod(BillingController::class, 'resolveTollInvoiceMetaData');
        $method->setAccessible(true);

        $data = $method->invoke($controller, $sourceInvoice, $request);

        $this->assertSame('996511', $data['custom_hsn_code']);
        $this->assertSame('Source billing address', $data['billing_address']);
        $this->assertSame('Source Place', $data['custom_place_of_supply']);
        $this->assertSame('Source District', $data['custom_district']);
        $this->assertSame('Source State', $data['custom_state']);
        $this->assertSame('22', $data['custom_state_code']);
        $this->assertSame('22AAAA0000A1Z5', $data['custom_gstn']);
        $this->assertSame('AAAA0000A', $data['custom_pan_no']);
        $this->assertSame('STATE-001', $data['state_vendor_code']);
        $this->assertSame('VENDOR-001', $data['vendor_code']);
        $this->assertSame('Source Vendor', $data['vendor_name']);
        $this->assertSame('Source Company', $data['company_name']);
        $this->assertSame('Y', $data['epod_status']);
        $this->assertSame('MN-001', $data['mn_number']);
        $this->assertSame(5, $data['no_of_lrs']);
    }

    public function test_is_same_gst_state_calculation(): void
    {
        $this->assertTrue(BillingController::isSameGstState('RAJASTHAN', 'RAJASTHAN'));
        $this->assertTrue(BillingController::isSameGstState('08-RAJASTHAN', 'RAJASTHAN'));
        $this->assertTrue(BillingController::isSameGstState('RAJASTHAN (08)', '08 - RAJASTHAN'));
        $this->assertFalse(BillingController::isSameGstState('RAJASTHAN', 'GUJARAT'));
        $this->assertFalse(BillingController::isSameGstState('MADHYA PRADESH', 'GUJARAT'));
    }
}
