<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$invoices = \App\Models\Invoice::take(20)->get(['id', 'invoice_no', 'bill_number', 'case_id', 'invoice_type', 'invoice_date', 'total_amount', 'status']);
echo "Total Invoices: " . \App\Models\Invoice::count() . "\n";
foreach ($invoices as $inv) {
    echo "ID: {$inv->id} | InvNo: '{$inv->invoice_no}' | BillNo: '{$inv->bill_number}' | CaseID: '{$inv->case_id}' | Type: '{$inv->invoice_type}' | Date: '{$inv->invoice_date}' | Total: {$inv->total_amount}\n";
}
