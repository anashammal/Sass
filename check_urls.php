<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$store = \App\Models\Store::find(7);
if ($store) {
    echo "Logo URL: " . $store->logo_url . "\n";
    echo "Stamp URL: " . $store->stamp_url . "\n";
}

$product = \App\Models\Product::first();
if ($product) {
    echo "Product URL: " . $product->image_url . "\n";
}
