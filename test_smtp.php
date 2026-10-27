<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Symfony\Component\Mailer\Transport\Smtp\EsmtpTransport;

try {
    $transport = new EsmtpTransport('smtp.gmail.com', 587, false);
    $transport->setUsername('navneetgehlot03061993@gmail.com');
    $transport->setPassword('tuejkfgkbnqjfgmz');
    $transport->start();
    echo "SMTP CONNECT SUCCESS!\n";
} catch (\Throwable $e) {
    echo "SMTP CONNECT FAILED: " . $e->getMessage() . "\n";
}
