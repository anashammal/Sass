<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Product;

// جلب أول 5 منتجات لديها صور
$products = Product::all()->filter(function($p) {
    return $p->hasMedia('products');
})->take(5);

if ($products->isEmpty()) {
    echo "No products with media found.\n";
    // Try even without filtering to see what the default returns
    $products = Product::take(3)->get();
}

foreach ($products as $p) {
    $spatieUrl = $p->getFirstMediaUrl('products', 'thumb');
    $accessorUrl = $p->image_url;
    
    echo "ID: " . $p->id . "\n";
    echo "Spatie Raw URL: " . $spatieUrl . "\n";
    echo "Accessor URL:   " . $accessorUrl . "\n";
    
    if (strpos($spatieUrl, 'storage/') !== false) {
        $path = explode('storage/', $spatieUrl, 2)[1];
        $fullPath = storage_path('app/public/' . explode('?', $path)[0]);
        echo "Physical Path: " . $fullPath . "\n";
        echo "File Exists:   " . (file_exists($fullPath) ? "YES" : "NO") . "\n";
    }
    echo "-----------------------------------\n";
}

echo "Asset('') returns: " . asset('') . "\n";
