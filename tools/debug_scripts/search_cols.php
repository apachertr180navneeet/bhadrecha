<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$db = env('DB_DATABASE');
$cols = \Illuminate\Support\Facades\DB::select("
    SELECT TABLE_NAME, COLUMN_NAME, DATA_TYPE, COLUMN_COMMENT 
    FROM INFORMATION_SCHEMA.COLUMNS 
    WHERE TABLE_SCHEMA = ? AND (
        COLUMN_NAME LIKE '%invoice%' 
        OR COLUMN_NAME LIKE '%bill%' 
        OR COLUMN_NAME LIKE '%case%'
        OR COLUMN_NAME LIKE '%eway%'
        OR COLUMN_NAME LIKE '%e_lr%'
    )
    ORDER BY TABLE_NAME, ORDINAL_POSITION
", [$db]);

foreach ($cols as $c) {
    echo "{$c->TABLE_NAME}.{$c->COLUMN_NAME} ({$c->DATA_TYPE})\n";
}
