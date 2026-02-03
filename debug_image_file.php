<?php

use App\Models\Product;

require __DIR__.'/vendor/autoload.php';
$app = require __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "--- DEBUG START ---\n";

$product = Product::first();
if ($product) {
    echo "Filesystem Root: " . public_path() . "\n";
    $url = $product->image_url;
    echo "Generated URL: " . $url . "\n";
    
    // Convert URL back to file path to check existence
    // URL: /system/storage/16/conversions/HYPO-thumb.jpg
    // Expected File: C:\xampp\htdocs\system\public\storage\16\conversions\HYPO-thumb.jpg
    
    // Remove leading /system/ if present (heuristically) based on relative checks
    // Or just look for 'storage' segment
    
    $parts = explode('/storage/', $url);
    if (count($parts) > 1) {
        $relativePath = 'storage/' . $parts[1];
        $fullPath = public_path($relativePath);
        echo "Checking File Path: " . $fullPath . "\n";
        
        if (file_exists($fullPath)) {
            echo "STATUS: FILE EXISTS on disk.\n";
        } else {
            echo "STATUS: FILE MISSING on disk.\n";
        }
    } else {
        echo "Could not parse storage path from URL.\n";
    }

} else {
    echo "No products found.\n";
}
echo "--- DEBUG END ---\n";
