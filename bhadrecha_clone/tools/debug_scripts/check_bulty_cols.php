<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$cols = \Illuminate\Support\Facades\Schema::getColumnListing('bulties');
print_r($cols);
$cols2 = \Illuminate\Support\Facades\Schema::getColumnListing('bulty_details');
print_r($cols2);
