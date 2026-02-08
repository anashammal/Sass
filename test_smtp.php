<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Mail;
use App\Mail\ReportMail;

$target = 'aanas.work@gmail.com'; // Testing with a known address
$subject = 'Notification ' . date('Y-m-d');
$body = 'Please find the attached document for your review.';

try {
    echo "Attempting to send Simplified ReportMail to $target via " . config('mail.mailers.smtp.host') . "...\n";
    Mail::to($target)->send(new ReportMail($subject, $body));
    echo "SUCCESS: Laravel thinks the Simplified ReportMail was sent.\n";
} catch (\Exception $e) {
    echo "FAILURE: " . $e->getMessage() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
}
