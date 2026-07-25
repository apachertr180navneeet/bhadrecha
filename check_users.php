<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "USERS:\n";
foreach (\App\Models\User::all() as $u) {
    echo "ID: {$u->id} | Name: {$u->full_name} | RoleCol: '{$u->role}' | CompanyID: {$u->company_id} | isSuperAdmin: " . ($u->isSuperAdmin() ? 'YES' : 'NO') . "\n";
}
