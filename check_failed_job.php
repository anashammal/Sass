<?php

use Illuminate\Support\Facades\DB;

require __DIR__.'/vendor/autoload.php';
$app = require __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "--- FAILED JOBS INSPECTION ---\n";

try {
    // Just get the last inserted row by ID usually
    $fail = DB::table('failed_jobs')->orderBy('id', 'desc')->first();
    
    if ($fail) {
        echo "Failed At: " . ($fail->failed_at ?? 'N/A') . "\n";
        echo "Exception Summary:\n";
        echo substr($fail->exception, 0, 2000) . "\n...";
    } else {
        echo "No failed jobs found (double check?).\n";
    }
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
