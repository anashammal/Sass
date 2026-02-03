<?php

use App\Models\Product;
use App\Models\Store;
use Illuminate\Support\Facades\DB;

require __DIR__.'/vendor/autoload.php';
$app = require __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "--- VERIFICATION OUTPUT Start ---\n";

$product = Product::first();
if ($product) {
    echo "Product Image URL (Should be relative): " . $product->image_url . "\n";
} else {
    echo "No products found.\n";
}

$store = Store::first();
if ($store) {
    // Ensuring logo_path exists for testing or falling back to default
    echo "Store Logo URL (Should be relative): " . $store->logo_url . "\n";
} else {
    echo "No stores found.\n";
}

echo "--- VERIFICATION OUTPUT End ---\n";
