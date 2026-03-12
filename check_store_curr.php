<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make("Illuminate\Contracts\Console\Kernel")->bootstrap();

$store = \App\Models\Store::find(7);
echo "Store: " . $store->name . " (ID: 7)\n";
echo "Base Currency: " . $store->baseCurrency->code . " (ID: " . $store->base_currency_id . ")\n";
echo "Accepted Currencies:\n";
foreach ($store->acceptedCurrencies as $cur) {
    echo "  - " . $cur->code . " (ID: " . $cur->id . ")\n";
}
