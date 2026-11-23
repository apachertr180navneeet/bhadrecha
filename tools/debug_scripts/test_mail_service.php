<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$user = \App\Models\User::find(1);
$config = \App\Models\UserMailConfiguration::firstOrNew(['user_id' => $user->id]);
$config->provider = 'gmail';
$config->mail_host = 'smtp.gmail.com';
$config->mail_port = 587;
$config->mail_encryption = 'tls';
$config->mail_username = 'navneetgehlot03061993@gmail.com';
$config->mail_password = 'tuejkfgkbnqjfgmz';
$config->mail_from_address = 'navneetgehlot03061993@gmail.com';
$config->mail_from_name = 'Transport ERP';
$config->is_active = true;
$config->save();

echo "Testing UserMailService::testConnection...\n";
$res = \App\Services\UserMailService::testConnection($config, 'navneetgehlot03061993@gmail.com');
print_r($res);
