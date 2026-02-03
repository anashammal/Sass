<?php

use App\Models\Product;
use Illuminate\Support\Facades\Storage;

require __DIR__.'/vendor/autoload.php';
$app = require __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$product = Product::first();
echo "--- DEBUG OUTPUT Start ---\n";
if ($product) {
    echo "Product Image URL: " . $product->image_url . "\n";
} else {
    echo "No products found.\n";
}
echo "Asset URL (test.png): " . asset('test.png') . "\n";
echo "Storage URL (test.png): " . Storage::url('test.png') . "\n";
echo "Config App URL: " . config('app.url') . "\n";
echo "--- DEBUG OUTPUT End ---\n";
