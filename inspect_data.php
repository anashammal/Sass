<?php

use App\Models\Product;
use App\Models\Store;
use Illuminate\Support\Facades\DB;

require __DIR__.'/vendor/autoload.php';
$app = require __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "--- DATA INSPECTION Start ---\n";

$store = Store::first();
if ($store) {
    echo "Store Logo Path (Raw DB): '" . $store->logo_path . "'\n";
    echo "Store Logo URL (Accessor): '" . $store->logo_url . "'\n";
} else {
    echo "No store found.\n";
}

$product = Product::first();
if ($product) {
    echo "Product Media URL (Raw Spatie): '" . $product->getFirstMediaUrl('products', 'thumb') . "'\n";
    echo "Product Image URL (Accessor): '" . $product->image_url . "'\n";
}

echo "--- DATA INSPECTION End ---\n";
