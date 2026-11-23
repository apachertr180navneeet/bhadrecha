<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$cols = ['state_vendor_code', 'vendor_code', 'custom_district', 'bill_number', 'invoice_no', 'invoice_date', 'e_invoice_no', 'ee_invoice_no'];
foreach ($cols as $col) {
    echo $col . ': ' . (Illuminate\Support\Facades\Schema::hasColumn('invoices', $col) ? 'YES' : 'NO') . "\n";
}
