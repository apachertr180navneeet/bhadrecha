<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$users = \App\Models\User::all();
foreach ($users as $u) {
    $cfg = \App\Models\UserMailConfiguration::where('user_id', $u->id)->first();
    echo "User ID: {$u->id}, Name: {$u->full_name}, Email: {$u->email}, Has Config: " . ($cfg ? ($cfg->is_configured ? 'YES (' . $cfg->mail_username . ')' : 'Configured=NO') : 'NO') . "\n";
}
