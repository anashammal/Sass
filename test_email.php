<?php

require __DIR__.'/vendor/autoload.php';
$app = require __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Mail;

echo "--- SENDING TEST EMAIL ---\n";

try {
    Mail::raw('This is a test email from the debugger.', function ($message) {
        $message->to('aanasshopping@gmail.com')
                ->subject('Test Email Debug');
    });
    echo "Email sent successfully.\n";
} catch (\Exception $e) {
    echo "Email Failed: " . $e->getMessage() . "\n";
}

echo "--- END TEST ---\n";
