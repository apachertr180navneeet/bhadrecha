<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$rows = \App\Models\BultyDetail::whereNotNull('bill_no')->orWhereNotNull('invoice_date')->orWhereNotNull('invoice_doc')->take(10)->get();
echo "BultyDetails matching count: " . $rows->count() . "\n";
foreach ($rows as $r) {
    echo "ID: {$r->id} | bulty: {$r->bulty_id} | bill_no: {$r->bill_no} | inv_date: {$r->invoice_date} | inv_doc: {$r->invoice_doc} | po: {$r->po_no}\n";
}
