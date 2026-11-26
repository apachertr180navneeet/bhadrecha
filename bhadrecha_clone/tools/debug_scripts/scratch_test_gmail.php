<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// User 2 has NO mail configuration
$user = \App\Models\User::find(2);
echo "Testing fallback send for user: " . $user->full_name . " (" . $user->email . ")\n";

try {
    \App\Services\UserMailService::send('navneetgehlot03061993@gmail.com', function($msg) {
        $msg->subject('Test Fallback System Mailer');
        $msg->html('Testing system fallback mailer with Gmail SMTP.');
    }, $user);
    echo "SUCCESS: Fallback email sent successfully!\n";
} catch (\Throwable $e) {
    echo "FAILED: " . $e->getMessage() . "\n";
}
