<?php

// Point to correct autoload location relative to public/
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);

use App\Models\Product;

$output = "--- DEBUG START ---\n";
try {
    $products = Product::where('name_ar', 'LIKE', '%حليب%')
        ->orWhere('product_type', 'compound')
        ->with('units')
        ->take(5)
        ->get();

    foreach ($products as $p) {
        $output .= "Prd: {$p->id} {$p->name_ar} ({$p->product_type})\n";
        foreach ($p->units as $u) {
             $output .= " - Unit: {$u->unit_name} is_sale: " . ($u->is_sale ? '1' : '0') . "\n";
        }
    }
} catch (\Exception $e) {
    $output .= "Error: " . $e->getMessage();
}
$output .= "--- DEBUG END ---\n";

file_put_contents(__DIR__ . '/debug_result.txt', $output);
echo "Written to " . __DIR__ . '/debug_result.txt';
