<?php

namespace Tests\Unit;

use App\Models\Bulty;
use App\Models\Company;
use App\Models\Invoice;
use Tests\TestCase;

class BillingLrUnitTest extends TestCase
{
    public function test_invoice_preview_renders_all_44_lr_numbers(): void
    {
        $company = new Company();
        $company->name = 'Bhadrecha Logistics';
        $company->gst_number = '08AAAAA0000A1Z5';

        $bulties = collect();
        $expectedLrNos = [];
        for ($i = 1; $i <= 44; $i++) {
            $lrNo = 'LR-TEST-44-' . str_pad((string)$i, 3, '0', STR_PAD_LEFT);
            $expectedLrNos[] = $lrNo;
            $b = new Bulty([
                'lr_no' => $lrNo,
                'lr_date' => now(),
                'freight_charges' => 500,
                'total_amount' => 500,
                'gst_amount' => 25,
                'other_charges' => 0,
            ]);
            $b->id = 100000 + $i;
            $b->setRelation('company', $company);
            $bulties->push($b);
        }

        $invoice = new Invoice([
            'invoice_no' => 'INV-TEST-44',
            'total_freight' => 44 * 500,
            'total_amount' => 44 * 525,
            'total_gst' => 44 * 25,
            'no_of_lrs' => 44,
            'template_type' => 'dynamic',
            'visible_fields' => null, // Standard format default fallback
        ]);
        $invoice->setRelation('bulties', $bulties);
        $invoice->setRelation('company', $company);

        // Render preview blade
        $html = view('admin.transport.billing._invoice_preview', [
            'existingInvoice' => $invoice,
            'invoiceCompany' => $company,
            'bulties' => $bulties,
            'billNumber' => 'INV-TEST-44',
            'freightTotal' => 44 * 500,
            'gstTotal' => 44 * 25,
            'otherTotal' => 0,
            'grandTotal' => 44 * 525,
            'amountInWords' => 'TEST AMOUNT',
        ])->render();

        // Assert all 44 LR numbers appear in the rendered HTML
        foreach ($expectedLrNos as $lrNo) {
            $this->assertStringContainsString($lrNo, $html, "Missing LR: {$lrNo} in rendered invoice preview");
        }
    }

    public function test_excel_export_renders_all_44_lr_numbers(): void
    {
        $company = new Company();
        $company->name = 'Bhadrecha Logistics';

        $bulties = collect();
        $expectedLrNos = [];
        for ($i = 1; $i <= 44; $i++) {
            $lrNo = 'LR-EXCEL-44-' . str_pad((string)$i, 3, '0', STR_PAD_LEFT);
            $expectedLrNos[] = $lrNo;
            $b = new Bulty([
                'lr_no' => $lrNo,
                'lr_date' => now(),
                'freight_charges' => 500,
                'total_amount' => 500,
                'gst_amount' => 25,
                'other_charges' => 0,
            ]);
            $b->id = 200000 + $i;
            $b->setRelation('company', $company);
            $bulties->push($b);
        }

        $invoice = new Invoice([
            'invoice_no' => 'INV-EXCEL-44',
            'total_freight' => 44 * 500,
            'total_amount' => 44 * 525,
            'total_gst' => 44 * 25,
            'no_of_lrs' => 44,
            'template_type' => 'dynamic',
            'visible_fields' => null, // Standard format default fallback
        ]);
        $invoice->setRelation('bulties', $bulties);
        $invoice->setRelation('company', $company);

        $html = view('admin.transport.billing.invoices.excel', [
            'invoice' => $invoice,
        ])->render();

        foreach ($expectedLrNos as $lrNo) {
            $this->assertStringContainsString($lrNo, $html, "Missing LR: {$lrNo} in rendered excel view");
        }
    }

    public function test_invoice_preview_renders_when_existing_invoice_is_null(): void
    {
        $company = new Company();
        $company->name = 'Bhadrecha Logistics';

        $bulty = new Bulty([
            'lr_no' => 'LR-NULL-INVOICE',
            'lr_date' => now(),
            'freight_charges' => 1500,
            'total_amount' => 1500,
            'gst_amount' => 75,
            'other_charges' => 0,
        ]);
        $bulty->id = 300001;
        $bulty->setRelation('company', $company);

        // Render preview with existingInvoice = null (as in print_bill)
        $html = view('admin.transport.billing._invoice_preview', [
            'existingInvoice' => null,
            'invoiceCompany' => $company,
            'bulties' => collect([$bulty]),
            'billNumber' => 'LR-NULL-INVOICE',
            'freightTotal' => 1500,
            'gstTotal' => 75,
            'otherTotal' => 0,
            'grandTotal' => 1575,
            'amountInWords' => 'ONE THOUSAND FIVE HUNDRED SEVENTY FIVE ONLY',
        ])->render();

        $this->assertStringContainsString('LR-NULL-INVOICE', $html);
    }
}
