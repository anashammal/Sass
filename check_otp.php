<?php

require __DIR__.'/vendor/autoload.php';
$app = require __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\PasswordResetCode;

echo "--- OTP INSPECTION ---\n";
$latest = DB::table('password_reset_codes')->orderBy('created_at', 'desc')->first();

if ($latest) {
    echo "Latest Code: " . $latest->code . "\n";
    echo "Email: " . $latest->email . "\n";
    echo "Created At: " . $latest->created_at . "\n";
    echo "Time Now: " . now() . "\n";
} else {
    echo "No OTP codes found.\n";
}
echo "--- END INSPECTION ---\n";
