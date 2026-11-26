<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "--- Invoices sample ---\n";
$invoices = \App\Models\Invoice::select('id', 'invoice_no', 'bill_number', 'case_id', 'invoice_type', 'template_type')->get();
foreach ($invoices as $inv) {
    echo "ID: {$inv->id} | InvNo: {$inv->invoice_no} | BillNo: {$inv->bill_number} | Case: {$inv->case_id} | Type: {$inv->invoice_type} | Template: {$inv->template_type}\n";
}

echo "--- Bulties with invoice_number sample ---\n";
$bulties = \App\Models\Bulty::whereNotNull('invoice_number')->select('id', 'lr_no', 'invoice_number', 'invoice_date', 'eway_bill_no', 'e_lr_no')->take(10)->get();
foreach ($bulties as $b) {
    echo "Bulty ID: {$b->id} | LR: {$b->lr_no} | InvNum: {$b->invoice_number} | InvDate: {$b->invoice_date} | Eway: {$b->eway_bill_no} | ELR: {$b->e_lr_no}\n";
}

echo "--- Bill Formats ---\n";
$formats = \App\Models\BillFormat::all();
foreach ($formats as $f) {
    echo "Format: {$f->format_name} | Type: {$f->template_type}\n";
    print_r($f->visible_fields);
}
