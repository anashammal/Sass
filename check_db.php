<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make("Illuminate\Contracts\Console\Kernel")->bootstrap();

use App\Models\Product;
use App\Models\ProductUnit;

echo "=== Duplicate Base Units Audit ===\n";
$counts = ProductUnit::where('is_base_unit', 1)
    ->groupBy('product_id')
    ->selectRaw('product_id, count(*) as count')
    ->having('count', '>', 1)
    ->get();

foreach ($counts as $c) {
    echo "Product ID " . $c->product_id . " has " . $c->count . " base units!\n";
    $units = ProductUnit::where('product_id', $c->product_id)->get();
    foreach ($units as $u) {
        echo "  - Unit ID: " . $u->id . " | Name: " . $u->unit_name . " | Base: " . ($u->is_base_unit ? 'YES' : 'NO') . " | CurrID: " . ($u->sell_price_currency_id ?? 'NULL') . "\n";
    }
}

echo "\n=== AED Check (ID 5) ===\n";
$aedUnits = ProductUnit::where('sell_price_currency_id', 5)->get();
foreach ($aedUnits as $au) {
    echo "Product ID " . $au->product_id . " has an AED unit. Base: " . ($au->is_base_unit ? 'YES' : 'NO') . "\n";
}
