<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->handle(Illuminate\Http\Request::capture());

echo "=== Currencies ===\n";
foreach (\App\Models\Currency::all() as $c) {
    echo $c->id . ': ' . $c->code . "\n";
}

echo "\n=== Store 7 Products ===\n";
$prods = \App\Models\Product::where('store_id', 7)->get();
foreach ($prods as $p) {
    $bu = $p->baseUnit;
    echo "ID: " . $p->id . " | Name: " . $p->name_ar . " | BaseUnit: " . ($bu ? $bu->id : 'NONE') . " | SellCurr: " . ($bu ? ($bu->sell_price_currency_id ?? 'NULL') : 'N/A') . "\n";
}
