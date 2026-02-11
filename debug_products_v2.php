<?php

require __DIR__ . '/bootstrap/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);

use App\Models\Product;
use App\Models\ProductUnit;

echo "--- START DEBUG ---\n";

// Search for 'Milk' or 'حليب' or ' Compound'
$products = Product::where('name_ar', 'LIKE', '%حليب%')
                   ->orWhere('product_type', 'compound')
                   ->with('units')
                   ->take(5)
                   ->get();

foreach ($products as $p) {
    echo "ID: {$p->id} | Name: {$p->name_ar} | Type: {$p->product_type}\n";
    foreach ($p->units as $u) {
        $isSal = $u->is_sale ? 'TRUE' : 'FALSE';
        $isPur = $u->is_purchase ? 'TRUE' : 'FALSE';
        echo "   Unit: {$u->unit_name} ({$u->barcode}) -> Sale: {$isSal} | Purchase: {$isPur}\n";
    }
    echo "--------------------\n";
}

echo "--- END DEBUG ---\n";
