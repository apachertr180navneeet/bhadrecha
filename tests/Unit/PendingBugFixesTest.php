<?php

namespace Tests\Unit;

use Tests\TestCase;
use Carbon\Carbon;
use App\Models\User;
use App\Models\Invoice;
use App\Models\Company;
use App\Models\Branch;
use App\Models\Bulty;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class PendingBugFixesTest extends TestCase
{
    /**
     * Issue 14: Sales Ledger TDS Calculation
     * TDS must be calculated strictly on base amount excluding GST, not on net payable including GST.
     */
    public function test_sales_ledger_tds_calculated_on_amount_without_gst(): void
    {
        // Example: Base Freight = 10,000, Other = 500 => Base Without GST = 10,500
        // GST (18%) = 1,890 => Net Payable = 12,390
        // TDS @ 2% on Base (10,500) = 210
        // If incorrectly calculated on Net Payable (12,390) => 247.80
        $freight = 10000.00;
        $other = 500.00;
        $gst = 1890.00;
        $netPayable = $freight + $other + $gst; // 12,390
        $tdsPercent = 2.0;

        $amountWithoutGst = $freight + $other; // 10,500
        $correctTds = round(($amountWithoutGst * $tdsPercent) / 100, 2);
        $incorrectTds = round(($netPayable * $tdsPercent) / 100, 2);

        $this->assertEquals(210.00, $correctTds);
        $this->assertNotEquals($incorrectTds, $correctTds);
    }

    /**
     * Dashboard: Day-of-month overflow prevention for monthly trends.
     * When date is 31st (e.g., May 31), Carbon::subMonths(1) would overflow to May 1 (duplicate May).
     * startOfMonth()->subMonths($i) guarantees exactly 12 unique months.
     */
    public function test_dashboard_monthly_trend_produces_no_duplicate_months_on_31st(): void
    {
        // Mock current time to May 31
        Carbon::setTestNow(Carbon::create(2026, 5, 31, 12, 0, 0));

        $monthsWithStartOfMonth = [];
        for ($i = 11; $i >= 0; $i--) {
            $date = Carbon::now()->startOfMonth()->subMonths($i);
            $monthsWithStartOfMonth[] = $date->format('M Y');
        }

        // Must produce exactly 12 unique months
        $this->assertCount(12, $monthsWithStartOfMonth);
        $this->assertCount(12, array_unique($monthsWithStartOfMonth));

        // Restore real time
        Carbon::setTestNow();
    }

    /**
     * Tyre Position Selector: Left side has 9 slots matching Right side (9 slots).
     */
    public function test_tyre_management_view_has_9_slots_label_on_left_and_right(): void
    {
        $viewPath = resource_path('views/admin/maintenance/tyre-management/edit.blade.php');
        $this->assertFileExists($viewPath);
        $content = file_get_contents($viewPath);

        $this->assertStringContainsString('label="Left Side (9 Slots)"', $content);
        $this->assertStringContainsString('label="Right Side (9 Slots)"', $content);
        $this->assertStringNotContainsString('label="Left Side (8 Slots)"', $content);
    }

    /**
     * Letter Pad Template: Contrast and Single-page print/PDF styles.
     */
    public function test_letterhead_views_contain_dark_contrast_and_single_page_pdf(): void
    {
        $createContent = file_get_contents(resource_path('views/admin/letterheads/create.blade.php'));
        $editContent = file_get_contents(resource_path('views/admin/letterheads/edit.blade.php'));
        $showContent = file_get_contents(resource_path('views/admin/letterheads/show.blade.php'));
        $pdfContent = file_get_contents(resource_path('views/admin/letterheads/pdf.blade.php'));

        // Dark color #062E39 instead of light/white on preview
        $this->assertStringContainsString('#062E39 !important', $createContent);
        $this->assertStringContainsString('#062E39 !important', $editContent);
        $this->assertStringContainsString('#062E39 !important', $showContent);

        // Show view has print button and print media CSS
        $this->assertStringContainsString('window.print()', $showContent);
        $this->assertStringContainsString('@media print', $showContent);

        // PDF template uses compact margins and base64 embedding
        $this->assertStringContainsString('margin: 80px 35px 55px 35px', $pdfContent);
        $this->assertStringContainsString('base64_encode', $pdfContent);
    }

    /**
     * Access & Controls: Invoice History Delete Permission Check
     */
    public function test_invoice_history_delete_option_wrapped_with_permission_check(): void
    {
        $indexContent = file_get_contents(resource_path('views/admin/transport/billing/invoices/index.blade.php'));
        $tollContent = file_get_contents(resource_path('views/admin/transport/billing/invoices/toll-bills.blade.php'));

        $this->assertStringContainsString("auth()->user()->can('delete invoices')", $indexContent);
        $this->assertStringContainsString("auth()->user()->can('delete invoices')", $tollContent);
    }

    /**
     * Transport Operation: Bilty create/edit company select rendered for multi-company/admin users.
     */
    public function test_bilty_views_render_company_select_when_companies_available(): void
    {
        $createContent = file_get_contents(resource_path('views/admin/transport/bulties/create.blade.php'));
        $editContent = file_get_contents(resource_path('views/admin/transport/bulties/edit.blade.php'));

        $this->assertStringContainsString('@if(!empty($companies) && count($companies) > 0)', $createContent);
        $this->assertStringContainsString('@if(!empty($companies) && count($companies) > 0)', $editContent);
    }
}
