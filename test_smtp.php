<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Mail;
use App\Mail\ReportMail;

$target = 'test-68adad@test.mailgenius.com'; // New MailGenius verification
$subject = 'Test ' . date('H:i');
$body = 'This is a test message from TechSys server. Time: ' . date('H:i:s');

try {
    echo "Attempting to send Simplified ReportMail to $target via " . config('mail.mailers.smtp.host') . "...\n";
    Mail::to($target)->send(new ReportMail($subject, $body));
    echo "SUCCESS: Laravel thinks the Simplified ReportMail was sent.\n";
} catch (\Exception $e) {
    echo "FAILURE: " . $e->getMessage() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
}
