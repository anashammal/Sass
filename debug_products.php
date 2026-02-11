<?php

require __DIR__ . '/bootstrap/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);

use App\Models\Product;
use App\Models\ProductUnit;

// Search for 'Milk' or similar
$term = 'حليب';
echo "Searching for: $term\n";

$products = Product::where('name_ar', 'LIKE', "%$term%")->with('units')->get();

foreach ($products as $p) {
    echo "Product ID: {$p->id} | Name: {$p->name_ar} | Type: {$p->product_type}\n";
    echo "Units:\n";
    foreach ($p->units as $u) {
        echo "  - Unit: {$u->unit_name} | Barcode: {$u->barcode} | is_base: {$u->is_base_unit} | is_sale: {$u->is_sale} | is_purchase: {$u->is_purchase}\n";
    }
    echo "---------------------------------------------------\n";
}

// Check Compound Products
echo "\nChecking Compound Products:\n";
$compounds = Product::where('product_type', 'compound')->with('units')->get();
foreach ($compounds as $p) {
    echo "Product ID: {$p->id} | Name: {$p->name_ar} | Type: {$p->product_type}\n";
    echo "Units:\n";
    foreach ($p->units as $u) {
        echo "  - Unit: {$u->unit_name} | is_sale: {$u->is_sale}\n";
    }
    echo "---------------------------------------------------\n";
}
