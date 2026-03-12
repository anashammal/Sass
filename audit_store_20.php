<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make("Illuminate\Contracts\Console\Kernel")->bootstrap();

$storeId = 20;
$prods = \App\Models\Product::where('store_id', $storeId)->get();
echo "Store ID: 20 | Total Products: " . $prods->count() . "\n";

foreach ($prods as $p) {
    $bu = $p->baseUnit;
    echo "ID: " . $p->id . " | Name: " . $p->name_ar . " | CurrID: " . ($bu ? ($bu->sell_price_currency_id ?? 'NULL') : 'NONE') . "\n";
}
