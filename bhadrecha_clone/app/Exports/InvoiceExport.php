<?php

namespace App\Exports;

use App\Models\Invoice;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class InvoiceExport implements FromView, ShouldAutoSize
{
    protected $invoice;

    public function __construct(Invoice $invoice)
    {
        $this->invoice = $invoice;
    }

    public function view(): View
    {
        $viewName = 'excel';
        if ($this->invoice->invoice_type === 'toll') {
            $viewName = 'toll-excel';
        } elseif ($this->invoice->template_type === 'nathdwara') {
            $viewName = 'excel_nathdwara';
        } elseif ($this->invoice->template_type === 'gypsum') {
            $viewName = 'excel_gypsum';
        }
        
        return view('admin.transport.billing.invoices.' . $viewName, [
            'invoice' => $this->invoice
        ]);
    }
}
