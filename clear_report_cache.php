<?php
// Clear Cache Script
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

echo "--- Clearing Daily Report Cache ---\n";

try {
    $stores = \App\Models\Store::where('status', 'active')->get();
    $count = 0;
    $date = date('Y-m-d');

    foreach ($stores as $store) {
        $key = 'daily_report_sent_' . $store->id . '_' . $date;
        
        if (Cache::has($key)) {
            Cache::forget($key);
            echo "✅ Cleared cache for store: {$store->name} (ID: {$store->id})\n";
            $count++;
        } else {
            echo "ℹ️  No cache found for store: {$store->name} (ID: {$store->id}) - Key: {$key}\n";
        }
    }

    echo "------------------------------------\n";
    echo "Done. Cleared {$count} records.\n";

} catch (\Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
