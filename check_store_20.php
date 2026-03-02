<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Product;
$products = Product::where('store_id', 20)->with('units')->get();
foreach ($products as $p) {
    echo "Product: {$p->name} | Type: {$p->product_type} | Active: " . ($p->is_active ? 'Y' : 'N') . "\n";
    foreach ($p->units as $u) {
        echo "  - Unit: {$u->unit_name} | is_sale: " . ($u->is_sale ? 'Y' : 'N') . " | is_base: " . ($u->is_base_unit ? 'Y' : 'N') . "\n";
    }
}
