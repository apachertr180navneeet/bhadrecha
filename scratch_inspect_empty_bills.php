<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$invoices = \App\Models\Invoice::whereNull('bill_number')->orWhere('bill_number', '')->get();
foreach ($invoices as $inv) {
    echo "ID: {$inv->id} | Invoice No: '{$inv->invoice_no}' | Bill No: '{$inv->bill_number}' | Type: {$inv->invoice_type} | Date: " . ($inv->invoice_date ? $inv->invoice_date->format('Y-m-d') : 'NULL') . "\n";
}
