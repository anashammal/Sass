<?php
// Load Laravel
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Config;

use Illuminate\Support\Facades\Artisan;

Artisan::call('config:clear');
echo "Configuration cache cleared!\n";
echo "Attempting to send email via SMTP...\n";
echo "Host: " . Config::get('mail.mailers.smtp.host') . "\n";
echo "Port: " . Config::get('mail.mailers.smtp.port') . "\n";
echo "Encryption: " . Config::get('mail.mailers.smtp.encryption') . "\n";
echo "Username: " . Config::get('mail.mailers.smtp.username') . "\n";
echo "From Address: " . Config::get('mail.from.address') . "\n"; // Verify new address

try {
    Mail::raw('This is a test email sent from the Laravel application using the no-reply address.', function ($message) {
        $message->to('aanashammal@gmail.com') // Replace with a known working email if needed, or ask user. I'll use the one from previous context if visible, otherwise generic.
                ->subject('Test Email from Laravel (No-Reply)');
    });
    echo "SUCCESS: Email sent successfully.\n";
} catch (\Exception $e) {
    echo "ERROR: Failed to send email.\n";
    echo "Message: " . $e->getMessage() . "\n";
}
