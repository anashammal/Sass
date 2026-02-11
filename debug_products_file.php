<?php

require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);

use App\Models\Product;

$output = "--- START DEBUG ---\n";

$products = Product::where('name_ar', 'LIKE', '%حليب%')
                   ->orWhere('product_type', 'compound')
                   ->with('units')
                   ->take(10)
                   ->get();

foreach ($products as $p) {
    $output .= "ID: {$p->id} | Name: {$p->name_ar} | Type: {$p->product_type}\n";
    foreach ($p->units as $u) {
        $isSal = $u->is_sale ? 'TRUE' : 'FALSE';
        $isPur = $u->is_purchase ? 'TRUE' : 'FALSE';
        $output .= "   Unit: {$u->unit_name} ({$u->barcode}) -> Sale: {$isSal} | Purchase: {$isPur}\n";
    }
    $output .= "--------------------\n";
}

$output .= "--- END DEBUG ---\n";

file_put_contents('debug_output.txt', $output);
echo "Done.";
