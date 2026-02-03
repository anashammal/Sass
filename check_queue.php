<?php

use Illuminate\Support\Facades\DB;

require __DIR__.'/vendor/autoload.php';
$app = require __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "--- QUEUE STATUS ---\n";

try {
    $jobs = DB::table('jobs')->count();
    $failed = DB::table('failed_jobs')->count();
    
    echo "Pending Jobs in Queue: $jobs\n";
    echo "Failed Jobs: $failed\n";
    
    if ($failed > 0) {
        $lastFail = DB::table('failed_jobs')->latest()->first();
        echo "Last Failed Job Error: " . substr($lastFail->exception, 0, 200) . "...\n";
    }
} catch (\Exception $e) {
    echo "Error checking tables: " . $e->getMessage() . "\n";
}

echo "--- END STATUS ---\n";
