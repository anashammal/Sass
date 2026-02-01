<?php
$_SERVER['REMOTE_ADDR'] = '127.0.0.1';
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);
echo "--- STORE TYPE FIX ---\n";

// Search for stores with "مطعم" (Restaurant) in the name
$stores = \App\Models\Store::where('name', 'LIKE', '%مطعم%')->get();

if ($stores->count() > 0) {
    foreach ($stores as $store) {
        echo "Updating Store ID: " . $store->id . " Name: [" . $store->name . "] ...\n";
        $store->type = 'restaurant';
        $store->save();
        echo "Done. New Type: " . $store->type . "\n";
    }
} else {
    echo "No stores found with 'مطعم' in the name.\n";
    // Constructive fallback: Set the LAST created store to restaurant just to be helpful if name doesn't match
    $latestStore = \App\Models\Store::latest()->first();
    if($latestStore) {
         echo "Fallback: Updating latest store ID: " . $latestStore->id . " Name: [" . $latestStore->name . "] to restaurant.\n";
         $latestStore->type = 'restaurant';
         $latestStore->save();
    }
}
echo "--- FIX COMPLETE ---\n";
